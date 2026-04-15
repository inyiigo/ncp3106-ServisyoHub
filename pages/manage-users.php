<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once "../config/db_connect.php";

if (empty($_SESSION['user_id'])) {
	header('Location: ./login.php');
	exit;
}

$db = $conn ?? null;
if (!$db instanceof mysqli) {
	http_response_code(500);
	exit('Database connection unavailable.');
}

function db_has_column(mysqli $db, string $table, string $column): bool {
	$table = mysqli_real_escape_string($db, $table);
	$column = mysqli_real_escape_string($db, $column);
	$res = @mysqli_query($db, "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
	if (!$res) {
		return false;
	}
	$exists = @mysqli_num_rows($res) > 0;
	@mysqli_free_result($res);
	return $exists;
}

function db_count(mysqli $db, string $sql): int {
	$res = @mysqli_query($db, $sql);
	if (!$res) {
		return 0;
	}
	$row = @mysqli_fetch_assoc($res);
	@mysqli_free_result($res);
	return (int)($row['c'] ?? 0);
}

function h(?string $value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function avatar_public_url(?string $avatar): ?string {
	$avatar = trim((string)$avatar);
	if ($avatar === '') {
		return null;
	}

	$avatar = str_replace('\\', '/', $avatar);
	if (strpos($avatar, '..') !== false) {
		return null;
	}

	$avatar = ltrim($avatar, '/');
	if (stripos($avatar, 'assets/') === 0 || stripos($avatar, 'uploads/') === 0) {
		return '../' . $avatar;
	}

	return null;
}

if (!db_has_column($db, 'users', 'status')) {
	@mysqli_query($db, "ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'pending' AFTER created_at");
}
if (!db_has_column($db, 'users', 'avatar_status')) {
	@mysqli_query($db, "ALTER TABLE users ADD COLUMN avatar_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER avatar");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
	$user_id = (int)$_POST['user_id'];
	$action = (string)$_POST['action'];
	$statusToSet = null;

	if ($action === 'verify') {
		$statusToSet = 'active';
	} elseif ($action === 'suspend') {
		$statusToSet = 'suspended';
	} elseif ($action === 'reactivate') {
		$statusToSet = 'active';
	}

	if ($statusToSet !== null && $stmt = mysqli_prepare($db, "UPDATE users SET status = ? WHERE id = ?")) {
		mysqli_stmt_bind_param($stmt, 'si', $statusToSet, $user_id);
		if (mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			header('Location: manage-users.php');
			exit;
		}
		mysqli_stmt_close($stmt);
	}
}

$users = [];
$result = @mysqli_query($db, "
	SELECT
		id,
		mobile,
		CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) AS name,
		COALESCE(email, '') AS email,
		COALESCE(avatar, '') AS avatar,
		LOWER(COALESCE(avatar_status, CASE WHEN COALESCE(avatar, '') <> '' THEN 'approved' ELSE 'none' END)) AS avatar_status,
		'User' AS role,
		DATE_FORMAT(created_at, '%Y-%m-%d') AS date_registered,
		LOWER(COALESCE(status, 'pending')) AS status
	FROM users
	ORDER BY id DESC
");
if ($result) {
	while ($row = mysqli_fetch_assoc($result)) {
		$rawName = trim((string)($row['name'] ?? ''));
		if ($rawName === '') {
			$row['name'] = 'User #' . (int)$row['id'];
		}
		$row['avatar_url'] = avatar_public_url((string)($row['avatar'] ?? ''));
		$users[] = $row;
	}
	mysqli_free_result($result);
}

$totalUsers = db_count($db, "SELECT COUNT(*) AS c FROM users");
$activeUsers = db_count($db, "SELECT COUNT(*) AS c FROM users WHERE LOWER(COALESCE(status, 'pending')) = 'active'");
$pendingUsers = db_count($db, "SELECT COUNT(*) AS c FROM users WHERE LOWER(COALESCE(status, 'pending')) = 'pending'");
$suspendedUsers = db_count($db, "SELECT COUNT(*) AS c FROM users WHERE LOWER(COALESCE(status, 'pending')) = 'suspended'");

$userStatusLabels = ['Active', 'Pending', 'Suspended'];
$userStatusData = [(int)$activeUsers, (int)$pendingUsers, (int)$suspendedUsers];
$userStatusColors = ['#0ea5e9', '#f59e0b', '#ef4444'];
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Manage Users • ServisyoHub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">
	<style>
		:root {
			--brand: #0078a6;
			--brand-dark: #0b2c24;
			--bg: #f3f8fb;
			--surface: rgba(255,255,255,.88);
			--line: rgba(15, 23, 42, .08);
			--text: #0f172a;
			--muted: #64748b;
			--shadow: 0 18px 50px rgba(2, 6, 23, .10);
		}
		* { box-sizing: border-box; }
		body {
			margin: 0;
			font-family: Montserrat, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
			color: var(--text);
			background:
				radial-gradient(circle at top left, rgba(0,120,166,.14), transparent 30%),
				radial-gradient(circle at right top, rgba(124,212,196,.22), transparent 28%),
				linear-gradient(180deg, #f7fbfd 0%, #eef7fb 100%);
		}
		.admin-shell {
			display: grid;
			grid-template-columns: 280px minmax(0, 1fr);
			min-height: 100vh;
		}
		.sidebar {
			position: sticky;
			top: 0;
			height: 100vh;
			padding: 24px 18px;
			background: rgba(7, 17, 24, .90);
			color: #fff;
			border-right: 1px solid rgba(255,255,255,.08);
			backdrop-filter: blur(14px);
			display: flex;
			flex-direction: column;
		}
		.brand {
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 6px 8px 18px;
		}
		.brand-logo {
			width: 48px;
			height: 48px;
			border-radius: 16px;
			background: linear-gradient(135deg, rgba(255,255,255,.18), rgba(255,255,255,.05));
			padding: 10px;
			flex: 0 0 48px;
			overflow: hidden;
		}
		.brand-logo img {
			width: 100%;
			height: 100%;
			display: block;
			object-fit: contain;
		}
		.brand h1 {
			margin: 0;
			font-size: 1.08rem;
			line-height: 1.1;
		}
		.brand p {
			margin: 4px 0 0;
			color: rgba(255,255,255,.68);
			font-size: .9rem;
		}
		.nav {
			display: grid;
			gap: 8px;
			margin-top: 18px;
		}
		.nav a {
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 12px 14px;
			border-radius: 14px;
			color: rgba(255,255,255,.86);
			border: 1px solid transparent;
			font-weight: 700;
			text-decoration: none;
		}
		.nav a.active {
			background: rgba(255,255,255,.12);
			border-color: rgba(255,255,255,.14);
			color: #fff;
		}
		.nav a:hover { background: rgba(255,255,255,.08); }
		.nav svg { width: 18px; height: 18px; flex: 0 0 18px; }
		.sidebar-footer {
			margin-top: auto;
			padding-top: 16px;
			color: rgba(255,255,255,.68);
			font-size: .9rem;
			line-height: 1.5;
		}
		.logout-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			width: 100%;
			margin-top: 14px;
			padding: 11px 12px;
			border-radius: 12px;
			border: 1px solid rgba(255,255,255,.22);
			background: rgba(239, 68, 68, .18);
			color: #fff;
			font-weight: 800;
			font-size: .9rem;
			text-decoration: none;
		}
		.logout-btn svg {
			width: 16px;
			height: 16px;
			flex: 0 0 16px;
		}
		.logout-btn:hover {
			background: rgba(239, 68, 68, .28);
			border-color: rgba(255,255,255,.35);
		}
		.content { padding: 24px; }
		.hero {
			display: grid;
			grid-template-columns: 1fr;
			gap: 18px;
			margin-bottom: 18px;
		}
		.hero-main {
			position: relative;
			overflow: hidden;
			background: linear-gradient(135deg, rgba(0,120,166,.97), rgba(14,116,162,.84));
			color: #fff;
			border-radius: 28px;
			padding: 28px;
			box-shadow: var(--shadow);
		}
		.hero-main h2 {
			margin: 0;
			font-size: clamp(1.2rem, 2vw, 1.75rem);
		}
		.hero-main p {
			margin: 10px 0 0;
			max-width: 580px;
			color: rgba(255,255,255,.9);
			line-height: 1.6;
		}
		.hero-main::after {
			content: '';
			position: absolute;
			width: 220px;
			height: 220px;
			right: -50px;
			top: -70px;
			border-radius: 999px;
			background: radial-gradient(circle, rgba(255,255,255,.34), rgba(255,255,255,.02));
		}
		.info-chip {
			position: relative;
			background: var(--surface);
			border: 1px solid var(--line);
			border-radius: 20px;
			padding: 18px;
			box-shadow: var(--shadow);
		}
		.info-chip h3 {
			margin: 0;
			font-size: 2rem;
			line-height: 1;
			color: #0f172a;
		}
		.info-chip p {
			margin: 8px 0 0;
			font-weight: 700;
			color: var(--muted);
		}
		.metrics {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 12px;
			margin-bottom: 18px;
		}
		.metric {
			background: var(--surface);
			border: 1px solid var(--line);
			border-radius: 16px;
			padding: 14px;
			box-shadow: var(--shadow);
		}
		.metric .label {
			display: block;
			font-size: .85rem;
			font-weight: 700;
			color: var(--muted);
			margin-bottom: 8px;
		}
		.metric .value {
			display: block;
			font-size: 1.7rem;
			font-weight: 800;
			line-height: 1;
			color: #0f172a;
		}
		.grid {
			display: grid;
			grid-template-columns: .95fr 2fr;
			gap: 18px;
			align-items: start;
		}
		.panel {
			background: var(--surface);
			border: 1px solid var(--line);
			border-radius: 20px;
			box-shadow: var(--shadow);
			overflow: hidden;
		}
		.panel-head {
			padding: 16px 18px 0;
		}
		.panel-head h3 {
			margin: 0;
			font-size: 1rem;
		}
		.panel-head p {
			margin: 6px 0 10px;
			color: var(--muted);
			font-size: .9rem;
		}
		.chart-wrap {
			height: 290px;
			padding: 0 14px 14px;
		}
		.table-wrap { padding: 8px 0 0; }
		.table-wrap + .table-wrap { border-top: 1px solid var(--line); }
		table {
			width: 100%;
			border-collapse: collapse;
			table-layout: auto;
		}
		thead th, section[aria-label="Profile picture review"] thead th {
			font-size: .82rem;
			text-transform: uppercase;
			letter-spacing: .04em;
			color: var(--muted);
			text-align: left;
			padding: 12px 14px;
			border-bottom: 1px solid var(--line);
		}
		tbody {
			display: block;
			max-height: 460px;
			overflow: auto;
		}
		thead, tbody tr {
			display: table;
			width: 100%;
			table-layout: auto;
		}
		tbody td {
			padding: 14px;
			border-bottom: 1px solid var(--line);
			font-size: .92rem;
			vertical-align: top;
		}
		tbody td:nth-child(3), section[aria-label="Profile picture review"] tbody td:nth-child(3) {
			word-break: break-word;
		}
		article[aria-label="User table"] thead th:nth-child(1), article[aria-label="User table"] tbody td:nth-child(1) { width: 7%; }
		article[aria-label="User table"] thead th:nth-child(2), article[aria-label="User table"] tbody td:nth-child(2) { width: 18%; }
		article[aria-label="User table"] thead th:nth-child(3), article[aria-label="User table"] tbody td:nth-child(3) { width: 30%; }
		article[aria-label="User table"] thead th:nth-child(4), article[aria-label="User table"] tbody td:nth-child(4) { width: 14%; }
		article[aria-label="User table"] thead th:nth-child(5), article[aria-label="User table"] tbody td:nth-child(5) { width: 13%; }
		article[aria-label="User table"] thead th:nth-child(6), article[aria-label="User table"] tbody td:nth-child(6) { width: 18%; }
		section[aria-label="Profile picture review"] thead th:nth-child(1), section[aria-label="Profile picture review"] tbody td:nth-child(1) { width: 6%; }
		section[aria-label="Profile picture review"] thead th:nth-child(2), section[aria-label="Profile picture review"] tbody td:nth-child(2) { width: 20%; }
		section[aria-label="Profile picture review"] thead th:nth-child(3), section[aria-label="Profile picture review"] tbody td:nth-child(3) { width: 13%; }
		section[aria-label="Profile picture review"] thead th:nth-child(4), section[aria-label="Profile picture review"] tbody td:nth-child(4) { width: 14%; }
		section[aria-label="Profile picture review"] thead th:nth-child(5), section[aria-label="Profile picture review"] tbody td:nth-child(5) { width: 29%; }
		section[aria-label="Profile picture review"] thead th:nth-child(6), section[aria-label="Profile picture review"] tbody td:nth-child(6) { width: 18%; }
		article[aria-label="User table"] thead th:nth-child(4),
		article[aria-label="User table"] tbody td:nth-child(4),
		article[aria-label="User table"] thead th:nth-child(5),
		article[aria-label="User table"] tbody td:nth-child(5),
		article[aria-label="User table"] thead th:nth-child(6),
		article[aria-label="User table"] tbody td:nth-child(6) {
			text-align: center;
		}
		article[aria-label="User table"] tbody td:nth-child(6) .action-stack {
			justify-content: center;
		}
		.status-pill {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-width: 98px;
			height: 30px;
			padding: 0 10px;
			border-radius: 999px;
			font-size: .8rem;
			font-weight: 800;
			letter-spacing: .03em;
		}
		.status-pending { background: #fef3c7; color: #92400e; }
		.status-active { background: #e0f2fe; color: #075985; }
		.status-suspended { background: #fee2e2; color: #991b1b; }
		.status-none { background: #e2e8f0; color: #334155; }
		.action-stack {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
		}
		.action-btn {
			border: none;
			border-radius: 10px;
			padding: 8px 12px;
			font-size: .82rem;
			font-weight: 700;
			cursor: pointer;
		}
		.action-btn.verify { background: #7cd4c4; color: #0b2c24; }
		.action-btn.suspend { background: #ef4444; color: #fff; }
		.action-btn.reactivate { background: #0ea5e9; color: #fff; }
		.empty-state {
			padding: 28px;
			text-align: center;
			font-weight: 700;
			color: var(--muted);
		}
		.avatar-thumb {
			width: 44px;
			height: 44px;
			border-radius: 10px;
			border: 1px solid var(--line);
			object-fit: cover;
			display: block;
			background: #e2e8f0;
			margin: 0 auto;
		}
		.avatar-missing {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 44px;
			height: 44px;
			border-radius: 10px;
			border: 1px dashed #94a3b8;
			font-size: .7rem;
			font-weight: 800;
			color: #64748b;
			background: #f8fafc;
			margin: 0 auto;
		}
		section[aria-label="Profile picture review"] thead th:nth-child(3),
		section[aria-label="Profile picture review"] tbody td:nth-child(3) {
			text-align: center;
		}
		section[aria-label="Profile picture review"] thead th:nth-child(4),
		section[aria-label="Profile picture review"] tbody td:nth-child(4),
		section[aria-label="Profile picture review"] thead th:nth-child(6),
		section[aria-label="Profile picture review"] tbody td:nth-child(6) {
			text-align: center;
		}
		.file-path {
			font-size: .8rem;
			color: #475569;
			word-break: break-word;
		}
		.view-link {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 7px 10px;
			border-radius: 9px;
			text-decoration: none;
			font-size: .8rem;
			font-weight: 800;
			background: #0ea5e9;
			color: #fff;
		}
		.view-link:hover { filter: brightness(.95); }
		@media (max-width: 1200px) {
			.admin-shell { grid-template-columns: 80px minmax(0, 1fr); }
			.brand h1, .brand p, .nav span, .sidebar-footer { display: none; }
			.nav a { justify-content: center; }
		}
		@media (max-width: 980px) {
			.hero,
			.metrics,
			.grid {
				grid-template-columns: 1fr;
			}
			.content { padding: 16px; }
			tbody { max-height: 420px; }
		}
	</style>
</head>
<body>
<div class="admin-shell">
	<aside class="sidebar" aria-label="Admin navigation">
		<div class="brand">
			<div class="brand-logo"><img src="../assets/images/newlogo2.png" alt="ServisyoHub"></div>
			<div>
				<h1>ServisyoHub</h1>
				<p>Admin tools</p>
			</div>
		</div>
		<nav class="nav">
			<a href="./admin.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z"/></svg>
				<span>Dashboard</span>
			</a>
			<a href="./post-approvals.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
				<span>Post Approvals</span>
			</a>
			<a href="./manage-users.php" class="active" aria-current="page">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
				<span>Manage Users</span>
			</a>
		</nav>
		<div class="sidebar-footer">
			<a class="logout-btn" href="./logout.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
				<span>Log out</span>
			</a>
		</div>
	</aside>

	<main class="content">
		<section class="hero">
			<div class="hero-main">
				<h2>User Verification and Account Governance</h2>
				<p>Monitor registration volume, review pending users, and keep account access healthy through quick verification and suspension controls.</p>
			</div>
		</section>

		<section class="metrics" aria-label="User metrics">
			<div class="metric">
				<span class="label">Registered Users</span>
				<span class="value"><?php echo (int)$totalUsers; ?></span>
			</div>
			<div class="metric">
				<span class="label">Active Users</span>
				<span class="value"><?php echo (int)$activeUsers; ?></span>
			</div>
			<div class="metric">
				<span class="label">Pending Verification</span>
				<span class="value"><?php echo (int)$pendingUsers; ?></span>
			</div>
			<div class="metric">
				<span class="label">Suspended Users</span>
				<span class="value"><?php echo (int)$suspendedUsers; ?></span>
			</div>
		</section>

		<section class="grid">
			<article class="panel" aria-label="Status graph">
				<div class="panel-head">
					<h3>User Status Overview</h3>
					<p>Distribution of active, pending, and suspended accounts.</p>
				</div>
				<div class="chart-wrap">
					<canvas id="userStatusChart"></canvas>
				</div>
			</article>

			<article class="panel" aria-label="User table">
				<div class="panel-head">
					<h3>Verification and Suspension Queue</h3>
					<p>Manage each account directly from this table.</p>
				</div>
				<div class="table-wrap">
					<table>
						<thead>
							<tr>
								<th>ID</th>
								<th>User</th>
								<th>Contact</th>
								<th>Registered</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php if ($users): ?>
								<?php foreach ($users as $user): ?>
								<tr>
									<td><?php echo (int)$user['id']; ?></td>
									<td><?php echo h((string)$user['name']); ?></td>
									<td><?php echo h((string)($user['email'] ?: $user['mobile'])); ?></td>
									<td><?php echo h((string)$user['date_registered']); ?></td>
									<td>
										<?php if ($user['status'] === 'pending'): ?>
											<span class="status-pill status-pending">PENDING</span>
										<?php elseif ($user['status'] === 'suspended'): ?>
											<span class="status-pill status-suspended">SUSPENDED</span>
										<?php else: ?>
											<span class="status-pill status-active">ACTIVE</span>
										<?php endif; ?>
									</td>
									<td>
										<div class="action-stack">
											<?php if ($user['status'] === 'pending'): ?>
												<form method="post">
													<input type="hidden" name="action" value="verify">
													<input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
													<button type="submit" class="action-btn verify">Verify</button>
												</form>
											<?php endif; ?>
											<?php if ($user['status'] === 'suspended'): ?>
												<form method="post" onsubmit="return confirm('Reactivate this user?');">
													<input type="hidden" name="action" value="reactivate">
													<input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
													<button type="submit" class="action-btn reactivate">Reactivate</button>
												</form>
											<?php else: ?>
												<form method="post" onsubmit="return confirm('Suspend this user?');">
													<input type="hidden" name="action" value="suspend">
													<input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
													<button type="submit" class="action-btn suspend">Suspend</button>
												</form>
											<?php endif; ?>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							<?php else: ?>
							<tr><td class="empty-state" colspan="7">No users found.</td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</article>
		</section>

		<section class="panel" aria-label="Profile picture review" style="margin-top:18px;">
			<div class="panel-head">
				<h3>Profile Picture Review</h3>
				<p>Separate table for checking uploaded profile photos before accepting account identity details.</p>
			</div>
			<div class="table-wrap">
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>User</th>
							<th>Preview</th>
							<th>Status</th>
							<th>File</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($users): ?>
							<?php foreach ($users as $user): ?>
							<tr>
								<td><?php echo (int)$user['id']; ?></td>
								<td><?php echo h((string)$user['name']); ?></td>
								<td>
									<?php if (!empty($user['avatar_url'])): ?>
										<img class="avatar-thumb" src="<?php echo h((string)$user['avatar_url']); ?>" alt="<?php echo h((string)$user['name']); ?> profile picture">
									<?php else: ?>
										<span class="avatar-missing">NO IMG</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if (($user['avatar_status'] ?? '') === 'approved'): ?>
										<span class="status-pill status-active">APPROVED</span>
									<?php elseif (($user['avatar_status'] ?? '') === 'rejected'): ?>
										<span class="status-pill status-suspended">REJECTED</span>
									<?php elseif (($user['avatar_status'] ?? '') === 'pending'): ?>
										<span class="status-pill status-pending">PENDING</span>
									<?php else: ?>
										<span class="status-pill status-none">NONE</span>
									<?php endif; ?>
								</td>
								<td><span class="file-path"><?php echo h((string)($user['avatar'] ?: 'No file uploaded')); ?></span></td>
								<td>
									<?php if (!empty($user['avatar_url'])): ?>
										<a class="view-link" href="./view-profile-picture.php?user_id=<?php echo (int)$user['id']; ?>" target="_blank" rel="noopener noreferrer">View Picture</a>
									<?php else: ?>
										<span class="file-path">Unavailable</span>
									<?php endif; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr><td class="empty-state" colspan="6">No users found.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</section>
	</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const userStatusLabels = <?php echo json_encode($userStatusLabels, JSON_UNESCAPED_SLASHES); ?>;
const userStatusValues = <?php echo json_encode($userStatusData, JSON_UNESCAPED_SLASHES); ?>;
const userStatusColors = <?php echo json_encode($userStatusColors, JSON_UNESCAPED_SLASHES); ?>;

const chartEl = document.getElementById('userStatusChart');
if (chartEl) {
	const hasData = userStatusValues.some((value) => value > 0);
	new Chart(chartEl, {
		type: 'doughnut',
		data: {
			labels: hasData ? userStatusLabels : ['No data'],
			datasets: [{
				data: hasData ? userStatusValues : [1],
				backgroundColor: hasData ? userStatusColors : ['#cbd5e1'],
				borderWidth: 0,
				hoverOffset: 4,
			}],
		},
		options: {
			maintainAspectRatio: false,
			plugins: {
				legend: {
					position: 'bottom',
					labels: {
						usePointStyle: true,
						padding: 14,
						boxWidth: 10,
					},
				},
			},
			cutout: '64%',
		},
	});
}
</script>
</body>
</html>
