<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if (empty($_SESSION['user_id'])) {
	header('Location: ./login.php');
	exit;
}

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/ai_moderation.php';
$db = $conn ?? null;
$focusPostId = max(0, (int)($_GET['job_id'] ?? 0));
$posts = [];
$totalUsers = 0;
$totalPosts = 0;
$pendingPosts = 0;
$approvedPosts = 0;
$rejectedPosts = 0;
$inProgressPosts = 0;
$closedPosts = 0;

function db_count(mysqli $db, string $sql): int {
	$res = @mysqli_query($db, $sql);
	if (!$res) {
		return 0;
	}
	$row = @mysqli_fetch_assoc($res);
	@mysqli_free_result($res);
	return (int)($row['c'] ?? 0);
}

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = strtolower(trim((string)($_POST['action'] ?? '')));
	$postId = (int)($_POST['post_id'] ?? 0);
	$statusMap = [
		'approve' => 'approved',
		'reject' => 'rejected',
		'pending' => 'pending',
	];

	if ($postId > 0 && $action === 'delete') {
		if ($stmt = @mysqli_prepare($db, "UPDATE jobs SET status = 'deleted' WHERE id = ? AND LOWER(COALESCE(status,'')) = 'rejected'")) {
			mysqli_stmt_bind_param($stmt, 'i', $postId);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_close($stmt);
		}
		header('Location: ./post-approvals.php');
		exit;
	}

	if ($postId > 0 && isset($statusMap[$action])) {
		$newStatus = $statusMap[$action];
		if ($stmt = @mysqli_prepare($db, "UPDATE jobs SET status = ? WHERE id = ? AND LOWER(COALESCE(status,'')) <> 'deleted'")) {
			mysqli_stmt_bind_param($stmt, 'si', $newStatus, $postId);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_close($stmt);
		}
	}

	header('Location: ./post-approvals.php');
	exit;
}

if ($db) {
	ai_ensure_moderation_schema($db);
	$hasAiDecision = ai_table_has_column($db, 'jobs', 'ai_decision');
	$hasAiScore = ai_table_has_column($db, 'jobs', 'ai_score');
	$hasAiReason = ai_table_has_column($db, 'jobs', 'ai_reason');

	$aiDecisionExpr = $hasAiDecision ? "LOWER(COALESCE(j.ai_decision, 'pending')) AS ai_decision" : "'pending' AS ai_decision";
	$aiScoreExpr = $hasAiScore ? "COALESCE(j.ai_score, NULL) AS ai_score" : "NULL AS ai_score";
	$aiReasonExpr = $hasAiReason ? "COALESCE(j.ai_reason, '') AS ai_reason" : "'' AS ai_reason";

	$totalUsers = db_count($db, "SELECT COUNT(*) AS c FROM users");
	$totalPosts = db_count($db, "SELECT COUNT(*) AS c FROM jobs");
	$pendingPosts = db_count($db, "SELECT COUNT(*) AS c FROM jobs WHERE LOWER(COALESCE(status,'pending')) = 'pending'");
	$approvedPosts = db_count($db, "SELECT COUNT(*) AS c FROM jobs WHERE LOWER(COALESCE(status,'approved')) IN ('approved','open')");
	$rejectedPosts = db_count($db, "SELECT COUNT(*) AS c FROM jobs WHERE LOWER(COALESCE(status,'')) = 'rejected'");
	$inProgressPosts = db_count($db, "SELECT COUNT(*) AS c FROM jobs WHERE LOWER(REPLACE(REPLACE(COALESCE(status,''), '-', ' '), '_', ' ')) = 'in progress'");
	$closedPosts = db_count($db, "SELECT COUNT(*) AS c FROM jobs WHERE LOWER(COALESCE(status,'')) = 'closed'");

	$sql = "SELECT
		j.id,
		COALESCE(j.title, 'Untitled') AS title,
		COALESCE(j.date_needed, DATE(j.posted_at), CURDATE()) AS date_needed,
		LOWER(COALESCE(j.status, 'pending')) AS status,
		{$aiDecisionExpr},
		{$aiScoreExpr},
		{$aiReasonExpr},
		COALESCE(
			NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), ''),
			NULLIF(u.username, ''),
			CONCAT('User #', j.user_id)
		) AS client
	FROM jobs j
	LEFT JOIN users u ON u.id = j.user_id
	ORDER BY j.id DESC";

	if ($res = @mysqli_query($db, $sql)) {
		while ($row = @mysqli_fetch_assoc($res)) {
			$posts[] = $row;
		}
		@mysqli_free_result($res);
	}
}

