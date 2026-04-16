<?php
session_start();
date_default_timezone_set('Asia/Manila');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if (empty($_SESSION['user_id'])) {
	header('Location: ./login.php');
	exit;
}

require_once __DIR__ . '/../config/db_connect.php';

$db = $conn ?? ($mysqli ?? null);
if (!$db instanceof mysqli) {
	http_response_code(500);
	exit('Database connection unavailable.');
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

function db_rows(mysqli $db, string $sql): array {
	$res = @mysqli_query($db, $sql);
	if (!$res) {
		return [];
	}
	$rows = [];
	while ($row = @mysqli_fetch_assoc($res)) {
		$rows[] = $row;
	}
	@mysqli_free_result($res);
	return $rows;
}

function offers_has_column(mysqli $db, string $column): bool {
	$column = mysqli_real_escape_string($db, $column);
	$res = @mysqli_query($db, "SHOW COLUMNS FROM offers LIKE '{$column}'");
	if (!$res) {
		return false;
	}
	$exists = @mysqli_num_rows($res) > 0;
	@mysqli_free_result($res);
	return $exists;
}

function h(?string $value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function pretty_status(string $status): string {
	$status = strtolower(trim($status));
	$map = [
		'rejected' => 'Deleted',
		'denied' => 'Deleted',
		'withdrawn' => 'Deleted',
		'cancelled' => 'Deleted',
		'canceled' => 'Deleted',
		'closed' => 'Closed',
		'suspended' => 'Suspended',
		'deleted' => 'Deleted',
	];
	return $map[$status] ?? ucfirst($status);
}

function money_value($value): string {
	if ($value === null || $value === '') {
		return 'N/A';
	}
	return 'PHP ' . number_format((float)$value, 2);
}

$userId = (int)$_SESSION['user_id'];
$isAdmin = false;
if ($stmt = mysqli_prepare($db, 'SELECT role FROM users WHERE id = ?')) {
	mysqli_stmt_bind_param($stmt, 'i', $userId);
	mysqli_stmt_execute($stmt);
	$res = mysqli_stmt_get_result($stmt);
	$row = $res ? mysqli_fetch_assoc($res) : null;
	$role = strtolower((string)($row['role'] ?? 'user'));
	$isAdmin = $role === 'admin';
	mysqli_stmt_close($stmt);
}

if (!$isAdmin) {
	header('Location: ./home-gawain.php');
	exit;
}

$archiveNotice = $_SESSION['archive_notice'] ?? null;
unset($_SESSION['archive_notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = strtolower(trim((string)($_POST['action'] ?? '')));
	$offerId = (int)($_POST['offer_id'] ?? 0);
	$jobId = (int)($_POST['job_id'] ?? 0);

	if ($action === 'retrieve_offer' && $offerId > 0) {
		$updated = false;
		if ($stmt = mysqli_prepare($db, "UPDATE offers
			SET status = 'pending'
			WHERE id = ?
			  AND LOWER(COALESCE(status, 'pending')) IN ('rejected','withdrawn','denied','cancelled','canceled')")) {
			mysqli_stmt_bind_param($stmt, 'i', $offerId);
			mysqli_stmt_execute($stmt);
			$updated = mysqli_stmt_affected_rows($stmt) > 0;
			mysqli_stmt_close($stmt);
		}

		if ($updated) {
			if (offers_has_column($db, 'admin_status')) {
				if ($stmt = mysqli_prepare($db, "UPDATE offers SET admin_status = 'pending' WHERE id = ?")) {
					mysqli_stmt_bind_param($stmt, 'i', $offerId);
					mysqli_stmt_execute($stmt);
					mysqli_stmt_close($stmt);
				}
			}
			if (offers_has_column($db, 'citizen_status')) {
				if ($stmt = mysqli_prepare($db, "UPDATE offers SET citizen_status = 'pending' WHERE id = ?")) {
					mysqli_stmt_bind_param($stmt, 'i', $offerId);
					mysqli_stmt_execute($stmt);
					mysqli_stmt_close($stmt);
				}
			}

			$_SESSION['archive_notice'] = [
				'type' => 'success',
				'message' => 'Offer has been retrieved and moved back to pending.',
			];
		} else {
			$_SESSION['archive_notice'] = [
				'type' => 'error',
				'message' => 'Offer could not be retrieved. It may already be active.',
			];
		}

		header('Location: ./archive.php');
		exit;
	}

	if ($action === 'retrieve_job' && $jobId > 0) {
		$updated = false;
		if ($stmt = mysqli_prepare($db, "UPDATE jobs
			SET status = 'pending'
			WHERE id = ?
			  AND LOWER(COALESCE(status, '')) = 'deleted'")) {
			mysqli_stmt_bind_param($stmt, 'i', $jobId);
			mysqli_stmt_execute($stmt);
			$updated = mysqli_stmt_affected_rows($stmt) > 0;
			mysqli_stmt_close($stmt);
		}

		if ($updated) {
			$_SESSION['archive_notice'] = [
				'type' => 'success',
				'message' => 'Job has been retrieved and moved back to pending.',
			];
		} else {
			$_SESSION['archive_notice'] = [
				'type' => 'error',
				'message' => 'Job could not be retrieved. It may already be active.',
			];
		}

		header('Location: ./archive.php');
		exit;
	}
}

// Fetch archive statistics
$totalRejectedOffers = db_count($db, "SELECT COUNT(*) c FROM offers WHERE LOWER(COALESCE(status, 'pending')) IN ('rejected','withdrawn','denied','cancelled','canceled')");
$totalDeletedJobs = db_count($db, "SELECT COUNT(*) c FROM jobs WHERE LOWER(COALESCE(status, '')) = 'deleted'");
$totalSuspendedUsers = db_count($db, "SELECT COUNT(*) c FROM users WHERE LOWER(COALESCE(avatar_status, '')) IN ('suspended','deleted')");

// Fetch deleted/rejected offers
$deletedOffers = db_rows($db, "SELECT
	o.id,
	o.job_id,
	CONCAT('Job #', o.job_id) AS job_title,
	COALESCE(o.amount, 0) AS amount,
	LOWER(COALESCE(o.status, 'pending')) AS status,
	o.created_at,
	COALESCE(
		NULLIF(TRIM(CONCAT(COALESCE(k.first_name,''), ' ', COALESCE(k.last_name,''))), ''),
		NULLIF(k.username, ''),
		CONCAT('Kasangga #', o.user_id)
	) AS kasangga_name
FROM offers o
LEFT JOIN users k ON k.id = o.user_id
WHERE LOWER(COALESCE(o.status, 'pending')) IN ('rejected','withdrawn','denied','cancelled','canceled')
ORDER BY o.created_at DESC
LIMIT 50");

// Fetch deleted jobs
$closedJobs = db_rows($db, "SELECT
	j.id,
	j.title,
	j.category,
	j.location,
	COALESCE(j.budget, 0) AS budget,
	LOWER(COALESCE(j.status, 'open')) AS status,
	j.posted_at,
	COALESCE(
		NULLIF(TRIM(CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,''))), ''),
		NULLIF(u.username, ''),
		CONCAT('User #', j.user_id)
	) AS client_name
FROM jobs j
LEFT JOIN users u ON u.id = j.user_id
WHERE LOWER(COALESCE(j.status, '')) = 'deleted'
ORDER BY j.posted_at DESC
LIMIT 50");
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Archive • ServisyoHub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">
	<style>
		:root {
			--brand: #0078a6;
			--brand-dark: #0b2c24;
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
		a { color: inherit; text-decoration: none; }
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
			flex-direction: column;
			align-items: center;
			gap: 10px;
			padding: 6px 8px 18px;
			margin-bottom: 10px;
			border-bottom: 1px solid rgba(255,255,255,.14);
		}
		.brand-logo {
			width: 56px;
			height: 56px;
			object-fit: contain;
		}
		.brand h1 {
			margin: 0;
			font-size: 1.2rem;
			line-height: 1.1;
			text-align: center;
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
		}
		.logout-btn svg { width: 16px; height: 16px; flex: 0 0 16px; }
		.logout-btn:hover {
			background: rgba(239, 68, 68, .28);
			border-color: rgba(255,255,255,.35);
		}
		.content { padding: 24px; }
		.hero {
			background: linear-gradient(135deg, rgba(0,120,166,.97), rgba(14,116,162,.84));
			color: #fff;
			border-radius: 28px;
			padding: 28px;
			box-shadow: var(--shadow);
			margin-bottom: 18px;
		}
		.hero h2 { margin: 0; font-size: clamp(1.2rem, 2vw, 1.8rem); font-weight: 900; }
		.hero p { margin: 10px 0 0; max-width: 62ch; line-height: 1.6; opacity: .92; }
		.panel {
			background: var(--surface);
			border: 1px solid rgba(255,255,255,.8);
			border-radius: 24px;
			box-shadow: var(--shadow);
			padding: 20px;
		}
		.overview-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 16px;
			margin-bottom: 18px;
		}
		.overview-grid > .panel {
			width: 100%;
			min-width: 0;
			align-items: stretch;
		}
		.notice {
			padding: 12px 14px;
			border-radius: 12px;
			font-weight: 700;
			margin-bottom: 12px;
			border: 1px solid transparent;
		}
		.notice.success {
			background: rgba(34, 197, 94, 0.12);
			border-color: rgba(34, 197, 94, 0.25);
			color: #166534;
		}
		.notice.error {
			background: rgba(239, 68, 68, 0.12);
			border-color: rgba(239, 68, 68, 0.25);
			color: #b91c1c;
		}
		.action-btn {
			border: 0;
			border-radius: 8px;
			padding: 8px 12px;
			font-weight: 700;
			cursor: pointer;
			font-size: 0.85rem;
		}
		.retrieve-btn {
			background: rgba(37, 99, 235, 0.12);
			color: #1d4ed8;
			border: 1px solid rgba(37, 99, 235, 0.2);
		}
		.retrieve-btn:hover {
			background: rgba(37, 99, 235, 0.2);
		}
		@media (max-width: 760px) {
			.admin-shell { grid-template-columns: 1fr; }
			.sidebar {
				position: static;
				height: auto;
				border-right: 0;
				border-bottom: 1px solid rgba(255,255,255,.08);
			}
			.nav { grid-template-columns: repeat(2, minmax(0, 1fr)); }
			.content { padding: 18px; }
			.overview-grid { grid-template-columns: 1fr; }
		}
	</style>
</head>
<body>
<div class="admin-shell">
	<aside class="sidebar">
		<div class="brand">
			<img class="brand-logo" src="../assets/images/job_logo.png" alt="ServisyoHub">
			<h1>Admin Console</h1>
		</div>
		<nav class="nav" aria-label="Admin navigation">
			<a href="./admin.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z"/></svg>
				<span>Dashboard</span>
			</a>
			<a href="./post-approvals.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
				<span>Post approvals</span>
			</a>
			<a href="./manage-users.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
				<span>Manage users</span>
			</a>
			<a href="./manage-offers.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18"/><path d="M7 3v8"/><path d="M17 3v8"/><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 14h8"/></svg>
				<span>Manage offers</span>
			</a>
			<a href="./documents.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
				<span>Documents</span>
			</a>
			<a href="./archive.php" class="active" aria-current="page">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="4" rx="1"/><path d="M5 9v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9"/><path d="M9 13h6"/></svg>
				<span>Archive</span>
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
		<?php if (is_array($archiveNotice) && !empty($archiveNotice['message'])): ?>
			<div class="notice <?php echo h((string)($archiveNotice['type'] ?? 'success')); ?>">
				<?php echo h((string)$archiveNotice['message']); ?>
			</div>
		<?php endif; ?>

		<section class="hero">
			<h2>Archive</h2>
			<p>Review deleted, rejected, withdrawn, and closed items. Archive statistics and detailed records are displayed below.</p>
		</section>

		<!-- Overview Cards -->
		<div class="overview-grid">
			<div class="panel">
				<div style="display: flex; justify-content: space-between; align-items: center;">
					<div>
						<div style="font-size: 2.4rem; font-weight: 900; color: var(--brand);"><?php echo $totalRejectedOffers; ?></div>
						<div style="font-size: 0.95rem; color: var(--muted); margin-top: 6px;">Deleted Offers</div>
					</div>
					<svg viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 48px; height: 48px; opacity: 0.5;">
						<rect x="3" y="5" width="18" height="4" rx="1"></rect>
						<path d="M5 9v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9"></path>
						<path d="M9 13h6"></path>
					</svg>
				</div>
			</div>
			<div class="panel">
				<div style="display: flex; justify-content: space-between; align-items: center;">
					<div>
						<div style="font-size: 2.4rem; font-weight: 900; color: #f59e0b;"><?php echo $totalDeletedJobs; ?></div>
						<div style="font-size: 0.95rem; color: var(--muted); margin-top: 6px;">Deleted Jobs</div>
					</div>
					<svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.5" style="width: 48px; height: 48px; opacity: 0.3;"><path d="M9 12l2 2 4-4M7 20h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2z"/></svg>
				</div>
			</div>
		</div>

		<!-- Deleted Offers Section -->
		<section class="panel" style="margin-top: 20px;">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--line);">
				<h3 style="margin: 0; font-size: 1.2rem;">Deleted Offers</h3>
				<span style="background: rgba(239, 68, 68, 0.15); color: #dc2626; font-weight: 700; font-size: 0.9rem; padding: 6px 12px; border-radius: 8px;"><?php echo count($deletedOffers); ?> records</span>
			</div>
			<?php if (!empty($deletedOffers)): ?>
				<div style="overflow-x: auto;">
					<table style="width: 100%; border-collapse: collapse;">
						<thead>
							<tr style="border-bottom: 2px solid var(--line);">
									<th style="text-align: center; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Offer ID</th>
									<th style="text-align: center; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Job ID</th>
								<th style="text-align: left; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Kasangga</th>
								<th style="text-align: left; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Amount</th>
								<th style="text-align: center; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Status</th>
								<th style="text-align: center; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Created</th>
								<th style="text-align: center; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($deletedOffers as $offer): ?>
								<tr style="border-bottom: 1px solid var(--line); hover: background rgba(0,120,166,0.04);">
												<td style="padding: 12px; font-weight: 600; text-align: center;">#<?php echo h($offer['id']); ?></td>
												<td style="padding: 12px; text-align: center;">#<?php echo h($offer['job_id']); ?></td>
									<td style="padding: 12px;"><?php echo h($offer['kasangga_name']); ?></td>
									<td style="padding: 12px; font-weight: 600; color: var(--brand);"><?php echo money_value($offer['amount']); ?></td>
									<td style="padding: 12px; text-align: center;">
										<span style="background: rgba(239, 68, 68, 0.15); color: #dc2626; font-weight: 700; font-size: 0.85rem; padding: 6px 10px; border-radius: 6px;">
											<?php echo pretty_status($offer['status']); ?>
										</span>
									</td>
									<td style="padding: 12px; font-size: 0.9rem; color: var(--muted); text-align: center;"><?php echo date('M d, Y', strtotime($offer['created_at'])); ?></td>
									<td style="padding: 12px; text-align: center;">
										<form method="post" style="margin: 0;">
											<input type="hidden" name="action" value="retrieve_offer">
											<input type="hidden" name="offer_id" value="<?php echo h((string)$offer['id']); ?>">
											<button type="submit" class="action-btn retrieve-btn">Retrieve</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else: ?>
				<div style="padding: 40px 20px; text-align: center; color: var(--muted);">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; margin: 0 auto 12px; opacity: 0.4;"><path d="M9 12l2 2 4-4M7 20h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2z"/></svg>
					<p style="margin: 0;">No rejected or withdrawn offers.</p>
				</div>
			<?php endif; ?>
		</section>

		<!-- Deleted Jobs Section -->
		<section class="panel" style="margin-top: 20px;">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--line);">
				<h3 style="margin: 0; font-size: 1.2rem;">Deleted Jobs</h3>
				<span style="background: rgba(251, 146, 60, 0.15); color: #ea580c; font-weight: 700; font-size: 0.9rem; padding: 6px 12px; border-radius: 8px;"><?php echo count($closedJobs); ?> jobs</span>
			</div>
			<?php if (!empty($closedJobs)): ?>
				<div style="overflow-x: auto;">
					<table style="width: 100%; border-collapse: collapse;">
						<thead>
							<tr style="border-bottom: 2px solid var(--line);">
									<th style="text-align: center; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Job ID</th>
								<th style="text-align: left; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Title</th>
								<th style="text-align: left; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Category</th>
								<th style="text-align: left; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Location</th>
								<th style="text-align: left; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Budget</th>
								<th style="text-align: center; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Status</th>
								<th style="text-align: center; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Posted</th>
								<th style="text-align: center; padding: 12px; font-weight: 700; color: var(--muted); font-size: 0.95rem;">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($closedJobs as $job): ?>
								<tr style="border-bottom: 1px solid var(--line);">
												<td style="padding: 12px; font-weight: 600; text-align: center;">#<?php echo h($job['id']); ?></td>
									<td style="padding: 12px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo h(substr($job['title'], 0, 40)); ?></td>
									<td style="padding: 12px; font-size: 0.9rem;"><?php echo h($job['category']); ?></td>
									<td style="padding: 12px; font-size: 0.9rem;"><?php echo h(substr($job['location'], 0, 30)); ?></td>
									<td style="padding: 12px; font-weight: 600; color: var(--brand);"><?php echo money_value($job['budget']); ?></td>
									<td style="padding: 12px; text-align: center;">
										<span style="background: rgba(251, 146, 60, 0.15); color: #ea580c; font-weight: 700; font-size: 0.85rem; padding: 6px 10px; border-radius: 6px;">
											<?php echo pretty_status($job['status']); ?>
										</span>
									</td>
									<td style="padding: 12px; font-size: 0.9rem; color: var(--muted); text-align: center;"><?php echo date('M d, Y', strtotime($job['posted_at'])); ?></td>
									<td style="padding: 12px; text-align: center;">
										<form method="post" style="margin: 0;">
											<input type="hidden" name="action" value="retrieve_job">
											<input type="hidden" name="job_id" value="<?php echo h((string)$job['id']); ?>">
											<button type="submit" class="action-btn retrieve-btn">Retrieve</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else: ?>
				<div style="padding: 40px 20px; text-align: center; color: var(--muted);">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; margin: 0 auto 12px; opacity: 0.4;"><path d="M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z"/></svg>
					<p style="margin: 0;">No deleted jobs.</p>
				</div>
			<?php endif; ?>
		</section>
	</main>
</div>
</body>
</html>
