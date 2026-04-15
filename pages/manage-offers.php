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

function table_has_column(mysqli $db, string $table, string $column): bool {
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

function ensure_offers_review_columns(mysqli $db): void {
	$addedAdmin = false;
	$addedCitizen = false;

	if (!offers_has_column($db, 'admin_status')) {
		@mysqli_query($db, "ALTER TABLE offers ADD COLUMN admin_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER status");
		$addedAdmin = true;
	}
	if (!offers_has_column($db, 'citizen_status')) {
		@mysqli_query($db, "ALTER TABLE offers ADD COLUMN citizen_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER admin_status");
		$addedCitizen = true;
	}

	if ($addedCitizen) {
		@mysqli_query($db, "UPDATE offers SET citizen_status = LOWER(COALESCE(status, 'pending')) WHERE citizen_status IS NULL OR citizen_status = ''");
	}
	if ($addedAdmin) {
		@mysqli_query($db, "UPDATE offers SET admin_status = CASE
			WHEN LOWER(COALESCE(status, 'pending')) IN ('accepted','rejected','denied','cancelled','canceled','withdrawn') THEN 'accepted'
			ELSE 'pending'
		END WHERE admin_status IS NULL OR admin_status = ''");
	}
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

function db_scalar(mysqli $db, string $sql): float {
	$res = @mysqli_query($db, $sql);
	if (!$res) {
		return 0.0;
	}
	$row = @mysqli_fetch_assoc($res);
	@mysqli_free_result($res);
	return (float)($row['v'] ?? 0);
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

function h(?string $value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalize_offer_status(string $status): string {
	$status = strtolower(trim($status));
	if ($status === 'rejected') {
		return 'denied';
	}
	if ($status === 'canceled' || $status === 'cancelled' || $status === 'withdrawn') {
		return 'cancelled';
	}
	if ($status === 'accepted' || $status === 'pending' || $status === 'denied') {
		return $status;
	}
	return 'other';
}

function pretty_offer_status(string $status): string {
	$status = normalize_offer_status($status);
	if ($status === 'denied') {
		return 'Denied';
	}
	if ($status === 'cancelled') {
		return 'Cancelled';
	}
	return ucfirst($status);
}

function money_value($value): string {
	if ($value === null || $value === '') {
		return 'N/A';
	}
	return 'PHP ' . number_format((float)$value, 2);
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

if (db_has_table($db, 'offers')) {
	ensure_offers_review_columns($db);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = strtolower(trim((string)($_POST['action'] ?? '')));
	$offerId = (int)($_POST['offer_id'] ?? 0);
	if ($offerId > 0 && $action === 'trash') {
		if ($stmt = @mysqli_prepare($db, "UPDATE offers SET admin_status = 'cancelled', citizen_status = 'cancelled', status = 'cancelled' WHERE id = ?")) {
			mysqli_stmt_bind_param($stmt, 'i', $offerId);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_close($stmt);
		}
		header('Location: ./manage-offers.php');
		exit;
	}
	$statusMap = [
		'accept' => 'accepted',
		'reject' => 'rejected',
		'retrieve' => 'pending',
		'undo' => 'pending',
	];

	if ($offerId > 0 && isset($statusMap[$action])) {
		$newStatus = $statusMap[$action];
		if ($stmt = @mysqli_prepare($db, "UPDATE offers
			SET admin_status = ?,
				citizen_status = CASE
					WHEN ? = 'pending' THEN 'pending'
					ELSE citizen_status
				END,
				status = CASE
					WHEN ? = 'rejected' THEN 'rejected'
					WHEN ? = 'pending' THEN 'pending'
					ELSE status
				END
			WHERE id = ?
			  AND (
				(LOWER(COALESCE(admin_status,'pending')) = 'pending' AND ? IN ('accepted','rejected'))
				OR (LOWER(COALESCE(admin_status,'')) = 'accepted' AND ? = 'pending')
				OR (LOWER(COALESCE(admin_status,'')) IN ('rejected','denied') AND ? = 'pending')
			  )")) {
			mysqli_stmt_bind_param($stmt, 'ssssisss', $newStatus, $newStatus, $newStatus, $newStatus, $offerId, $newStatus, $newStatus, $newStatus);
			@mysqli_stmt_execute($stmt);
			@mysqli_stmt_close($stmt);
		}
	}

	header('Location: ./manage-offers.php');
	exit;
}

$offersTableExists = db_has_table($db, 'offers');
$jobsTableExists = db_has_table($db, 'jobs');
$usersTableExists = db_has_table($db, 'users');
$jobsHasDescription = $jobsTableExists && (table_has_column($db, 'jobs', 'description') || table_has_column($db, 'jobs', 'details'));
$jobDescriptionExpr = "''";
if ($jobsHasDescription) {
	$jobDescriptionExpr = table_has_column($db, 'jobs', 'description') ? 'COALESCE(j.description, \'\')' : 'COALESCE(j.details, \'\')';
}

$totalOffers = 0;
$offers7d = 0;
$pendingOffers = 0;
$acceptedOffers = 0;
$deniedOffers = 0;
$cancelledOffers = 0;
$activeKasangga = 0;
$citizensWithOffers = 0;
$avgOfferAmount = 0.0;

$topCitizenRows = [];
$recentOffers = [];
$avgOfferTrendRows = [];

$trendStartDate = date('Y-04-01');
if (strtotime(date('Y-m-d')) < strtotime($trendStartDate)) {
	$trendStartDate = date('Y-04-01', strtotime('-1 year'));
}
$trendStartSql = mysqli_real_escape_string($db, $trendStartDate . ' 00:00:00');

if ($offersTableExists) {
	$totalOffers = db_count($db, 'SELECT COUNT(*) AS c FROM offers');
	$offers7d = db_count($db, "SELECT COUNT(*) AS c FROM offers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
	$pendingOffers = db_count($db, "SELECT COUNT(*) AS c FROM offers WHERE LOWER(COALESCE(admin_status,'pending')) = 'pending'");
	$acceptedOffers = db_count($db, "SELECT COUNT(*) AS c FROM offers WHERE LOWER(COALESCE(admin_status,'')) = 'accepted'");
	$deniedOffers = db_count($db, "SELECT COUNT(*) AS c FROM offers WHERE LOWER(COALESCE(admin_status,'')) IN ('rejected','denied')");
	$cancelledOffers = db_count($db, "SELECT COUNT(*) AS c FROM offers WHERE LOWER(COALESCE(admin_status,'')) IN ('withdrawn','canceled','cancelled')");
	$activeKasangga = db_count($db, 'SELECT COUNT(DISTINCT user_id) AS c FROM offers WHERE user_id IS NOT NULL');
	$avgOfferAmount = db_scalar($db, "SELECT COALESCE(AVG(amount),0) AS v
		FROM offers
		WHERE amount IS NOT NULL
		  AND amount > 0
		  AND created_at >= '{$trendStartSql}'
		  AND LOWER(COALESCE(admin_status,'pending')) = 'accepted'");

	if ($jobsTableExists) {
		$citizensWithOffers = db_count($db, 'SELECT COUNT(DISTINCT j.user_id) AS c FROM offers o INNER JOIN jobs j ON j.id = o.job_id WHERE j.user_id IS NOT NULL');
	}

	$avgOfferTrendRows = db_rows($db, "SELECT
		DATE(created_at) AS day,
		COALESCE(AVG(amount), 0) AS avg_amount
	FROM offers
	WHERE created_at >= '{$trendStartSql}'
	  AND amount IS NOT NULL
	  AND amount > 0
	  AND LOWER(COALESCE(admin_status,'pending')) = 'accepted'
	GROUP BY DATE(created_at)
	ORDER BY day ASC");

	if ($jobsTableExists) {
		$topCitizenRows = db_rows($db, "SELECT
			j.user_id AS citizen_id,
			COALESCE(
				NULLIF(TRIM(CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,''))), ''),
				NULLIF(c.username, ''),
				CONCAT('Citizen #', j.user_id)
			) AS citizen_name,
			COUNT(*) AS offers_count,
			COALESCE(AVG(o.amount), 0) AS avg_offer_amount
		FROM offers o
		INNER JOIN jobs j ON j.id = o.job_id
		LEFT JOIN users c ON c.id = j.user_id
		WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
		GROUP BY j.user_id, citizen_name
		ORDER BY offers_count DESC, j.user_id DESC
		LIMIT 3");

		$recentOffers = db_rows($db, "SELECT
			o.id,
			o.job_id,
			COALESCE(j.title, CONCAT('Job #', o.job_id)) AS job_title,
			{$jobDescriptionExpr} AS job_description,
			COALESCE(j.location, 'Online') AS job_location,
			COALESCE(j.date_needed, 'Anytime') AS date_needed,
			COALESCE(o.amount, 0) AS amount,
			LOWER(COALESCE(o.admin_status, 'pending')) AS admin_status,
			LOWER(COALESCE(o.citizen_status, COALESCE(o.status, 'pending'))) AS citizen_status,
			o.created_at,
			COALESCE(
				NULLIF(TRIM(CONCAT(COALESCE(k.first_name,''), ' ', COALESCE(k.last_name,''))), ''),
				NULLIF(k.username, ''),
				CONCAT('Kasangga #', o.user_id)
			) AS kasangga_name,
			COALESCE(
				NULLIF(TRIM(CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,''))), ''),
				NULLIF(c.username, ''),
				CONCAT('Citizen #', j.user_id)
			) AS citizen_name
		FROM offers o
		LEFT JOIN jobs j ON j.id = o.job_id
		LEFT JOIN users k ON k.id = o.user_id
		LEFT JOIN users c ON c.id = j.user_id
		ORDER BY o.id DESC
		LIMIT 25");
	} else {
		$recentOffers = db_rows($db, "SELECT
			o.id,
			o.job_id,
			CONCAT('Job #', o.job_id) AS job_title,
			'' AS job_description,
			'Online' AS job_location,
			'Anytime' AS date_needed,
			COALESCE(o.amount, 0) AS amount,
			LOWER(COALESCE(o.admin_status, 'pending')) AS admin_status,
			LOWER(COALESCE(o.citizen_status, COALESCE(o.status, 'pending'))) AS citizen_status,
			o.created_at,
			COALESCE(
				NULLIF(TRIM(CONCAT(COALESCE(k.first_name,''), ' ', COALESCE(k.last_name,''))), ''),
				NULLIF(k.username, ''),
				CONCAT('Kasangga #', o.user_id)
			) AS kasangga_name,
			'Citizen unavailable' AS citizen_name
		FROM offers o
		LEFT JOIN users k ON k.id = o.user_id
		ORDER BY o.id DESC
		LIMIT 25");
	}
}

$avgOfferTrendMap = [];
foreach ($avgOfferTrendRows as $row) {
	$day = (string)($row['day'] ?? '');
	if ($day !== '') {
		$avgOfferTrendMap[$day] = (float)($row['avg_amount'] ?? 0);
	}
}

$avgOfferLabels = [];
$avgOfferData = [];

$avgOfferPeakAmount = 0.0;
$cursor = strtotime($trendStartDate);
$today = strtotime(date('Y-m-d'));
while ($cursor <= $today) {
	$day = date('Y-m-d', $cursor);
	$avgValue = round((float)($avgOfferTrendMap[$day] ?? 0), 2);
	$avgOfferLabels[] = date('M j', $cursor);
	$avgOfferData[] = $avgValue;
	if ($avgValue > $avgOfferPeakAmount) {
		$avgOfferPeakAmount = $avgValue;
	}
	$cursor = strtotime('+1 day', $cursor);
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Manage Offers • ServisyoHub</title>
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
		}
		.brand h1 { margin: 0; font-size: 1.1rem; line-height: 1.1; }
		.brand p { margin: 4px 0 0; color: rgba(255,255,255,.68); font-size: .9rem; }
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
		.sidebar-footer { margin-top: auto; padding-top: 16px; }
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
		.logout-btn svg { width: 16px; height: 16px; }
		.logout-btn:hover {
			background: rgba(239, 68, 68, .28);
			border-color: rgba(255,255,255,.35);
		}

		.content { padding: 24px; }
		.hero {
			background: linear-gradient(135deg, rgba(0,120,166,.97), rgba(14,116,162,.84));
			color: #fff;
			border-radius: 24px;
			padding: 22px;
			box-shadow: var(--shadow);
			margin-bottom: 18px;
		}
		.hero h2 { margin: 0; font-size: clamp(1.2rem, 2vw, 1.8rem); font-weight: 900; }
		.hero p { margin: 8px 0 0; opacity: .92; }

		.metrics {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 12px;
			margin-bottom: 18px;
		}
		.metric-card {
			background: var(--surface);
			border: 1px solid rgba(255,255,255,.8);
			border-radius: 18px;
			padding: 14px;
			box-shadow: var(--shadow);
		}
		.metric-card .label {
			font-size: .74rem;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: .1em;
			color: var(--muted);
		}
		.metric-card .value {
			margin-top: 8px;
			font-size: 1.7rem;
			font-weight: 900;
			line-height: 1;
		}
		.metric-inline-trend {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			margin-top: 8px;
		}
		.metric-inline-trend .value {
			margin-top: 0;
		}
		.sparkline-wrap {
			width: 84px;
			height: 36px;
			flex: 0 0 84px;
		}
		.sparkline-wrap canvas {
			width: 100% !important;
			height: 100% !important;
		}
		.metric-card .hint { margin-top: 6px; font-size: .86rem; color: var(--muted); }

		.overview-grid {
			display: grid;
			grid-template-columns: 1.1fr .9fr;
			gap: 14px;
			align-items: start;
			margin-bottom: 18px;
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
		.panel-head h3 { margin: 0; font-size: 1.08rem; }
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
		.subhead { margin: 0 0 12px; color: var(--muted); font-size: .95rem; line-height: 1.5; }
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
		.chart-wrap { position: relative; height: 260px; }
		.avg-offer-chart-wrap { height: 360px; }
		.chart-scroll-wrap {
			overflow-x: auto;
			overflow-y: hidden;
			padding-bottom: 6px;
		}
		.chart-scroll-inner {
			position: relative;
			height: 360px;
			min-width: 100%;
		}
		.chart-scroll-inner canvas {
			width: 100% !important;
			height: 100% !important;
		}
		.stat-list { display: grid; gap: 10px; }
		.stat-item {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 12px;
			border-radius: 12px;
			border: 1px solid var(--line);
			background: #fff;
		}
		.stat-item .name { font-weight: 700; color: #334155; }
		.stat-item .num { font-weight: 900; }

		.table-wrap {
			max-height: 62vh;
			overflow: auto;
			border-radius: 16px;
			border: 1px solid var(--line);
			background: #fff;
		}
		.offers-table-wrap {
			max-height: 470px;
		}
		.table { width: 100%; border-collapse: collapse; }
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
		.table .col-center { text-align: center; }
		.table tr:hover { background: rgba(14, 165, 233, .04); }
		.badge {
			display: inline-flex;
			align-items: center;
			padding: 5px 10px;
			border-radius: 999px;
			font-size: .76rem;
			font-weight: 800;
		}
		.badge.accepted { background: #dcfce7; color: #166534; }
		.badge.pending { background: #fef3c7; color: #92400e; }
		.badge.denied { background: #fee2e2; color: #991b1b; }
		.badge.cancelled { background: #e2e8f0; color: #475569; }
		.badge.other { background: #e0f2fe; color: #075985; }
		.money { font-weight: 800; color: var(--brand-dark); }
		.meta { color: var(--muted); font-size: .88rem; margin-top: 4px; }
		.action-stack {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			flex-wrap: wrap;
		}
		.action-stack form { margin: 0; }
		.action-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: auto;
			min-width: 88px;
			height: 34px;
			padding: 0 10px;
			border-radius: 10px;
			font-size: .82rem;
			font-weight: 800;
			border: 0;
			cursor: pointer;
		}
		.action-btn.accept { background: #7cd4c4; color: #0b2c24; }
		.action-btn.reject { background: #ef4444; color: #fff; }
		.action-btn.retrieve { background: #64748b; color: #fff; }
		.action-btn.undo { background: #334155; color: #fff; }
		.action-btn.trash {
			min-width: 34px;
			width: 34px;
			padding: 0;
			background: #ef4444;
			color: #fff;
		}
		.action-btn.trash svg {
			width: 16px;
			height: 16px;
			stroke: currentColor;
			fill: none;
		}
		.action-btn.accept:hover { filter: brightness(.96); }
		.action-btn.reject:hover { filter: brightness(.94); }
		.action-btn.retrieve:hover { background: #475569; }
		.action-btn.undo:hover { background: #1f2937; }
		.action-btn.trash:hover { filter: brightness(.92); }
		.action-na { color: var(--muted); font-weight: 700; }
		.job-link-btn {
			appearance: none;
			border: 0;
			padding: 0;
			margin: 0;
			background: transparent;
			color: #0b4f74;
			font-weight: 800;
			cursor: pointer;
			text-align: left;
		}
		.job-link-btn:hover {
			text-decoration: underline;
		}
		.offer-modal {
			position: fixed;
			inset: 0;
			display: none;
			align-items: center;
			justify-content: center;
			z-index: 1200;
			padding: 18px;
		}
		.offer-modal.open { display: flex; }
		.offer-modal-backdrop {
			position: absolute;
			inset: 0;
			background: rgba(2, 6, 23, .5);
		}
		.offer-modal-card {
			position: relative;
			width: min(760px, calc(100vw - 28px));
			max-height: calc(100vh - 32px);
			overflow: auto;
			background: #fff;
			border-radius: 18px;
			border: 1px solid rgba(15, 23, 42, .08);
			box-shadow: 0 30px 80px rgba(2, 6, 23, .25);
			padding: 18px;
		}
		.offer-modal-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 12px;
		}
		.offer-modal-head h3 {
			margin: 0;
			font-size: 1.1rem;
		}
		.offer-modal-close {
			appearance: none;
			border: 0;
			border-radius: 10px;
			padding: 8px 10px;
			font-weight: 800;
			cursor: pointer;
			background: #e2e8f0;
			color: #0f172a;
		}
		.offer-modal-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 14px;
		}
		.offer-modal-section {
			border: 1px solid var(--line);
			border-radius: 12px;
			padding: 12px;
			background: #f8fbff;
		}
		.offer-modal-section h4 {
			margin: 0 0 8px;
			font-size: .82rem;
			text-transform: uppercase;
			letter-spacing: .08em;
			color: #64748b;
		}
		.offer-modal-description {
			white-space: pre-wrap;
			color: #334155;
			line-height: 1.5;
		}
		.offer-meta-row {
			display: flex;
			justify-content: space-between;
			gap: 10px;
			padding: 6px 0;
			border-bottom: 1px dashed rgba(15, 23, 42, .1);
			font-size: .92rem;
		}
		.offer-meta-row:last-child { border-bottom: 0; }
		@media (max-width: 760px) {
			.offer-modal-grid { grid-template-columns: 1fr; }
		}
		.empty-state {
			padding: 18px;
			border: 1px dashed var(--line);
			border-radius: 18px;
			color: var(--muted);
			background: rgba(255,255,255,.7);
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
			.metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
			.overview-grid { grid-template-columns: 1fr; }
		}
		@media (max-width: 680px) {
			.content { padding: 14px; }
			.panel, .hero { border-radius: 18px; }
			.table th, .table td { font-size: .88rem; padding: 10px 8px; }
			.metrics { grid-template-columns: 1fr; }
		}
	</style>
</head>
<body>
<div class="admin-shell">
	<aside class="sidebar">
		<div class="brand">
			<img class="brand-logo" src="../assets/images/newlogo2.png" alt="ServisyoHub">
			<div>
				<h1>Admin Console</h1>
				<p>ServisyoHub marketplace control</p>
			</div>
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
			<a href="./manage-offers.php" class="active" aria-current="page">
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
			<h2>Kasangga Offer Management</h2>
			<p>Track offers sent by Kasangga to citizens, monitor conversion, and review payout-related offer activity in one place.</p>
		</section>

		<section class="metrics" aria-label="Offer metrics">
			<div class="metric-card">
				<div class="label">Total Offers</div>
				<div class="value"><?php echo (int)$totalOffers; ?></div>
				<div class="hint">All submitted offers.</div>
			</div>
			<div class="metric-card">
				<div class="label">Pending</div>
				<div class="value"><?php echo (int)$pendingOffers; ?></div>
				<div class="hint">Waiting for citizen response.</div>
			</div>
			<div class="metric-card">
				<div class="label">Accepted</div>
				<div class="value"><?php echo (int)$acceptedOffers; ?></div>
				<div class="hint">Offers successfully matched.</div>
			</div>
			<div class="metric-card">
				<div class="label">Avg Offer Amount</div>
				<div class="metric-inline-trend">
					<div class="value"><?php echo h(number_format($avgOfferAmount, 2)); ?></div>
					<div class="sparkline-wrap">
						<canvas id="avgOfferSparkline" aria-label="Average offer amount trend"></canvas>
					</div>
				</div>
				<div class="hint"><?php echo h(money_value($avgOfferAmount)); ?></div>
			</div>
		</section>

		<section class="overview-grid">
			<div class="panel">
				<div class="panel-head">
					<h3>Average Offer Amount Trend</h3>
					<span class="chip">Live snapshot</span>
				</div>
				<p class="subhead">Daily trend of average Kasangga-to-citizen offer amounts since Apr 1.</p>
				<div class="chart-card">
					<h4>Average Offer Amount</h4>
					<div class="chart-scroll-wrap" id="avgOfferChartScroll">
						<div class="chart-scroll-inner" id="avgOfferChartInner"><canvas id="avgOfferChart"></canvas></div>
					</div>
				</div>
			</div>

			<div class="panel">
				<div class="panel-head">
					<h3>Offer Participation</h3>
					<span class="chip">Top senders</span>
				</div>
				<p class="subhead">Most active Kasangga and receiving citizens.</p>
				<div class="stat-list">
					<div class="stat-item"><span class="name">Kasangga with Offers</span><span class="num"><?php echo (int)$activeKasangga; ?></span></div>
					<div class="stat-item"><span class="name">Citizens with Offers</span><span class="num"><?php echo (int)$citizensWithOffers; ?></span></div>
					<div class="stat-item"><span class="name">Offers This Week</span><span class="num"><?php echo (int)$offers7d; ?></span></div>
					<div class="chart-card" style="margin-top:2px;">
						<h4>Top Citizens Receiving Offers (Last 14 Days)</h4>
						<div class="table-wrap" style="max-height:none; border-radius:12px;">
							<?php if (!empty($topCitizenRows)): ?>
								<table class="table">
									<thead>
										<tr>
											<th>Citizen</th>
											<th class="col-center">Offer Count</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($topCitizenRows as $row): ?>
										<tr>
											<td><?php echo h((string)$row['citizen_name']); ?></td>
											<td class="col-center"><?php echo (int)($row['offers_count'] ?? 0); ?></td>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							<?php else: ?>
								<div class="empty-state">No top citizen data for the last 14 days.</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="panel" aria-label="Recent offers table">
			<div class="panel-head">
				<h3>Kasangga to Citizen Offers</h3>
				<span class="chip"><?php echo count($recentOffers); ?> shown</span>
			</div>
			<p class="subhead">Offers are listed in hierarchy order by ID (latest first).</p>

			<div class="table-wrap offers-table-wrap">
				<?php if (!empty($recentOffers)): ?>
					<table class="table">
						<thead>
							<tr>
								<th class="col-center">ID</th>
								<th>Kasangga</th>
								<th>Citizen</th>
								<th>Job</th>
								<th class="col-center">Admin Status</th>
								<th class="col-center">Citizen Status</th>
								<th>Amount</th>
								<th class="col-center">Date</th>
								<th class="col-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($recentOffers as $offer): ?>
								<?php $adminStatus = normalize_offer_status((string)($offer['admin_status'] ?? 'other')); ?>
								<?php $citizenStatus = normalize_offer_status((string)($offer['citizen_status'] ?? 'other')); ?>
								<?php $isPending = strtolower(trim((string)($offer['admin_status'] ?? ''))) === 'pending'; ?>
								<?php $isAccepted = $adminStatus === 'accepted'; ?>
								<?php $isDenied = in_array($adminStatus, ['denied'], true); ?>
								<tr>
									<td class="col-center">#<?php echo (int)$offer['id']; ?></td>
									<td><?php echo h((string)$offer['kasangga_name']); ?></td>
									<td><?php echo h((string)$offer['citizen_name']); ?></td>
									<td>
										<button
											type="button"
											class="job-link-btn"
											data-job-title="<?php echo h((string)$offer['job_title']); ?>"
											data-job-id="<?php echo (int)$offer['job_id']; ?>"
											data-job-description="<?php echo h((string)($offer['job_description'] ?? '')); ?>"
											data-job-location="<?php echo h((string)($offer['job_location'] ?? 'Online')); ?>"
											data-job-date-needed="<?php echo h((string)($offer['date_needed'] ?? 'Anytime')); ?>"
											data-offer-id="<?php echo (int)$offer['id']; ?>"
											data-kasangga-name="<?php echo h((string)$offer['kasangga_name']); ?>"
											data-citizen-name="<?php echo h((string)$offer['citizen_name']); ?>"
											data-offer-amount="<?php echo h(money_value($offer['amount'] ?? null)); ?>"
											data-admin-status="<?php echo h(pretty_offer_status((string)$adminStatus)); ?>"
											data-citizen-status="<?php echo h(pretty_offer_status((string)$citizenStatus)); ?>"
											data-offer-date="<?php echo h(date('M j, Y', strtotime((string)$offer['created_at']))); ?>"
										>
											<?php echo h((string)$offer['job_title']); ?>
										</button>
										<div class="meta">Job #<?php echo (int)$offer['job_id']; ?></div>
									</td>
									<td class="col-center"><span class="badge <?php echo h($adminStatus); ?>"><?php echo h(pretty_offer_status((string)$adminStatus)); ?></span></td>
									<td class="col-center"><span class="badge <?php echo h($citizenStatus); ?>"><?php echo h(pretty_offer_status((string)$citizenStatus)); ?></span></td>
									<td class="money"><?php echo h(money_value($offer['amount'] ?? null)); ?></td>
									<td class="col-center"><?php echo h(date('M j, Y', strtotime((string)$offer['created_at']))); ?></td>
									<td class="col-center">
										<?php if ($isPending): ?>
											<div class="action-stack">
												<form method="post">
													<input type="hidden" name="action" value="accept">
													<input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
													<button type="submit" class="action-btn accept">Accept</button>
												</form>
												<form method="post" onsubmit="return confirm('Reject this offer?');">
													<input type="hidden" name="action" value="reject">
													<input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
													<button type="submit" class="action-btn reject">Reject</button>
												</form>
											</div>
											<?php elseif ($isAccepted): ?>
												<div class="action-stack">
													<form method="post" onsubmit="return confirm('Undo admin acceptance for this offer?');">
														<input type="hidden" name="action" value="undo">
														<input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
														<button type="submit" class="action-btn undo">Undo</button>
													</form>
													<form method="post" onsubmit="return confirm('Move this offer to trash?');">
														<input type="hidden" name="action" value="trash">
														<input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
														<button type="submit" class="action-btn trash" aria-label="Trash offer" title="Trash offer">
															<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M6 6l1 15a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-15"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
														</button>
													</form>
												</div>
										<?php elseif ($isDenied): ?>
											<div class="action-stack">
												<form method="post" onsubmit="return confirm('Retrieve this denied offer for admin review again?');">
													<input type="hidden" name="action" value="retrieve">
													<input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
													<button type="submit" class="action-btn retrieve">Retrieve</button>
												</form>
												<form method="post" onsubmit="return confirm('Move this offer to trash?');">
													<input type="hidden" name="action" value="trash">
													<input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
													<button type="submit" class="action-btn trash" aria-label="Trash offer" title="Trash offer">
														<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M6 6l1 15a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-15"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
													</button>
												</form>
											</div>
										<?php else: ?>
											<span class="action-na">-</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<div class="empty-state">No offers available yet.</div>
				<?php endif; ?>
			</div>
		</section>

	</main>
</div>

<div class="offer-modal" id="offerDetailModal" aria-hidden="true">
	<div class="offer-modal-backdrop" data-close-offer-modal="true"></div>
	<div class="offer-modal-card" role="dialog" aria-modal="true" aria-labelledby="offerModalTitle">
		<div class="offer-modal-head">
			<h3 id="offerModalTitle">Post & Offer Summary</h3>
			<button type="button" class="offer-modal-close" id="offerModalCloseBtn">Close</button>
		</div>
		<div class="offer-modal-grid">
			<section class="offer-modal-section">
				<h4>Post Details</h4>
				<div class="offer-meta-row"><span>Job</span><strong id="modalJobTitle">-</strong></div>
				<div class="offer-meta-row"><span>Job ID</span><strong id="modalJobId">-</strong></div>
				<div class="offer-meta-row"><span>Location</span><strong id="modalJobLocation">-</strong></div>
				<div class="offer-meta-row"><span>Date Needed</span><strong id="modalJobDateNeeded">-</strong></div>
				<div class="offer-meta-row" style="display:block; border-bottom:0; padding-top:10px;">
					<div style="font-size:.8rem; color:#64748b; margin-bottom:6px; text-transform:uppercase; letter-spacing:.08em;">Post Description</div>
					<div class="offer-modal-description" id="modalJobDescription">No description available.</div>
				</div>
			</section>
			<section class="offer-modal-section">
				<h4>Offer Summary</h4>
				<div class="offer-meta-row"><span>Offer ID</span><strong id="modalOfferId">-</strong></div>
				<div class="offer-meta-row"><span>Kasangga</span><strong id="modalKasangga">-</strong></div>
				<div class="offer-meta-row"><span>Citizen</span><strong id="modalCitizen">-</strong></div>
				<div class="offer-meta-row"><span>Amount</span><strong id="modalOfferAmount">-</strong></div>
				<div class="offer-meta-row"><span>Admin Status</span><strong id="modalAdminStatus">-</strong></div>
				<div class="offer-meta-row"><span>Citizen Status</span><strong id="modalCitizenStatus">-</strong></div>
				<div class="offer-meta-row"><span>Submitted</span><strong id="modalOfferDate">-</strong></div>
			</section>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const avgOfferLabels = <?php echo json_encode($avgOfferLabels, JSON_UNESCAPED_SLASHES); ?>;
const avgOfferData = <?php echo json_encode($avgOfferData, JSON_UNESCAPED_SLASHES); ?>;
const avgOfferSparklineData = avgOfferData.slice(-4);
const trendPointCount = avgOfferLabels.length;

const avgOfferChartInner = document.getElementById('avgOfferChartInner');
const avgOfferChartScroll = document.getElementById('avgOfferChartScroll');
if (avgOfferChartInner) {
	const minVisibleWidth = 700;
	const pxPerDay = 28;
	const computedWidth = Math.max(minVisibleWidth, trendPointCount * pxPerDay);
	avgOfferChartInner.style.width = `${computedWidth}px`;
}

function renderAvgOfferLine(canvasId, labels, values) {
	const canvas = document.getElementById(canvasId);
	if (!canvas) return;
	const hasData = values.some((value) => Number(value) > 0);
	new Chart(canvas, {
		type: 'line',
		data: {
			labels,
			datasets: [{
				label: 'Avg amount',
				data: hasData ? values : labels.map(() => 0),
				borderColor: '#0ea5e9',
				backgroundColor: 'rgba(14, 165, 233, 0.16)',
				pointBackgroundColor: '#0284c7',
				pointRadius: 3,
				pointHoverRadius: 5,
				tension: 0.35,
				fill: true,
			}],
		},
		options: {
			maintainAspectRatio: false,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => `Avg: PHP ${Number(context.parsed.y || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`,
					},
				},
			},
			scales: {
				x: {
					grid: { color: 'rgba(148,163,184,.16)' },
				},
				y: {
					beginAtZero: true,
					grid: { color: 'rgba(148,163,184,.25)' },
					ticks: {
						callback: (value) => `PHP ${Number(value).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`,
					},
				},
			},
		},
	});
}

function renderAvgOfferSparkline(canvasId, values) {
	const canvas = document.getElementById(canvasId);
	if (!canvas) return;

	const numericValues = values.map((value) => Number(value) || 0);
	let trendColor = '#16a34a';

	if (numericValues.length > 1) {
		const latest = numericValues[numericValues.length - 1];
		let previousComparable = numericValues[0];
		for (let i = numericValues.length - 2; i >= 0; i--) {
			if (numericValues[i] !== latest) {
				previousComparable = numericValues[i];
				break;
			}
		}
		trendColor = latest >= previousComparable ? '#16a34a' : '#dc2626';
	}

	new Chart(canvas, {
		type: 'line',
		data: {
			labels: numericValues.map((_, index) => index),
			datasets: [{
				data: numericValues,
				borderColor: trendColor,
				backgroundColor: 'transparent',
				pointRadius: 0,
				pointHoverRadius: 0,
				borderWidth: 2,
				tension: 0.35,
				fill: false,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: { display: false },
				tooltip: { enabled: false },
			},
			scales: {
				x: { display: false },
				y: { display: false },
			},
			elements: {
				line: { capBezierPoints: true },
			},
		},
	});
}

renderAvgOfferLine('avgOfferChart', avgOfferLabels, avgOfferData);
renderAvgOfferSparkline('avgOfferSparkline', avgOfferSparklineData);

if (avgOfferChartScroll) {
	avgOfferChartScroll.scrollLeft = avgOfferChartScroll.scrollWidth;
}

const offerDetailModal = document.getElementById('offerDetailModal');
const offerModalCloseBtn = document.getElementById('offerModalCloseBtn');

function setModalText(id, value) {
	const el = document.getElementById(id);
	if (!el) return;
	el.textContent = value && String(value).trim() !== '' ? String(value) : '-';
}

function openOfferDetailModal(trigger) {
	if (!offerDetailModal || !trigger) return;
	const desc = trigger.dataset.jobDescription || '';

	setModalText('modalJobTitle', trigger.dataset.jobTitle);
	setModalText('modalJobId', trigger.dataset.jobId ? `#${trigger.dataset.jobId}` : '-');
	setModalText('modalJobLocation', trigger.dataset.jobLocation || 'Online');
	setModalText('modalJobDateNeeded', trigger.dataset.jobDateNeeded || 'Anytime');
	setModalText('modalJobDescription', desc || 'No description available.');
	setModalText('modalOfferId', trigger.dataset.offerId ? `#${trigger.dataset.offerId}` : '-');
	setModalText('modalKasangga', trigger.dataset.kasanggaName);
	setModalText('modalCitizen', trigger.dataset.citizenName);
	setModalText('modalOfferAmount', trigger.dataset.offerAmount);
	setModalText('modalAdminStatus', trigger.dataset.adminStatus);
	setModalText('modalCitizenStatus', trigger.dataset.citizenStatus);
	setModalText('modalOfferDate', trigger.dataset.offerDate);

	offerDetailModal.classList.add('open');
	offerDetailModal.setAttribute('aria-hidden', 'false');
}

function closeOfferDetailModal() {
	if (!offerDetailModal) return;
	offerDetailModal.classList.remove('open');
	offerDetailModal.setAttribute('aria-hidden', 'true');
}

document.querySelectorAll('.job-link-btn').forEach((btn) => {
	btn.addEventListener('click', () => openOfferDetailModal(btn));
});

if (offerModalCloseBtn) {
	offerModalCloseBtn.addEventListener('click', closeOfferDetailModal);
}

if (offerDetailModal) {
	offerDetailModal.addEventListener('click', (event) => {
		if (event.target && event.target.dataset && event.target.dataset.closeOfferModal === 'true') {
			closeOfferDetailModal();
		}
	});
}

document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') {
		closeOfferDetailModal();
	}
});
</script>
</body>
</html>
