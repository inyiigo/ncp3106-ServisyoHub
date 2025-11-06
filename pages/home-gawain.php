<?php
// Start output buffering (prevents "headers already sent" warnings)
ob_start();

// Start session safely before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure self user id exists for Profile links
$self_uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Capture mobile from POST if present and keep in session for future requests
if (!empty($_POST['mobile'])) {
    $_SESSION['mobile'] = trim($_POST['mobile']);
}

// Determine display name
$display = $_SESSION['display_name'] ?? $_SESSION['mobile'] ?? 'there';

// Create avatar initial
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));

// Safe DB connect to fetch recent posts
$configPath = __DIR__ . '/../includes/config.php';
$mysqli = null;
$dbAvailable = false;
$lastConnError = '';
if (file_exists($configPath)) { require_once $configPath; }
$attempts = [];
if (isset($db_host, $db_user, $db_pass, $db_name)) $attempts[] = [$db_host, $db_user, $db_pass, $db_name];
$attempts[] = ['localhost', 'root', '', 'login']; // YOUR JOBS DATABASE - prioritize this!
$attempts[] = ['localhost', 'root', '', 'servisyohub']; // Fallback
foreach ($attempts as $creds) {
	list($h,$u,$p,$n) = $creds;
	if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_OFF);
	try {
		$conn = @mysqli_connect($h,$u,$p,$n);
		if ($conn && !mysqli_connect_errno()) { 
			$mysqli = $conn; 
			$dbAvailable = true; 
			error_log("✓ Connected to database: $n"); // Debug log
			break; 
		}
		else { $lastConnError = mysqli_connect_error() ?: 'Connection failed'; if ($conn) { @mysqli_close($conn); } }
	} catch (Throwable $ex) {
		$lastConnError = $ex->getMessage();
	} finally {
		if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);
	}
}
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
/* Replace time_ago() with a robust implementation */
function time_ago($dt) {
	// empty input -> empty output (avoids misleading "just now")
	if (empty($dt) && $dt !== '0') return '';

	// Parse posted time robustly (handle numeric ts in seconds or milliseconds, and DATETIME strings)
	try {
		if (is_numeric($dt)) {
			$ts = (int)$dt;
			// if ms timestamp, convert to seconds
			if ($ts > 9999999999) $ts = (int) floor($ts / 1000);
			$posted = (new DateTimeImmutable())->setTimestamp($ts);
		} else {
			$posted = new DateTimeImmutable($dt);
		}
	} catch (Throwable $ex) {
		// parsing failed — return empty to avoid "just now" being shown incorrectly
		return '';
	}

	$nowTs = (new DateTimeImmutable())->getTimestamp();
	$postedTs = $posted->getTimestamp();
	$delta = $nowTs - $postedTs;
	$abs = abs($delta);

	// Very recent
	if ($abs < 5) return 'just now';
	if ($abs < 60) return $abs . ' sec' . ($abs > 1 ? 's' : '') . ' ago';

	$mins = (int) floor($abs / 60);
	if ($mins < 60) return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';

	$hours = (int) floor($abs / 3600);
	if ($hours < 24) return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';

	$days = (int) floor($abs / 86400);
	if ($days < 7) return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';

	if ($days < 30) {
		$weeks = (int) floor($days / 7);
		return $weeks . ' wk' . ($weeks > 1 ? 's' : '') . ' ago';
	}

	$months = (int) floor($days / 30);
	if ($months < 12) return $months . ' mo' . ($months > 1 ? 's' : '') . ' ago';

	$years = (int) floor($days / 365);
	return $years . ' yr' . ($years > 1 ? 's' : '') . ' ago';
}
$jobs = [];
if ($dbAvailable) {
	// read filter/sort inputs from GET (safe defaults)
	$sort = $_GET['sort'] ?? 'recent';
	$catFilter = trim((string)($_GET['cat'] ?? ''));
	$min = $_GET['min'] ?? '';
	$max = $_GET['max'] ?? '';

	// base where
	$where = "WHERE COALESCE(status,'open') IN ('open','pending')";

	// category filter
	if ($catFilter !== '') {
		$catEsc = mysqli_real_escape_string($mysqli, $catFilter);
		$where .= " AND category = '{$catEsc}'";
	}

	// min/max budget filter (only when numeric)
	if (is_numeric($min)) {
		$minVal = (float)$min;
		$where .= " AND CAST(NULLIF(budget,'') AS DECIMAL(12,2)) >= {$minVal}";
	}
	if (is_numeric($max)) {
		$maxVal = (float)$max;
		$where .= " AND CAST(NULLIF(budget,'') AS DECIMAL(12,2)) <= {$maxVal}";
	}

	// determine ordering
	switch ($sort) {
		case 'price_asc':
			// put items with empty budget last, then sort by numeric budget
			$order = "ORDER BY (budget IS NULL OR budget = '') ASC, CAST(NULLIF(budget,'') AS DECIMAL(12,2)) ASC, posted_at DESC, id DESC";
			break;
		case 'price_desc':
			$order = "ORDER BY (budget IS NULL OR budget = '') ASC, CAST(NULLIF(budget,'') AS DECIMAL(12,2)) DESC, posted_at DESC, id DESC";
			break;
		default:
			$order = "ORDER BY posted_at DESC, id DESC";
			break;
	}

	// build and execute query
	$sql = "SELECT id, title, category, COALESCE(location,'') AS location, COALESCE(budget,'') AS budget, COALESCE(date_needed,'') AS date_needed, COALESCE(status,'open') AS status, posted_at, UNIX_TIMESTAMP(posted_at) AS posted_ts
	        FROM jobs
	        {$where}
	        {$order}
	        LIMIT 50"; // increase a bit for filter results

	if ($res = @mysqli_query($mysqli, $sql)) {
		while ($row = mysqli_fetch_assoc($res)) $jobs[] = $row;
		@mysqli_free_result($res);
	}
}