$postStatusLabels = ['Pending', 'Approved', 'Rejected', 'In Progress', 'Closed'];
$postStatusData = [
	(int)$pendingPosts,
	(int)$approvedPosts,
	(int)$rejectedPosts,
	(int)$inProgressPosts,
	(int)$closedPosts,
];
$postStatusColors = ['#f59e0b', '#22c55e', '#ef4444', '#8b5cf6', '#64748b'];
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Post Approvals • ServisyoHub</title>
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
		html, body { min-height: 100%; }
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
		}
		.logout-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			width: 100%;
			padding: 11px 12px;
			border-radius: 12px;
			border: 1px solid rgba(255,255,255,.22);
			background: rgba(239, 68, 68, .18);
			color: #fff;
			font-weight: 800;
			font-size: .9rem;
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

		.content {
			padding: 24px;
		}
		.hero {
			background: linear-gradient(135deg, rgba(0,120,166,.97), rgba(14,116,162,.84));
			color: #fff;
			border-radius: 24px;
			padding: 20px 22px;
			box-shadow: var(--shadow);
			margin-bottom: 18px;
		}
		.hero h2 {
			margin: 0;
			font-size: clamp(1.2rem, 2vw, 1.8rem);
			font-weight: 900;
		}
		.hero p {
			margin: 8px 0 0;
			opacity: .92;
		}
		.stats-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 12px;
			margin-bottom: 18px;
		}
		.stat-card {
			background: var(--surface);
			border: 1px solid rgba(255,255,255,.8);
			border-radius: 18px;
			padding: 14px;
			box-shadow: var(--shadow);
		}
		.stat-card .label {
			font-size: .74rem;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: .1em;
			color: var(--muted);
		}
		.stat-card .value {
			margin-top: 8px;
			font-size: 1.7rem;
			font-weight: 900;
			line-height: 1;
		}
		.stat-card .hint {
			margin-top: 6px;
			font-size: .86rem;
			color: var(--muted);
		}

		.overview-grid {
			display: grid;
			grid-template-columns: 1.1fr .9fr;
			gap: 14px;
			margin-bottom: 18px;
		}
		.chart-card {
			background: #fff;
			border: 1px solid var(--line);
			border-radius: 16px;
			padding: 14px;
		}
		.chart-card h4 {
			margin: 0 0 10px;
			font-size: .84rem;
			letter-spacing: .1em;
			text-transform: uppercase;
			color: var(--muted);
		}
		.chart-wrap {
			position: relative;
			height: 260px;
		}
		.quick-stats {
			display: grid;
			gap: 10px;
		}
		.quick-item {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 12px;
			border-radius: 12px;
			border: 1px solid var(--line);
			background: #fff;
		}
		.quick-item .name {
			font-weight: 700;
			color: #334155;
		}
		.quick-item .num {
			font-weight: 900;
		}

		.panel {
			background: var(--surface);
			border: 1px solid rgba(255,255,255,.8);
			border-radius: 24px;
			box-shadow: var(--shadow);
			padding: 20px;
		}
		.panel-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			margin-bottom: 10px;
		}
		.panel-head h3 {
			margin: 0;
			font-size: 1.08rem;
		}
		.chip {
			display: inline-flex;
			align-items: center;
			padding: 6px 10px;
			border-radius: 999px;
			background: #e0f2fe;
			color: #075985;
			font-size: .8rem;
			font-weight: 800;
		}
		.subhead {
			margin: 0 0 12px;
			color: var(--muted);
			font-size: .95rem;
			line-height: 1.5;
		}

		.table-wrap {
			max-height: 62vh;
			overflow: auto;
			border-radius: 16px;
			border: 1px solid var(--line);
			background: #fff;
		}
		.table {
			width: 100%;
			border-collapse: collapse;
		}
		.table thead th {
			position: sticky;
			top: 0;
			background: #f7fbfd;
			z-index: 1;
		}
		.table th,
		.table td {
			padding: 12px 10px;
			border-bottom: 1px solid var(--line);
			text-align: left;
			font-size: .94rem;
			vertical-align: top;
		}
		.table th {
			font-size: .76rem;
			text-transform: uppercase;
			letter-spacing: .1em;
			color: var(--muted);
		}
		.table td.title {
			overflow-wrap: anywhere;
			word-break: break-word;
		}
		.table td.date,
		.table td.status-cell,
		.table td.id {
			white-space: nowrap;
		}

		.status-pill {
			display: inline-flex;
			align-items: center;
			padding: 5px 12px;
			border-radius: 999px;
			font-size: .78rem;
			font-weight: 800;
			letter-spacing: .02em;
		}
		.status-pending { background: #fef3c7; color: #92400e; }
		.status-approved { background: #dcfce7; color: #166534; }
		.status-rejected { background: #fee2e2; color: #991b1b; }
		.status-deleted { background: #e2e8f0; color: #334155; }

		.actions-cell {
			min-width: 132px;
		}
		.ai-cell {
			min-width: 190px;
		}
		.ai-note {
			margin-top: 4px;
			color: var(--muted);
			font-size: .78rem;
			line-height: 1.35;
		}
		.action-stack {
			display: grid;
			gap: 8px;
		}
		.action-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 100%;
			height: 36px;
			padding: 0 10px;
			border-radius: 10px;
			font-size: .86rem;
			font-weight: 800;
			border: 0;
			cursor: pointer;
		}
		.action-btn.approve { background: #7cd4c4; color: #0b2c24; }
		.action-btn.reject { background: #ef4444; color: #fff; }
		.action-btn.delete {
			background: #334155;
			color: #fff;
			gap: 6px;
		}
		.action-btn.delete svg {
			width: 14px;
			height: 14px;
			stroke: currentColor;
			fill: none;
		}
		.action-btn.approve:hover { filter: brightness(.96); }
		.action-btn.reject:hover { filter: brightness(.94); }
		.action-btn.delete:hover { background: #1f2937; }
		.action-na { color: var(--muted); font-weight: 700; }
		.row-focus {
			background: rgba(0, 120, 166, .14);
			outline: 2px solid #0078a6;
			outline-offset: -2px;
		}

		@media (max-width: 1000px) {
			.admin-shell { grid-template-columns: 1fr; }
			.sidebar {
				position: static;
				height: auto;
				border-right: 0;
				border-bottom: 1px solid rgba(255,255,255,.08);
			}
			.nav { grid-template-columns: repeat(2, minmax(0, 1fr)); }
			.stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
			.overview-grid { grid-template-columns: 1fr; }
		}
		@media (max-width: 680px) {
			.content { padding: 14px; }
			.panel, .hero { border-radius: 18px; }
			.table th, .table td { font-size: .88rem; padding: 10px 8px; }
			.stats-grid { grid-template-columns: 1fr; }
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
			<a href="./admin.php" aria-label="Dashboard">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z"/></svg>
				<span>Dashboard</span>
			</a>
			<a href="./post-approvals.php" class="active" aria-current="page" aria-label="Post Approvals">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
				<span>Post approvals</span>
			</a>
			<a href="./manage-users.php" aria-label="Manage users">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
				<span>Manage users</span>
			</a>
			<a href="./manage-offers.php" aria-label="Manage offers">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18"/><path d="M7 3v8"/><path d="M17 3v8"/><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 14h8"/></svg>
				<span>Manage offers</span>
			</a>
			<a href="./documents.php" aria-label="Documents">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
				<span>Documents</span>
			</a>
			<a href="./archive.php" aria-label="Archive">
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
		<section class="hero">
			<h2>Post Approvals</h2>
			<p>Review pending client job posts and decide which listings go live in the marketplace.</p>
		</section>

		<section class="stats-grid" aria-label="Post approval metrics">
			<div class="stat-card">
				<div class="label">Total Users</div>
				<div class="value"><?php echo (int)$totalUsers; ?></div>
				<div class="hint">Registered accounts</div>
			</div>
			<div class="stat-card">
				<div class="label">Total Posts</div>
				<div class="value"><?php echo (int)$totalPosts; ?></div>
				<div class="hint">All job listings</div>
			</div>
			<div class="stat-card">
				<div class="label">Pending Posts</div>
				<div class="value"><?php echo (int)$pendingPosts; ?></div>
				<div class="hint">Waiting for review</div>
			</div>
			<div class="stat-card">
				<div class="label">Approved Posts</div>
				<div class="value"><?php echo (int)$approvedPosts; ?></div>
				<div class="hint">Live or approved listings</div>
			</div>
		</section>

		<section class="panel" aria-label="Post overview" style="margin-bottom:18px;">
			<div class="panel-head">
				<h3>Post Status Overview</h3>
				<span class="chip">Live snapshot</span>
			</div>
			<p class="subhead">Track moderation outcomes and current listing states before processing new approvals.</p>
			<div class="overview-grid">
				<div class="chart-card">
					<h4>Post Status Graph</h4>
					<div class="chart-wrap"><canvas id="postStatusChart"></canvas></div>
				</div>
				<div class="quick-stats">
					<div class="quick-item"><span class="name">Pending</span><span class="num"><?php echo (int)$pendingPosts; ?></span></div>
					<div class="quick-item"><span class="name">Approved</span><span class="num"><?php echo (int)$approvedPosts; ?></span></div>
					<div class="quick-item"><span class="name">Rejected</span><span class="num"><?php echo (int)$rejectedPosts; ?></span></div>
					<div class="quick-item"><span class="name">In Progress</span><span class="num"><?php echo (int)$inProgressPosts; ?></span></div>
					<div class="quick-item"><span class="name">Closed</span><span class="num"><?php echo (int)$closedPosts; ?></span></div>
				</div>
			</div>
		</section>

		<section class="panel" aria-label="Client Posts">
			<div class="panel-head">
				<h3>Approve or Reject Client Posts</h3>
				<span class="chip"><?php echo count($posts); ?> total</span>
			</div>
			<p class="subhead">Pending posts are prioritized first. Approved and rejected records remain visible for quick auditing.</p>

			<div class="table-wrap">
				<table class="table" aria-label="Client Posts">
					<thead>
						<tr>
							<th>ID</th>
							<th>Client</th>
							<th>Title</th>
							<th>Date</th>
							<th>Status</th>
							<th>AI Review</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($posts)): ?>
						<?php foreach ($posts as $post): ?>
						<tr id="job-row-<?php echo (int)$post['id']; ?>"<?php echo $focusPostId === (int)$post['id'] ? ' class="row-focus"' : ''; ?>>
							<td class="id"><?php echo (int)$post['id']; ?></td>
							<td class="client"><?php echo htmlspecialchars((string)$post['client'], ENT_QUOTES); ?></td>
							<td class="title"><?php echo htmlspecialchars((string)$post['title'], ENT_QUOTES); ?></td>
							<td class="date"><?php echo htmlspecialchars((string)$post['date_needed'], ENT_QUOTES); ?></td>
							<td class="status-cell">
								<?php $st = strtolower((string)($post['status'] ?? 'pending')); ?>
								<?php $aiDecision = strtolower((string)($post['ai_decision'] ?? 'pending')); ?>
								<?php $aiScore = isset($post['ai_score']) ? (float)$post['ai_score'] : null; ?>
								<?php $aiReason = trim((string)($post['ai_reason'] ?? '')); ?>
								<?php if ($st === 'approved'): ?>
									<span class="status-pill status-approved">APPROVED</span>
								<?php elseif ($st === 'rejected'): ?>
									<span class="status-pill status-rejected">REJECTED</span>
									<?php elseif ($st === 'deleted'): ?>
										<span class="status-pill status-deleted">DELETED</span>
								<?php else: ?>
									<span class="status-pill status-pending">PENDING</span>
								<?php endif; ?>
							</td>
							<td class="ai-cell">
								<?php if ($aiDecision === 'approve'): ?>
									<span class="status-pill status-approved">AI APPROVE</span>
								<?php elseif ($aiDecision === 'reject'): ?>
									<span class="status-pill status-rejected">AI REJECT</span>
								<?php else: ?>
									<span class="status-pill status-pending">AI REVIEW</span>
								<?php endif; ?>
								<div class="ai-note">
									<?php if ($aiScore !== null): ?>Risk score: <?php echo htmlspecialchars(number_format($aiScore, 2), ENT_QUOTES); ?><?php else: ?>Risk score: N/A<?php endif; ?>
									<?php if ($aiReason !== ''): ?><br><?php echo htmlspecialchars($aiReason, ENT_QUOTES); ?><?php endif; ?>
								</div>
							</td>
							<td class="actions-cell">
								<?php if ($st === 'pending'): ?>
									<div class="action-stack">
										<form method="post">
											<input type="hidden" name="action" value="approve">
											<input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
											<button type="submit" class="action-btn approve">Approve</button>
										</form>
										<form method="post" onsubmit="return confirm('Reject this post?');">
											<input type="hidden" name="action" value="reject">
											<input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
											<button type="submit" class="action-btn reject">Reject</button>
										</form>
									</div>
								<?php elseif ($st === 'approved'): ?>
									<div class="action-stack">
										<form method="post" onsubmit="return confirm('Mark this post as pending for re-review?');">
											<input type="hidden" name="action" value="pending">
											<input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
											<button type="submit" class="action-btn delete">Mark Pending</button>
										</form>
										<form method="post" onsubmit="return confirm('Reject this approved post?');">
											<input type="hidden" name="action" value="reject">
											<input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
											<button type="submit" class="action-btn reject">Reject</button>
										</form>
									</div>
								<?php elseif ($st === 'rejected'): ?>
									<div class="action-stack">
										<form method="post" onsubmit="return confirm('Approve this rejected post?');">
											<input type="hidden" name="action" value="approve">
											<input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
											<button type="submit" class="action-btn approve">Approve</button>
										</form>
										<form method="post" onsubmit="return confirm('Move this post back to pending?');">
											<input type="hidden" name="action" value="pending">
											<input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
											<button type="submit" class="action-btn delete">Mark Pending</button>
										</form>
										<form method="post" onsubmit="return confirm('Delete this rejected post? It will move to Archive.');">
											<input type="hidden" name="action" value="delete">
											<input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
											<button type="submit" class="action-btn delete" title="Delete post">
												<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M6 6l1 15a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-15"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
												<span>Delete</span>
											</button>
										</form>
									</div>
								<?php else: ?>
									<span class="action-na">-</span>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php else: ?>
						<tr>
							<td colspan="7" style="color:#64748b; text-align:center; font-weight:700;">No posts found.</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</section>
	</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const postStatusLabels = <?php echo json_encode($postStatusLabels, JSON_UNESCAPED_SLASHES); ?>;
const postStatusValues = <?php echo json_encode($postStatusData, JSON_UNESCAPED_SLASHES); ?>;
const postStatusColors = <?php echo json_encode($postStatusColors, JSON_UNESCAPED_SLASHES); ?>;
const focusPostId = <?php echo (int)$focusPostId; ?>;

const chartEl = document.getElementById('postStatusChart');
if (chartEl) {
	const hasData = postStatusValues.some((value) => value > 0);
	new Chart(chartEl, {
		type: 'doughnut',
		data: {
			labels: hasData ? postStatusLabels : ['No data'],
			datasets: [{
				data: hasData ? postStatusValues : [1],
				backgroundColor: hasData ? postStatusColors : ['#cbd5e1'],
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

if (focusPostId > 0) {
	const row = document.getElementById(`job-row-${focusPostId}`);
	if (row) {
		row.scrollIntoView({ behavior: 'smooth', block: 'center' });
	}
}
</script>
</body>
</html>
