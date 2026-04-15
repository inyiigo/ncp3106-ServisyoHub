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

$db = $conn ?? ($mysqli ?? null);
if (!$db instanceof mysqli) {
	http_response_code(500);
	exit('Database connection unavailable.');
}

function db_has_table(mysqli $db, string $table): bool {
	$table = mysqli_real_escape_string($db, $table);
	$res = @mysqli_query($db, "SHOW TABLES LIKE '{$table}'");
	if (!$res) {
		return false;
	}
	$exists = @mysqli_num_rows($res) > 0;
	@mysqli_free_result($res);
	return $exists;
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

function normalize_status(string $status): string {
	$status = strtolower(trim($status));
	$status = preg_replace('/\s+/', ' ', $status);
	if ($status === 'in_progress' || $status === 'in-progress' || $status === 'in progress') {
		return 'in progress';
	}
	if ($status === 'published') {
		return 'open';
	}
	if (in_array($status, ['open', 'pending', 'approved', 'closed', 'rejected'], true)) {
		return $status;
	}
	return 'other';
}

function pretty_status(string $status): string {
	$status = normalize_status($status);
	if ($status === 'in progress') {
		return 'In Progress';
	}
	return ucfirst($status);
}

function money_value($value): string {
	if ($value === null || $value === '') {
		return 'N/A';
	}
	return 'PHP ' . number_format((float)$value, 2);
}

function h(?string $value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$user_id = (int)$_SESSION['user_id'];
if ($stmt = mysqli_prepare($db, 'SELECT role FROM users WHERE id = ?')) {
	mysqli_stmt_bind_param($stmt, 'i', $user_id);
	mysqli_stmt_execute($stmt);
	$res = mysqli_stmt_get_result($stmt);
	$row = $res ? mysqli_fetch_assoc($res) : null;
	$user_role = isset($row['role']) ? strtolower((string)$row['role']) : 'user';
	mysqli_stmt_close($stmt);

	if ($user_role !== 'admin') {
		header('Location: ./home-gawain.php');
		exit;
	}
}

$totalUsers = db_count($db, 'SELECT COUNT(*) AS c FROM users');
$totalJobs = db_count($db, 'SELECT COUNT(*) AS c FROM jobs');
$totalOffers = db_count($db, 'SELECT COUNT(*) AS c FROM offers');
$totalComments = db_has_table($db, 'comments') ? db_count($db, 'SELECT COUNT(*) AS c FROM comments') : 0;

$activeUsers = db_count($db, "SELECT COUNT(DISTINCT user_id) AS c FROM jobs WHERE LOWER(COALESCE(status,'')) IN ('approved','open') AND user_id IS NOT NULL");
$openJobs = db_count($db, "SELECT COUNT(*) AS c FROM jobs WHERE LOWER(COALESCE(status,'open')) IN ('open','approved')");
$pendingJobs = db_count($db, "SELECT COUNT(*) AS c FROM jobs WHERE LOWER(COALESCE(status,'pending')) = 'pending'");
$pendingOffers = db_count($db, "SELECT COUNT(*) AS c FROM offers WHERE LOWER(COALESCE(status,'pending')) = 'pending'");
$recentJobs7d = db_count($db, "SELECT COUNT(*) AS c FROM jobs WHERE posted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");

$pendingUsers = 0;
$suspendedUsers = 0;
if (db_has_column($db, 'users', 'status')) {
	$pendingUsers = db_count($db, "SELECT COUNT(*) AS c FROM users WHERE LOWER(COALESCE(status,'pending')) = 'pending'");
	$suspendedUsers = db_count($db, "SELECT COUNT(*) AS c FROM users WHERE LOWER(COALESCE(status,'')) = 'suspended'");
}

$moderationQueue = $pendingJobs + $pendingOffers + $pendingUsers;
$activityCount = $totalJobs + $totalOffers + $totalComments;

$jobStatusCounts = [
	'open' => 0,
	'pending' => 0,
	'approved' => 0,
	'closed' => 0,
	'in progress' => 0,
	'rejected' => 0,
	'other' => 0,
];
if ($totalJobs > 0) {
	foreach (db_rows($db, "SELECT LOWER(COALESCE(status,'other')) AS status_key, COUNT(*) AS c FROM jobs GROUP BY LOWER(COALESCE(status,'other'))") as $row) {
		$key = normalize_status((string)($row['status_key'] ?? 'other'));
		$jobStatusCounts[$key] = ($jobStatusCounts[$key] ?? 0) + (int)($row['c'] ?? 0);
	}
}

$offerStatusCounts = [
	'accepted' => 0,
	'pending' => 0,
	'denied' => 0,
	'cancelled' => 0,
];
if ($totalOffers > 0) {
	foreach (db_rows($db, "SELECT LOWER(COALESCE(status,'other')) AS status_key, COUNT(*) AS c FROM offers GROUP BY LOWER(COALESCE(status,'other'))") as $row) {
		$key = strtolower(trim((string)($row['status_key'] ?? '')));
		if ($key === 'rejected') {
			$key = 'denied';
		} elseif ($key === 'withdrawn' || $key === 'canceled' || $key === 'cancelled') {
			$key = 'cancelled';
		} elseif ($key !== 'accepted' && $key !== 'pending') {
			$key = 'cancelled';
		}
		$offerStatusCounts[$key] = ($offerStatusCounts[$key] ?? 0) + (int)($row['c'] ?? 0);
	}
}

$recentJobs = db_rows($db, "SELECT id, title, category, COALESCE(location,'') AS location, COALESCE(budget,0) AS budget, LOWER(COALESCE(status,'open')) AS status, posted_at FROM jobs ORDER BY posted_at DESC, id DESC LIMIT 5");
$recentOffers = db_rows($db, "SELECT o.id, o.job_id, COALESCE(j.title, CONCAT('Job #', o.job_id)) AS job_title, COALESCE(o.amount, 0) AS amount, LOWER(COALESCE(o.status,'pending')) AS status, o.created_at FROM offers o LEFT JOIN jobs j ON j.id = o.job_id ORDER BY o.id DESC LIMIT 5");
$recentComments = db_has_table($db, 'comments') ? db_rows($db, "SELECT c.id, c.job_id, COALESCE(j.title, CONCAT('Job #', c.job_id)) AS job_title, LEFT(c.body, 80) AS body, c.created_at FROM comments c LEFT JOIN jobs j ON j.id = c.job_id ORDER BY c.created_at DESC, c.id DESC LIMIT 5") : [];

$jobApprovedTotal = (int)$jobStatusCounts['approved'] + (int)$jobStatusCounts['open'];

$jobStatusLabels = ['Approved', 'Pending', 'In Progress', 'Closed', 'Rejected'];
$jobStatusData = [
	(int)$jobApprovedTotal,
	(int)$jobStatusCounts['pending'],
	(int)$jobStatusCounts['in progress'],
	(int)$jobStatusCounts['closed'],
	(int)$jobStatusCounts['rejected'],
];
$jobStatusColors = ['#22c55e', '#f59e0b', '#8b5cf6', '#64748b', '#ef4444'];

$offerStatusLabels = ['Accepted', 'Pending', 'Denied', 'Cancelled'];
$offerStatusData = [
	(int)$offerStatusCounts['accepted'],
	(int)$offerStatusCounts['pending'],
	(int)$offerStatusCounts['denied'],
	(int)$offerStatusCounts['cancelled'],
];
$offerStatusColors = ['#22c55e', '#f59e0b', '#ef4444', '#64748b'];
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Admin Dashboard • ServisyoHub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">
	<style>
		:root {
			--brand: #0078a6;
			--brand-dark: #0b2c24;
			--bg: #f3f8fb;
			--surface: rgba(255,255,255,.88);
			--surface-strong: #ffffff;
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
		img { max-width: 100%; }

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
			display: grid;
			grid-template-columns: 1.6fr .9fr;
			gap: 18px;
			margin-bottom: 18px;
		}
		.hero-panel {
			position: relative;
			overflow: hidden;
			background: linear-gradient(135deg, rgba(0,120,166,.97), rgba(14,116,162,.84));
			color: #fff;
			border-radius: 28px;
			padding: 28px;
			box-shadow: var(--shadow);
		}
		.hero-panel::before,
		.hero-panel::after {
			content: '';
			position: absolute;
			border-radius: 999px;
			background: rgba(255,255,255,.12);
			pointer-events: none;
		}
		.hero-panel::before { width: 180px; height: 180px; right: -48px; top: -36px; }
		.hero-panel::after { width: 120px; height: 120px; right: 60px; bottom: -48px; }
		.hero-kicker {
			margin: 0 0 10px;
			font-size: .82rem;
			font-weight: 800;
			letter-spacing: .16em;
			text-transform: uppercase;
			opacity: .86;
		}
		.hero-title {
			margin: 0;
			font-size: clamp(1.8rem, 3vw, 3rem);
			line-height: 1.05;
			font-weight: 900;
			max-width: 12ch;
		}
		.hero-copy {
			margin: 14px 0 0;
			max-width: 58ch;
			font-size: 1rem;
			line-height: 1.6;
			opacity: .92;
		}
		.hero-metrics {
			display: grid;
			gap: 12px;
		}
		.hero-stat {
			background: rgba(255,255,255,.84);
			border: 1px solid rgba(255,255,255,.7);
			border-radius: 22px;
			padding: 18px;
			box-shadow: var(--shadow);
		}
		.hero-stat .label {
			font-size: .8rem;
			text-transform: uppercase;
			letter-spacing: .12em;
			font-weight: 800;
			color: var(--muted);
		}
		.hero-stat .value {
			margin-top: 8px;
			font-size: 1.9rem;
			font-weight: 900;
			color: var(--text);
		}
		.hero-stat .hint {
			margin-top: 6px;
			font-size: .92rem;
			color: var(--muted);
		}

		.metric-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 14px;
			margin-bottom: 18px;
		}
		.metric-card {
			background: var(--surface);
			backdrop-filter: blur(10px);
			border: 1px solid rgba(255,255,255,.8);
			border-radius: 22px;
			padding: 18px;
			box-shadow: var(--shadow);
			min-height: 132px;
		}
		.metric-card .label {
			color: var(--muted);
			font-size: .82rem;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: .1em;
		}
		.metric-card .value {
			margin-top: 8px;
			font-size: 2rem;
			font-weight: 900;
			line-height: 1;
		}
		.metric-card .foot {
			margin-top: 8px;
			color: var(--muted);
			font-size: .92rem;
			line-height: 1.45;
		}
		.metric-card.accent-blue { border-top: 5px solid #0ea5e9; }
		.metric-card.accent-green { border-top: 5px solid #22c55e; }
		.metric-card.accent-orange { border-top: 5px solid #f59e0b; }
		.metric-card.accent-purple { border-top: 5px solid #8b5cf6; }

		.grid-2 {
			display: grid;
			grid-template-columns: 1.25fr .85fr;
			gap: 18px;
			align-items: start;
		}
		.grid-2.overview-row {
			align-items: stretch;
		}
		.grid-2.overview-row > .panel {
			height: 100%;
		}
		.grid-2.recent-row {
			align-items: stretch;
		}
		.grid-2.recent-row > .panel {
			height: 100%;
		}
		.panel {
			background: var(--surface);
			border: 1px solid rgba(255,255,255,.8);
			border-radius: 24px;
			box-shadow: var(--shadow);
			padding: 20px;
		}
		.panel h2 {
			margin: 0 0 14px;
			font-size: 1.05rem;
		}
		.panel-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			margin-bottom: 8px;
		}
		.panel-head h2 {
			margin: 0;
		}
		.mini-action {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 6px 10px;
			border-radius: 999px;
			border: 1px solid #1f87ad;
			background: #1f87ad;
			color: #ffffff;
			font-size: .78rem;
			font-weight: 800;
			line-height: 1;
			white-space: nowrap;
		}
		.mini-action:hover {
			background: #176d8d;
			border-color: #176d8d;
		}
		.panel .subhead {
			margin: -6px 0 16px;
			color: var(--muted);
			font-size: .95rem;
			line-height: 1.5;
		}
		.chart-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 16px;
		}
		.chart-card {
			padding: 16px;
			border: 1px solid var(--line);
			border-radius: 18px;
			background: #fff;
			display: flex;
			flex-direction: column;
		}
		.chart-card h3 {
			margin: 0 0 12px;
			font-size: .95rem;
			color: var(--muted);
			text-transform: uppercase;
			letter-spacing: .1em;
		}
		.chart-wrap {
			position: relative;
			flex: 1;
			min-height: 260px;
		}

		.actions-grid {
			display: grid;
			grid-template-columns: 1fr;
			grid-auto-rows: 1fr;
			gap: 12px;
			height: 100%;
		}
		.panel.quick-actions-panel {
			display: flex;
			flex-direction: column;
		}
		.panel.quick-actions-panel .actions-grid {
			flex: 1;
			min-height: 0;
		}
		.action-card {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 14px;
			padding: 16px;
			border-radius: 18px;
			background: linear-gradient(135deg, #fff 0%, #f5fbfe 100%);
			border: 1px solid var(--line);
			box-shadow: 0 10px 22px rgba(2, 6, 23, .05);
			min-height: 58px;
			transition: background-color .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
		}
		.action-card strong {
			display: block;
			margin-bottom: 4px;
		}
		.action-card span {
			color: var(--muted);
			font-size: .92rem;
			line-height: 1.45;
		}
		.action-card svg {
			width: 20px;
			height: 20px;
			flex: 0 0 20px;
			color: var(--brand);
		}
		.action-card:hover {
			background: #e0f2fe;
			border-color: #0ea5e9;
			box-shadow: 0 12px 24px rgba(14, 165, 233, .18);
			transform: translateY(-1px);
		}

		.list-panel .table {
			width: 100%;
			border-collapse: collapse;
		}
		.list-panel.recent-fixed {
			display: flex;
			flex-direction: column;
			height: 560px;
		}
		.list-panel.recent-fixed .subhead {
			margin-bottom: 12px;
		}
		.list-scroll {
			flex: 1;
			min-height: 0;
			overflow-y: auto;
			overflow-x: hidden;
			padding-right: 4px;
		}
		.list-scroll .table thead th {
			position: sticky;
			top: 0;
			background: #f7fbfd;
			z-index: 1;
		}
		.list-scroll .empty-state {
			height: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
			text-align: center;
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
		.table .col-center {
			text-align: center;
		}
		.table tr:hover { background: rgba(14, 165, 233, .04); }
		.badge {
			display: inline-flex;
			align-items: center;
			padding: 5px 10px;
			border-radius: 999px;
			font-size: .76rem;
			font-weight: 800;
			text-transform: capitalize;
		}
		.badge.open,
		.badge.approved,
		.badge.active,
		.badge.accepted { background: #dcfce7; color: #166534; }
		.badge.pending { background: #fef3c7; color: #92400e; }
		.badge.closed,
		.badge.withdrawn { background: #e2e8f0; color: #475569; }
		.badge.rejected { background: #fee2e2; color: #991b1b; }
		.badge.other { background: #e0f2fe; color: #075985; }
		.badge.in-progress { background: #ede9fe; color: #6d28d9; }
		.meta-line { color: var(--muted); font-size: .88rem; margin-top: 4px; }
		.money { font-weight: 800; color: var(--brand-dark); }
		.empty-state {
			padding: 18px;
			border: 1px dashed var(--line);
			border-radius: 18px;
			color: var(--muted);
			background: rgba(255,255,255,.7);
		}

		@media (max-width: 1100px) {
			.hero,
			.grid-2 {
				grid-template-columns: 1fr;
			}
			.metric-grid {
				grid-template-columns: repeat(2, 1fr);
			}
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
			.metric-grid,
			.actions-grid,
			.chart-grid {
				grid-template-columns: 1fr;
			}
			.list-panel.recent-fixed {
				height: 460px;
			}
		}
		@media (max-width: 520px) {
			.content { padding: 14px; }
			.hero-panel,
			.panel,
			.metric-card { border-radius: 20px; }
			.hero-panel { padding: 22px; }
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
			<a href="./admin.php" class="active" aria-current="page">
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
			<a href="./pencil-booking.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
				<span>Pencil booking</span>
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
			<div class="hero-panel">
				<p class="hero-kicker">Admin dashboard</p>
				<h2 class="hero-title">Marketplace control for ServisyoHub</h2>
				<p class="hero-copy">Track user growth, post moderation, offers, and service activity from one command center built around this project’s workflow.</p>
			</div>
			<div class="hero-metrics">
				<div class="hero-stat">
					<div class="label">Moderation queue</div>
					<div class="value"><?php echo (int)$moderationQueue; ?></div>
					<div class="hint">Pending jobs, offers, and user reviews waiting for action.</div>
				</div>
				<div class="hero-stat">
					<div class="label">Activity this week</div>
					<div class="value"><?php echo (int)$recentJobs7d; ?></div>
					<div class="hint">New job posts created in the last 7 days.</div>
				</div>
			</div>
		</section>

		<section class="metric-grid" aria-label="Key metrics">
			<div class="metric-card accent-blue">
				<div class="label">Total users</div>
				<div class="value"><?php echo (int)$totalUsers; ?></div>
				<div class="foot">Registered accounts across the platform.</div>
			</div>
			<div class="metric-card accent-green">
				<div class="label">Open jobs</div>
				<div class="value"><?php echo (int)$openJobs; ?></div>
				<div class="foot">Job posts currently available to the community.</div>
			</div>
			<div class="metric-card accent-orange">
				<div class="label">Pending posts</div>
				<div class="value"><?php echo (int)$pendingJobs; ?></div>
				<div class="foot">Listings waiting for moderation or verification.</div>
			</div>
			<div class="metric-card accent-purple">
				<div class="label">Offers waiting</div>
				<div class="value"><?php echo (int)$pendingOffers; ?></div>
				<div class="foot">Incoming offer requests still unresolved.</div>
			</div>
		</section>

		<section class="grid-2 overview-row">
			<div class="panel">
				<h2>System overview</h2>
				<p class="subhead">A quick read on the service marketplace. The charts below follow the project’s native flow: jobs, offers, and moderation activity.</p>
				<div class="chart-grid">
					<div class="chart-card">
						<h3>Job status</h3>
						<div class="chart-wrap"><canvas id="jobStatusChart"></canvas></div>
					</div>
					<div class="chart-card">
						<h3>Offer status</h3>
						<div class="chart-wrap"><canvas id="offerStatusChart"></canvas></div>
					</div>
				</div>
			</div>

			<div class="panel quick-actions-panel">
				<h2>Quick actions</h2>
				<p class="subhead">Common admin tasks for this app, grouped as shortcuts instead of hidden in menus.</p>
				<div class="actions-grid">
					<a class="action-card" href="./manage-users.php">
						<div>
							<strong>User management</strong>
							<span>Check accounts, statuses, and profile details.</span>
						</div>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
					</a>
					<a class="action-card" href="./pencil-booking.php">
						<div>
							<strong>Pencil booking</strong>
							<span>Manage service bookings and scheduling.</span>
						</div>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
					</a>
					<a class="action-card" href="./documents.php">
						<div>
							<strong>Documents</strong>
							<span>Keep supporting files and uploads organized.</span>
						</div>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v6h6"/></svg>
					</a>
				</div>
			</div>
		</section>

		<section class="grid-2 recent-row" style="margin-top:18px;">
			<div class="panel list-panel recent-fixed">
				<div class="panel-head">
					<h2>Recent jobs</h2>
					<a class="mini-action" href="./post-approvals.php">Post approvals</a>
				</div>
				<p class="subhead">Latest marketplace posts, including category, location, and posting status.</p>
				<div class="list-scroll">
					<?php if (!empty($recentJobs)): ?>
						<table class="table">
							<thead>
								<tr>
									<th>Job</th>
									<th class="col-center">Status</th>
									<th>Budget</th>
									<th class="col-center">Posted</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($recentJobs as $job): ?>
									<?php $status = normalize_status((string)($job['status'] ?? 'other')); ?>
									<tr>
										<td>
											<div style="font-weight:800;"><a href="./gawain-detail.php?id=<?php echo (int)$job['id']; ?>"><?php echo h((string)$job['title']); ?></a></div>
											<div class="meta-line"><?php echo h((string)($job['category'] ?? '')); ?><?php echo trim((string)($job['location'] ?? '')) !== '' ? ' • ' . h((string)$job['location']) : ''; ?></div>
										</td>
										<td class="col-center"><span class="badge <?php echo h($status); ?>"><?php echo h(pretty_status((string)($job['status'] ?? 'other'))); ?></span></td>
										<td class="money"><?php echo h(money_value($job['budget'] ?? null)); ?></td>
										<td class="col-center"><?php echo h(date('M j, Y', strtotime((string)$job['posted_at']))); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else: ?>
						<div class="empty-state">No jobs have been posted yet.</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="panel list-panel recent-fixed">
				<div class="panel-head">
					<h2>Recent offers</h2>
					<a class="mini-action" href="./manage-offers.php">Manage offers</a>
				</div>
				<p class="subhead">Latest offer activity from the live marketplace.</p>
				<div class="list-scroll">
					<?php if (!empty($recentOffers)): ?>
						<table class="table">
							<thead>
								<tr>
									<th class="col-center">ID</th>
									<th>Offer</th>
									<th class="col-center">Status</th>
									<th>Amount</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($recentOffers as $offer): ?>
									<?php $offerStatus = normalize_status((string)($offer['status'] ?? 'other')); ?>
									<tr>
										<td class="col-center">#<?php echo (int)$offer['id']; ?></td>
										<td>
											<div style="font-weight:800;"><?php echo h((string)$offer['job_title']); ?></div>
											<div class="meta-line"><?php echo h(date('M j, Y', strtotime((string)$offer['created_at']))); ?></div>
										</td>
										<td class="col-center"><span class="badge <?php echo h($offerStatus); ?>"><?php echo h(pretty_status((string)($offer['status'] ?? 'other'))); ?></span></td>
										<td class="money"><?php echo h(money_value($offer['amount'] ?? null)); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else: ?>
						<div class="empty-state">No offers have been submitted yet.</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

	</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const jobLabels = <?php echo json_encode($jobStatusLabels, JSON_UNESCAPED_SLASHES); ?>;
const jobValues = <?php echo json_encode($jobStatusData, JSON_UNESCAPED_SLASHES); ?>;
const jobColors = <?php echo json_encode($jobStatusColors, JSON_UNESCAPED_SLASHES); ?>;
const offerLabels = <?php echo json_encode($offerStatusLabels, JSON_UNESCAPED_SLASHES); ?>;
const offerValues = <?php echo json_encode($offerStatusData, JSON_UNESCAPED_SLASHES); ?>;
const offerColors = <?php echo json_encode($offerStatusColors, JSON_UNESCAPED_SLASHES); ?>;

function renderDoughnut(canvasId, labels, values, colors) {
	const canvas = document.getElementById(canvasId);
	if (!canvas) {
		return;
	}
	const safeValues = values.some((value) => value > 0) ? values : [1];
	const safeLabels = values.some((value) => value > 0) ? labels : ['No data'];
	const safeColors = values.some((value) => value > 0) ? colors : ['#cbd5e1'];
	new Chart(canvas, {
		type: 'doughnut',
		data: {
			labels: safeLabels,
			datasets: [{
				data: safeValues,
				backgroundColor: safeColors,
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
						padding: 16,
						boxWidth: 10,
					},
				},
			},
			cutout: '64%',
		},
	});
}

renderDoughnut('jobStatusChart', jobLabels, jobValues, jobColors);
renderDoughnut('offerStatusChart', offerLabels, offerValues, offerColors);
</script>
</body>
</html>