/* Fetch recent job posts from database */
$recentPosts = [];
if ($dbAvailable) {
	// include epoch seconds posted_ts for accurate time calculations
	$sql = "SELECT j.*, 'User' AS display_name, '' AS email, UNIX_TIMESTAMP(j.posted_at) AS posted_ts
	        FROM jobs j 
	        WHERE j.status = 'open' 
	        ORDER BY j.posted_at DESC 
	        LIMIT 10";
	
	error_log("✓ Fetching recent posts..."); // Debug log
	$result = @mysqli_query($mysqli, $sql);
	
	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) {
			$recentPosts[] = $row;
		}
		mysqli_free_result($result);
		error_log("✓ Found " . count($recentPosts) . " recent posts"); // Debug log
	} else {
		error_log("✗ Query failed: " . mysqli_error($mysqli)); // Debug log
	}
}

// End buffering (send output)
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Home • Gawain • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
	<style>
		/* Side nav: compact by default, expand on hover */
		.dash-aside { width: 64px; transition: width 200ms ease, box-shadow 180ms ease; overflow: hidden; }
		.dash-aside:hover { width: 240px; box-shadow: 0 12px 28px rgba(2,6,23,.12); }
		.dash-aside .dash-nav a { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 10px; }
		.dash-aside .dash-nav .dash-icon { width: 20px; height: 20px; flex: 0 0 20px; }

		/* Bottom nav: centered at the bottom */
		.dash-bottom-nav { position: fixed; left: 50%; right: auto; bottom: 16px; transform: translateX(-50%) scale(0.92); transform-origin: bottom center; margin: 0; width: max-content; transition: transform 180ms ease, box-shadow 180ms ease; border: 3px solid #0078a6; background: transparent; z-index: 999; }
		.dash-bottom-nav:hover { transform: translateX(-50%) scale(1); box-shadow: 0 12px 28px rgba(2,6,23,.12); }
		@media (max-width:520px) { .dash-bottom-nav { bottom: 12px; transform: translateX(-50%); } }
		/* Main content wrapper: add responsive top margin for comfortable spacing */
		.dash-content { max-width: 1100px; margin: clamp(12px, 9vh, 96px) auto 0; padding: 0 16px; position: relative; z-index: 1; }
		.home-hero { text-align: center; }
		.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }
		.bg-logo { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 25%; max-width: 350px; opacity: 0.15; z-index: 0; pointer-events: none; }
		.bg-logo img { width: 100%; height: auto; display: block; }
		.svc-cats { max-width: 1100px; margin: 10px auto 8px; padding: 0 16px; display: flex; gap: 10px; overflow-x: auto; scrollbar-width: none; }
		.svc-cats::-webkit-scrollbar { display: none; }
		.svc-cat { appearance: none; border: 2px solid #e2e8f0; background: #fff; color: #0f172a; border-radius: 999px; padding: 8px 16px; font-weight: 800; font-size: .9rem; white-space: nowrap; cursor: pointer; transition: all .15s ease; }
		.svc-cat:hover { background: #f1f5f9; }
		.svc-cat.active { background:#0078a6; color:#fff; border-color:#0078a6; }
		.svc-cats-wrap { max-width:1100px; margin:10px auto 8px; padding:0 16px; position:relative; }
		/* Ensure pills don't sit under the nav arrows */
		.svc-cats-wrap .svc-cats { margin:0 !important; padding:0 48px !important; }
		.cat-nav-btn { position:absolute; top:50%; transform:translateY(-50%); width:34px; height:34px; border-radius:999px; border:2px solid #e2e8f0; background:#fff; color:#0f172a; display:grid; place-items:center; cursor:pointer; box-shadow:0 6px 16px rgba(0,0,0,.08); transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease; z-index:2; }
		.cat-nav-btn:hover { transform:translateY(-50%) scale(1.05); border-color:#0078a6; background:#f8fafc; }
		.cat-nav-btn[disabled] { opacity:.35; cursor:default; transform:translateY(-50%); box-shadow:none; }
		.cat-nav-btn.prev { left:8px; }
		.cat-nav-btn.next { right:8px; }
		.cat-nav-btn svg { width:16px; height:16px; }
		.results-bar { max-width: 1100px; margin: 0 auto 10px; padding: 0 16px; display:flex; align-items:center; justify-content: space-between; gap:10px; }
		.results-left { display:flex; align-items:center; gap:8px; color:#64748b; font-weight:700; }
		.results-left .ico { width:18px; height:18px; color: #64748b; }
		.results-right { display:flex; align-items:center; gap:8px; }
		.notify-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border-radius:10px; border:2px solid #e2e8f0; background:#fff; color:#0f172a; font-weight:800; cursor:pointer; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
		.notify-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.08); border-color:#0078a6; }
		.toggle-btn { display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:10px; border:2px solid #e2e8f0; background:#fff; color:#0f172a; cursor:pointer; text-decoration:none; transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease; }
		.toggle-btn:hover { background:#f8fafc; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.08); border-color:#0078a6; }
		.toggle-btn svg, .toggle-btn img { width:18px; height:18px; }
		.svc-list { max-width: 1100px; margin: 0 auto 18px; padding: 0 16px; display:grid; gap:10px; }
		/* Align Recent Posts with above elements */
		.jobs-feed { max-width: 1100px; margin: 0 auto 18px; padding: 0 16px; }
		.feed-title { font-weight:800; color:#0f172a; margin: 0 0 8px; }
		.svc-card { display:grid; grid-template-columns: 1fr auto; gap:12px; align-items:flex-start; background:#fff; border:2px solid #e2e8f0; border-radius:12px; padding:14px 16px; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
		.svc-card:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0,0,0,.08); border-color:#0078a6; }
		.svc-title { margin:0 0 8px; font-weight:800; color:#0f172a; }
		.svc-meta { display:flex; flex-wrap:wrap; gap:8px 14px; color:#64748b; font-size:.9rem; }
		.svc-meta .item { display:inline-flex; align-items:center; gap:6px; white-space:nowrap; }
		.svc-meta .item svg { width:14px; height:14px; }
		.svc-posted { margin-top:8px; display:flex; align-items:center; gap:8px; color:#94a3b8; font-size:.85rem; }
		.svc-av { width:22px; height:22px; border-radius:50%; background:#e2e8f0; color:#0f172a; display:grid; place-items:center; font-weight:800; font-size:.75rem; }
		.svc-price { display:grid; align-content:center; gap:4px; text-align:right; }
		.svc-price .amt { font-weight:800; color:#0078a6; }
		.svc-price .note { color:#94a3b8; font-size:.8rem; }
		/* Search row: bar on left, bell on right */
		.svc-search { max-width: 1100px; margin: clamp(16px, 6vh, 72px) auto 16px; padding: 0 16px; position: relative; display:flex; align-items:center; gap:12px; }
		.svc-search-box { display: flex; align-items: center; gap: 12px; background: #fff; border: 2px solid #e2e8f0; border-radius: 999px; padding: 10px 12px; box-shadow: 0 4px 12px rgba(0,0,0,.06); transition: box-shadow .15s ease, border-color .15s ease; }
		.svc-search-box { flex: 1; }
		.svc-search-box:focus-within { border-color: #0078a6; box-shadow: 0 8px 20px rgba(0,120,166,.12); }
		.svc-search-icon { width: 20px; height: 20px; color: #64748b; flex-shrink: 0; }
		.svc-search-input { appearance: none; border: 0; outline: 0; background: transparent; font: inherit; color: #0f172a; flex: 1; font-size: .95rem; }
		.svc-search-input::placeholder { color: #94a3b8; }

		/* Notification button inside search */
		.svc-notify-btn { appearance:none; border:0; outline:0; background: transparent; display:inline-grid; place-items:center; width: 32px; height: 32px; border-radius: 999px; color:#64748b; cursor:pointer; flex-shrink:0; position: relative; transition: background .15s ease, color .15s ease, transform .15s ease; }
		.svc-notify-btn:hover { background:#f8fafc; color:#0078a6; transform: translateY(-1px); }
		.svc-notify-btn:active { transform: translateY(0); }
		.svc-notify-btn svg { width: 18px; height: 18px; }

		/* Counter badge */
		.svc-badge { position:absolute; top:-2px; right:-2px; min-width:16px; height:16px; padding:0 4px; border-radius:999px; background:#ef4444; color:#fff; font-weight:800; font-size:10px; line-height:16px; display:inline-grid; place-items:center; box-shadow:0 0 0 2px #fff; }
		.svc-badge[data-count="0"], .svc-badge[hidden] { display:none; }

		/* Drawer panel */
		.notify-wrap { position: relative; display: inline-flex; align-items: center; }
		.svc-notify-drawer { position:absolute; right:0; top: calc(100% + 10px); width: min(92vw, 360px); max-height: 60vh; overflow:auto; background:#fff; border:2px solid #e2e8f0; border-radius: 14px; box-shadow: 0 12px 28px rgba(0,0,0,.16); z-index:1100; opacity:0; transform: translateY(-4px); transition: opacity .18s ease, transform .18s ease; }
		.svc-notify-drawer[hidden] { display:block !important; opacity:0; pointer-events:none; }
		.svc-notify-drawer.is-open { opacity:1; transform: translateY(0); }
		.svc-notify-header { position:sticky; top:0; background:#fff; padding:12px 14px; border-bottom:2px solid #e2e8f0; font-weight:800; color:#0f172a; z-index:1; }
		.svc-tabs { display:flex; gap:8px; padding:8px; border-bottom:2px solid #e2e8f0; background:#fff; position:sticky; top:44px; z-index:1; }
		.svc-tab { flex:1; appearance:none; border:2px solid #e2e8f0; background:#fff; color:#0f172a; border-radius:999px; padding:8px 12px; font-weight:800; cursor:pointer; transition: background .15s ease, color .15s ease, border-color .15s ease, transform .15s ease; }
		.svc-tab:hover { background:#f8fafc; transform: translateY(-1px); }
		.svc-tab[aria-selected="true"], .svc-tab.is-active { background:#0078a6; color:#fff; border-color:#0078a6; box-shadow:0 6px 16px rgba(0,120,166,.22); transform:none; }
		.svc-notify-list { list-style:none; margin:0; padding:8px; display:grid; gap:8px; }
		.svc-notify-item { display:grid; grid-template-columns: 1fr auto; gap:8px; align-items:flex-start; padding:10px 12px; border:2px solid #e2e8f0; border-radius:12px; background:#fff; }
		.svc-notify-item .title { font-weight:800; color:#0f172a; margin:0 0 4px; }
		.svc-notify-item .meta { color:#64748b; font-size:.85rem; }
		.svc-notify-item .time { color:#94a3b8; font-size:.8rem; white-space:nowrap; }

		/* Backdrop */
		.svc-notify-backdrop { position:fixed; inset:0; background: rgba(15,23,42,.28); backdrop-filter: blur(1px); z-index:1090; opacity:0; pointer-events:none; transition: opacity .18s ease; }
		.svc-notify-backdrop.is-open { opacity:1; pointer-events:auto; }
		/* Sample post layout container */
		.sample-post { max-width:1100px; margin:0 auto 24px; padding:0 16px; }
		.sample-post .sample-title { font-weight:800; color:#0f172a; margin: 0 0 8px; }
		/* Page background: use plain white (homebackground asset left intact but not applied) */
		body.theme-profile-bg {
			background: #ffffff !important;
			background-attachment: initial !important;
		}
		.dash-shell { position: relative; z-index: 1; }
		.dash-overlay { display: none !important; }

		/* Right-side full-height sidebar nav (from profile.php) */
		.dash-float-nav {
			position: fixed; top: 0; right: 0; bottom: 0;
			z-index: 1000;
			display: flex !important; flex-direction: column; justify-content: flex-start;
			gap: 8px;
			padding: 12px 8px 8px 8px;
			border-right: 0;
			background: rgba(255,255,255,.95);
			backdrop-filter: saturate(1.15) blur(12px);
			border-top-left-radius: 16px; border-bottom-left-radius: 16px;
			border-top-right-radius: 0; border-bottom-right-radius: 0;
			box-shadow: 0 8px 24px rgba(0,120,166,.28), 0 0 0 1px rgba(255,255,255,.4) inset;
			transition: width .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s ease;
			width: 56px; overflow: hidden;
		}
		.dash-float-nav:hover { width: 200px; box-shadow: 0 12px 32px rgba(0,120,166,.35), 0 0 0 1px rgba(255,255,255,.5) inset; }

		/* Brand at top: job_logo by default, bluefont on hover */
		.dash-float-nav .nav-brand { display: grid; place-items: center; position: relative; height: 56px; padding: 6px 0; }
		.dash-float-nav .nav-brand a { display:block; width:100%; height:100%; position:relative; text-decoration:none; }
		.dash-float-nav .nav-brand img {
			position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
			display:block; object-fit:contain; pointer-events:none;
			transition: opacity .25s ease, transform .25s ease, width .3s ease;
		}
		.dash-float-nav .nav-brand .logo-small { width:26px; height:auto; opacity:1; }
		.dash-float-nav .nav-brand .logo-wide { width:160px; height:auto; opacity:0; }
		.dash-float-nav:hover .nav-brand .logo-small { opacity:0; transform:translate(-50%,-50%) scale(.96); }
		.dash-float-nav:hover .nav-brand .logo-wide { opacity:1; transform:translate(-50%,-50%) scale(1); }

		/* Groups */
		.dash-float-nav > .nav-main { display:grid; gap:8px; align-content:start; }
		.dash-float-nav > .nav-settings { margin-top:auto; display:grid; gap:8px; }

		/* Links and icons */
		.dash-float-nav a {
			position: relative;
			width: 40px; height: 40px;
			display: grid; grid-template-columns: 40px 1fr; place-items: center; align-items: center;
			border-radius: 12px; color: #0f172a; text-decoration: none; outline: none; white-space: nowrap;
			transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4,0,0.2,1);
		}
		.dash-float-nav:hover a { width: 184px; }
		.dash-float-nav a:hover:not(.active) { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); transform: scale(1.05); }
		.dash-float-nav a:focus-visible { box-shadow: 0 0 0 3px rgba(0,120,166,.3); }
		.dash-float-nav a.active { background: linear-gradient(135deg, #0078a6 0%, #006a94 100%); color:#fff; box-shadow: 0 6px 18px rgba(0,120,166,.4); }
		.dash-float-nav a.active::after {
			content: ""; position: absolute; left: -5px; width: 3px; height: 18px;
			background: linear-gradient(180deg, #0078a6 0%, #0078a6 100%); border-radius: 2px;
			box-shadow: 0 0 0 2px rgba(255,255,255,.9), 0 0 12px rgba(0,120,166,.6);
		}
		.dash-float-nav .dash-icon { width:18px; height:18px; justify-self:center; object-fit:contain; transition: transform .2s ease; }
		.dash-float-nav a:hover .dash-icon { transform: scale(1.1); }
		.dash-float-nav a .dash-text {
			opacity:0; transform:translateX(-10px);
			transition: opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s;
			font-weight:800; font-size:.85rem; color:inherit; justify-self:start; padding-left:8px;
		}
		.dash-float-nav:hover a .dash-text { opacity:1; transform:translateX(0); }

		/* Remove top bar on this page */
		.dash-topbar { display: none !important; }

		/* Remove bottom nav on this page */
		.dash-bottom-nav { display: none !important; }

		/* Sidebar nav: match settings.php colors and remove border/inset line */
		.dash-float-nav {
			background: #2596be !important;
			border: none !important;
			box-shadow: 0 8px 24px rgba(0,0,0,.24) !important; /* no inset line */
		}
		.dash-float-nav:hover {
			box-shadow: 0 12px 32px rgba(0,0,0,.28) !important; /* override hover inset shadow */
		}
		.dash-float-nav a { color: #fff !important; }
		.dash-float-nav a:hover:not(.active) {
			background: rgba(255,255,255,.15) !important;
			color: #fff !important;
		}
		.dash-float-nav a.active {
			background: rgba(255,255,255,.22) !important;
			color: #fff !important;
			box-shadow: 0 6px 18px rgba(0,0,0,.22) !important;
		}
		/* Remove the active left strip line */
		.dash-float-nav a.active::after { display: none !important; }

		/* Unbold all texts sitewide, except nav bars */
		:root { --fw-normal: 400; --fw-bold: 800; }
		body, body *:not(svg):not(path) { font-weight: var(--fw-normal) !important; }
		.dash-aside .dash-nav a,
		.dash-aside .dash-nav a span,
		.dash-float-nav a,
		.dash-float-nav a .dash-text { font-weight: var(--fw-bold) !important; }

		/* Re-bold specific titles (e.g., "Household Cleaning (2-Bedroom)") */
		.svc-title,
		.fc-title { font-weight: var(--fw-bold) !important; }

		/* Recent Posts Section */
		/* removed - recent posts UI removed */
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-overlay"></div>
	<div class="dash-shell">
		<main class="dash-content">
			<!-- NEW: search bar below hero -->
			<section class="svc-search" aria-label="Search gawain">
				<div class="svc-search-box">
					<svg class="svc-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
					</svg>
					<input class="svc-search-input" type="search" name="svc-search" placeholder="Search gawain (e.g., cleaning, plumbing)" aria-label="Search gawain">
				</div>
				<div class="notify-wrap">
					<button type="button" class="svc-notify-btn" aria-label="Notifications" title="Notifications" aria-expanded="false" aria-controls="svcNotifyDrawer">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M18 8a6 6 0 10-12 0c0 7-3 8-3 8h18s-3-1-3-8"/>
							<path d="M13.73 21a2 2 0 01-3.46 0"/>
						</svg>
						<span class="svc-badge" data-count="3">3</span>
					</button>
					<div id="svcNotifyDrawer" class="svc-notify-drawer" role="dialog" aria-label="Notifications" hidden>
						<div class="svc-notify-header">Notifications</div>
						<div class="svc-tabs" role="tablist" aria-label="Notification role">
							<button class="svc-tab is-active" id="tabKasangga" role="tab" aria-selected="true" data-role="kasangga">As a Kasangga</button>
							<button class="svc-tab" id="tabCitizen" role="tab" aria-selected="false" data-role="citizen">As a Citizen</button>
						</div>
						<ul class="svc-notify-list">
							<li class="svc-notify-item" data-role="kasangga">
								<div>
									<p class="title">New job posted near you</p>
									<p class="meta">Plumbing • ₱1,500 • Today</p>
								</div>
								<time class="time">2m ago</time>
							</li>
							<li class="svc-notify-item" data-role="citizen">
								<div>
									<p class="title">Your post got a reply</p>
									<p class="meta">Cleaning • 1 offer</p>
								</div>
								<time class="time">12m ago</time>
							</li>
							<li class="svc-notify-item" data-role="kasangga">
								<div>
									<p class="title">Reminder: Job starts tomorrow</p>
									<p class="meta">Painting • 9:00 AM</p>
								</div>
								<time class="time">1h ago</time>
							</li>
						</ul>
					</div>
				</div>
			</section>

			<!-- Categories carousel: full list + arrows -->
			<div class="svc-cats-wrap" aria-label="Categories carousel">
				<button type="button" class="cat-nav-btn prev" id="catPrev" aria-label="Previous categories">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
				</button>
				<nav id="svcCats" class="svc-cats" aria-label="Categories">
					<button type="button" class="svc-cat active" data-cat="All">All</button>
					<button type="button" class="svc-cat" data-cat="Business & admin">Business &amp; admin</button>
					<button type="button" class="svc-cat" data-cat="Care services">Care services</button>
					<button type="button" class="svc-cat" data-cat="Creative">Creative</button>
					<button type="button" class="svc-cat" data-cat="Household">Household</button>
					<button type="button" class="svc-cat" data-cat="Part-time">Part-time</button>
					<button type="button" class="svc-cat" data-cat="Research">Research</button>
					<button type="button" class="svc-cat" data-cat="Social media">Social media</button>
					<button type="button" class="svc-cat" data-cat="Talents">Talents</button>
					<button type="button" class="svc-cat" data-cat="Teach me">Teach me</button>
					<button type="button" class="svc-cat" data-cat="Tech & IT">Tech &amp; IT</button>
					<button type="button" class="svc-cat" data-cat="Others">Others</button>
				</nav>
				<button type="button" class="cat-nav-btn next" id="catNext" aria-label="Next categories">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
				</button>
			</div>

			<div class="results-bar">
				<div class="results-left">
					<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
					<span><?php echo (int)count($jobs); ?> results</span>
				</div>
				<div class="results-right">
					<a href="./filter.php" class="toggle-btn" aria-label="Filter">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M3 5h18l-7 8v6l-4 2v-8L3 5z"/>
						</svg>
					</a>
				</div>
			</div>

			<div class="svc-notify-backdrop" hidden></div>

			<section class="svc-list" aria-label="Nearby posts">
				<?php if (!$dbAvailable): ?>
					<!-- DB unavailable: hide message from UI -->
				<?php elseif (empty($jobs)): ?>
					<div class="form-card glass-card" style="background:#fff;color:#0f172a;border-color:#e2e8f0">
						No posts yet. Be the first to post using the + button.
					</div>
				<?php else: ?>
					<?php foreach ($jobs as $j): ?>
					<?php $jid = isset($j['id']) ? (int)$j['id'] : 0; ?>
					<a class="svc-card" data-category="<?php echo e($j['category'] ?? ''); ?>" href="./gawain-detail.php<?php echo $jid ? ('?id=' . $jid) : ''; ?>" aria-label="View post: <?php echo e($j['title']); ?>">
						<div>
							<h3 class="svc-title" title="<?php echo e($j['title']); ?>"><?php echo e($j['title']); ?></h3>
							<div class="svc-meta">
								<?php if (!empty($j['location'])): ?>
									<span class="item" title="<?php echo e($j['location']); ?>">
										<svg viewBox="0 0 24 24"><path d="M12 21s-6-4.35-6-9a6 6 0 1 1 12 0c0 4.65-6 9-6 9Z"/><circle cx="12" cy="12" r="2"/></svg>
										<?php echo e($j['location']); ?>
									</span>
								<?php endif; ?>
								<?php if (!empty($j['date_needed'])): ?>
									<span class="item">
										<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
										On <?php echo e($j['date_needed']); ?>
									</span>
								<?php endif; ?>
							</div>
							<div class="svc-posted">
								<span class="svc-av"><?php echo htmlspecialchars($avatar); ?></span>
								<span>Posted <?php echo e(time_ago($j['posted_ts'] ?? $j['posted_at'])); ?></span>
							</div>
						</div>
						<div class="svc-price">
							<span class="amt"><?php echo !empty($j['budget']) ? '₱'.e($j['budget']) : 'Negotiable'; ?></span>
							<?php if (empty($j['budget'])): ?><span class="note">Negotiable</span><?php endif; ?>
						</div>
					</a>
				<?php endforeach; ?>
				<?php endif; ?>
			</section>

		</main>

		<aside class="dash-aside">
			<nav class="dash-nav" aria-label="Main navigation">
				<a href="./home-gawain.php" class="active" aria-label="Browse">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
					</svg>
					<span>Browse</span>
				</a>
				<a href="./my-gawain.php" aria-label="My Gawain">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
					<span>My Gawain</span>
				</a>
				<a href="./user-detail.php<?php echo $self_uid ? ('?id='.$self_uid) : ''; ?>" aria-label="Profile">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
					<span>Profile</span>
				</a>
			</nav>
		</aside>
	</div>

	<!-- Floating bottom navigation (removed) -->
	<!--
	<nav class="dash-bottom-nav">
		<a href="./home-gawain.php" class="active" aria-label="Browse">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
			</svg>
			<span>Browse</span>
		</a>
		<a href="./post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span>Post</span>
		</a>
		<a href="./my-gawain.php" aria-label="My Gawain">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Gawain</span>
		</a>
		<a href="./chats.php" aria-label="Chats">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			<span>Profile</span>
		</a>
		<a href="./profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
	-->

	<!-- Right-side full-height sidebar navigation (copied from profile.php) -->
	<nav class="dash-float-nav" id="dashNav">
		<div class="nav-brand">
			<a href="./home-gawain.php" title="">
				<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub">
				<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub">
			</a>
		</div>

		<div class="nav-main">
			<a href="./user-detail.php<?php echo $self_uid ? ('?id='.$self_uid) : ''; ?>" aria-label="Profile">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
				<span class="dash-text">Profile</span>
			</a>
			<a href="./post.php" aria-label="Post">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/>
				</svg>
				<span class="dash-text">Post</span>
			</a>
			<a href="./my-gawain.php" aria-label="My Gawain">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
				<span class="dash-text">My Gawain</span>
			</a>
			<a href="./chats.php" aria-label="Chats">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
				<span class="dash-text">Chats</span>
			</a>
		</div>

		<div class="nav-settings">
			<a href="./about-us.php" aria-label="About Us">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
				</svg>
				<span class="dash-text">About Us</span>
			</a>
			<a href="./terms-and-conditions.php" aria-label="Terms & Conditions">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M6 4h12v16H6z"/><path d="M8 8h8M8 12h8M8 16h5"/>
				</svg>
				<span class="dash-text">Terms & Conditions</span>
			</a>
			<a href="./profile.php?logout=1" aria-label="Log out">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 21V3"/>
				</svg>
				<span class="dash-text">Log out</span>
			</a>
		</div>
	</nav>

	<script>
	// Categories carousel + search + filter logic
	(function(){
		const row = document.getElementById('svcCats');
		const prev = document.getElementById('catPrev');
		const next = document.getElementById('catNext');
		const search = document.querySelector('.svc-search-input');
		// only svc-card elements remain for filtering
		const svcCards = document.querySelectorAll('.svc-card');
		
		if (!row || !prev || !next) return;

		let activeCategory = 'All';

		function updateArrows(){
			const max = row.scrollWidth - row.clientWidth - 1;
			prev.disabled = row.scrollLeft <= 0;
			next.disabled = row.scrollLeft >= max;
		}
		
		function scrollByStep(dir){
			const step = Math.max(160, Math.floor(row.clientWidth * 0.9));
			row.scrollBy({ left: dir * step, behavior: 'smooth' });
			setTimeout(updateArrows, 250);
		}
		
		prev.addEventListener('click', ()=> scrollByStep(-1));
		next.addEventListener('click', ()=> scrollByStep(1));
		row.addEventListener('scroll', updateArrows, { passive: true });
		window.addEventListener('resize', updateArrows);

		// Category click: activate, fill search, filter posts
		row.addEventListener('click', (e)=>{
			const btn = e.target.closest('.svc-cat');
			if (!btn) return;
			
			// Update active state
			row.querySelectorAll('.svc-cat').forEach(b=>b.classList.remove('active'));
			btn.classList.add('active');
			
			activeCategory = btn.getAttribute('data-cat') || 'All';
			
			// Fill/clear search box
			if (search) {
				if (activeCategory === 'All') {
					search.value = '';
				} else {
					search.value = activeCategory;
				}
			}
			
			// Filter svc cards by category
			filterPosts(activeCategory);
		});

		function normalize(s){ return (s || '').toString().trim().toLowerCase(); }

		function filterPosts(category){
			const catNorm = normalize(category);
			// Filter service list cards only
			svcCards.forEach(card => {
				const cardCat = normalize(card.dataset.category || '');
				if (!catNorm || catNorm === 'all' || cardCat === catNorm) {
					card.style.display = '';
				} else {
					card.style.display = 'none';
				}
			});
		}

		// Search box typing also filters
		if (search) {
			search.addEventListener('input', ()=>{
				const term = search.value.trim().toLowerCase();
				
				// If search matches a category, activate it
				const matchBtn = Array.from(row.querySelectorAll('.svc-cat')).find(b => {
					const cat = (b.getAttribute('data-cat') || '').toLowerCase();
					return cat === term;
				});
				
				if (matchBtn) {
					row.querySelectorAll('.svc-cat').forEach(b=>b.classList.remove('active'));
					matchBtn.classList.add('active');
					activeCategory = matchBtn.getAttribute('data-cat') || 'All';
					filterPosts(activeCategory);
				} else if (term === '') {
					// Empty search = All
					const allBtn = row.querySelector('.svc-cat[data-cat="All"]');
					if (allBtn) {
						row.querySelectorAll('.svc-cat').forEach(b=>b.classList.remove('active'));
						allBtn.classList.add('active');
						activeCategory = 'All';
						filterPosts('All');
					}
				} else {
					// Free-text search: show svc-cards that match text
					svcCards.forEach(card => {
						const text = card.textContent.toLowerCase();
						card.style.display = text.includes(term) ? '' : 'none';
					});
				}
			});
		}

		updateArrows();
	})();
	</script>

	<script>
	// Notification drawer logic
	(function(){
		const btn = document.querySelector('.svc-notify-btn');
		const drawer = document.getElementById('svcNotifyDrawer');
		const backdrop = document.querySelector('.svc-notify-backdrop');
		const tabs = drawer ? drawer.querySelectorAll('.svc-tab') : null;
		const items = drawer ? drawer.querySelectorAll('.svc-notify-item') : null;
		if (!btn || !drawer || !backdrop) return;

		function openDrawer(){
			drawer.hidden = false; // ensure it's in layout for transition
			requestAnimationFrame(()=>{
				drawer.classList.add('is-open');
				backdrop.classList.add('is-open');
			});
			btn.setAttribute('aria-expanded','true');
		}

		function closeDrawer(){
			drawer.classList.remove('is-open');
			backdrop.classList.remove('is-open');
			btn.setAttribute('aria-expanded','false');
			// Wait for transition to end before hiding
			setTimeout(()=>{ drawer.hidden = true; }, 180);
		}

		btn.addEventListener('click', (e)=>{
			e.stopPropagation();
			const isOpen = btn.getAttribute('aria-expanded') === 'true';
			if (isOpen) closeDrawer(); else openDrawer();
		});

		backdrop.addEventListener('click', closeDrawer);

		document.addEventListener('keydown', (e)=>{
			if (e.key === 'Escape') closeDrawer();
		});

		// Close when clicking outside the drawer
		document.addEventListener('click', (e)=>{
			if (!drawer.contains(e.target) && !btn.contains(e.target)) {
				closeDrawer();
			}
		});

		// Tabs: filter items by data-role
		function applyRoleFilter(role){
			if (!items) return;
			items.forEach(it => {
				const r = it.getAttribute('data-role') || 'kasangga';
				it.style.display = (role === 'all' || r === role) ? '' : 'none';
			});
		}

		if (tabs && tabs.length) {
			// Default to kasangga tab active
			applyRoleFilter('kasangga');
			tabs.forEach(tab => {
				tab.addEventListener('click', () => {
					const role = tab.getAttribute('data-role') || 'kasangga';
					tabs.forEach(t => { t.classList.remove('is-active'); t.setAttribute('aria-selected','false'); });
					tab.classList.add('is-active');
					tab.setAttribute('aria-selected','true');
					applyRoleFilter(role);
				});
			});
		}

		// Optional: reflect count from data-count attribute
		const badge = btn.querySelector('.svc-badge');
		if (badge) {
			const count = parseInt(badge.getAttribute('data-count')||'0',10);
			badge.textContent = String(count);
			if (!count) badge.setAttribute('hidden',''); else badge.removeAttribute('hidden');
		}
	})();
	</script>
</body>
</html>