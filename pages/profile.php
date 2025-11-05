<?php
session_start();
$__logout = isset($_GET['logout']);
if ($__logout) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ./login.php');
    exit;
}


// Include DB and normalize handle
include '../config/db_connect.php';
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
	if (isset($conn) && $conn instanceof mysqli) { $mysqli = $conn; }
}

// Ensure 'prof-name' defaults to 'USER' after login until edited
$mobile = $_SESSION['mobile'] ?? '';
if ($mobile && (!isset($_SESSION['display_name']) || trim($_SESSION['display_name']) === '' || $_SESSION['display_name'] === 'Guest')) {
    $_SESSION['display_name'] = 'USER';
}
$display = $_SESSION['display_name'] ?? ($mobile ? 'USER' : 'Guest');

// If logged in, always refresh display name and avatar from DB so repeated edits reflect
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$dbAvatarPath = '';
if ($user_id > 0 && isset($mysqli) && $mysqli instanceof mysqli && !$mysqli->connect_error) {
	if ($stmt = $mysqli->prepare("SELECT username, COALESCE(avatar,'') FROM users WHERE id = ?")) {
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		$stmt->bind_result($dbUsername, $dbAvatarPath);
		if ($stmt->fetch()) {
			if (!empty($dbUsername)) {
				$_SESSION['display_name'] = $dbUsername;
				$display = $dbUsername;
			}
		}
		$stmt->close();
	}
}

// Replace the basic identity setup with a safer version
$display_name = trim((string)($_SESSION['display_name'] ?? ''));
$mobile_raw   = trim((string)($_SESSION['mobile'] ?? ''));
$mobile       = $mobile_raw;

// If a proper name exists, use it; else fall back to mobile, then Guest
$display = $display_name !== '' ? $display_name : ($mobile !== '' ? $mobile : 'Guest');

// helper: resolve avatar path to URL
if (!function_exists('avatar_url')) {
	function avatar_url($path){ return $path ? '../'.ltrim($path,'/') : ''; }
}
$avatarUrl = avatar_url($dbAvatarPath ?? '');

// Avatar: prefer first alphabet of name; if none, use 'G'
$initialSrc = $display_name !== '' ? $display_name : 'Guest';
$firstAlpha = preg_replace('/[^A-Za-z]/', '', $initialSrc);
$avatar     = strtoupper(substr($firstAlpha !== '' ? $firstAlpha : 'G', 0, 1));

// About Me data (with safe fallbacks)
$joined_raw = $_SESSION['joined_at'] ?? null;
$joined_ts  = is_numeric($joined_raw) ? (int)$joined_raw : ($joined_raw ? strtotime((string)$joined_raw) : time());
$joined_date = date('m/d/Y', $joined_ts);
$kasangga_done = (int)($_SESSION['kasangga_completed'] ?? 0);
$citizen_done = (int)($_SESSION['citizen_completed'] ?? 0);
$skills = $_SESSION['skills'] ?? ['AI & Machine Learning', 'Frontend Development', 'Software Development'];
$portfolio_url = trim((string)($_SESSION['portfolio_url'] ?? ''));

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Profile • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
	<style>
		/* centered floating bottom navigation (match home-services.php) */
		.dash-bottom-nav {
			position: fixed;
			left: 50%;
			right: auto;           /* ensure true centering */
			bottom: 16px;
			z-index: 1000;
			width: max-content;
			transform: translateX(-50%) scale(0.92);
			transform-origin: bottom center;
			transition: transform 180ms ease, box-shadow 180ms ease;
			border: 3px solid #0078a6;
			background: transparent;
		}
		.dash-bottom-nav:hover {
			transform: translateX(-50%) scale(1);
			box-shadow: 0 12px 28px rgba(2,6,23,.12);
		}

		/* Enhancements: profile header and menu visuals */
		.prof-container { max-width: 480px; }

		.prof-kasangga {
			/* ensure inner spacing from rounded edges */
			padding: 14px 16px;
			/* align avatar + details side-by-side */
			display: grid;
			grid-template-columns: 56px 1fr;
			gap: 12px;
			align-items: center;
			/* put the blue box back */
			border-radius: 16px;
			background: #0078a6;
			color: #fff;
			border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
			box-shadow: 0 10px 28px rgba(0,120,166,.24);
		}
		.prof-avatar {
			/* fixed, centered circle */
			width: 56px;
			height: 56px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #e6f2f8; /* light tint for contrast */
			color: #0f172a;
			font-weight: 900;
			font-size: 1rem;
		}
		.prof-kasangga .prof-name { margin: 0 0 2px; line-height: 1.2; }
		.prof-kasangga .prof-meta { margin: 0 0 8px; }
		.prof-kasangga .prof-edit { margin-top: 2px; }

		@media (max-width:420px){
			.prof-kasangga {
				grid-template-columns: 52px 1fr;
				gap: 10px;
				padding: 12px 14px;
			}
			.prof-avatar { width: 52px; height: 52px; }
		}

		.prof-name, .prof-meta { color: #fff !important; }
		.prof-avatar {
			box-shadow: inset 0 0 0 2px rgba(255,255,255,.85), 0 8px 18px rgba(2,6,23,.14);
		}
		.prof-edit {
			display: inline-flex; align-items: center; gap: 6px;
			background: #fff; color: #0078a6; padding: 6px 12px; border-radius: 999px;
			text-decoration: none; font-weight: 800;
			transition: filter .15s ease, transform .15s ease, box-shadow .15s ease;
			box-shadow: 0 8px 20px rgba(255,255,255,.2);
		}
		.prof-edit:hover { filter: brightness(1.05); transform: translateY(-1px); box-shadow: 0 12px 28px rgba(255,255,255,.3); }

		.prof-menu {
			display: grid;
			gap: 10px;
			padding: 12px;
			border-radius: 16px;
			background: #0078a6;
			box-shadow: 0 10px 28px rgba(0,120,166,.24);
		}
		.prof-sep { display: none; } /* use spacing instead of separators */

		.prof-item {
			display: flex; align-items: center; justify-content: space-between; gap: 12px;
			padding: 14px 16px; border-radius: 14px;
			background: rgba(255,255,255,.15); color: #fff;
			border: 2px solid rgba(255,255,255,.5);
			box-shadow: 0 4px 12px rgba(0,0,0,.15);
			text-decoration: none;
			transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
		}
		.prof-item:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 20px rgba(0,0,0,.2);
			background: #0078a6;
			color: #fff;
		}
		.prof-item:hover .prof-ico,
		.prof-item:hover .prof-chev { color: #fff; }
		.prof-item:active { transform: translateY(0); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
		.prof-item .prof-ico { color: #fff; opacity: .95; transition: color .15s ease; }
		.prof-item span { font-weight: 800; }
		.prof-chev { width: 18px; height: 18px; color: #fff; opacity: .9; flex: 0 0 18px; transition: color .15s ease; }

		/* Entrance animation for the profile container */
		@media (prefers-reduced-motion: no-preference) {
			.prof-container { animation: profIn .45s ease both; }
			@keyframes profIn { from { opacity: .0; transform: translateY(8px); } to { opacity:1; transform:none; } }
		}

		/* Stronger focus-visible rings on actionable elements */
		.prof-edit:focus-visible,
		.prof-item:focus-visible {
			outline: 3px solid rgba(14,116,162,.28);
			outline-offset: 3px;
			border-radius: 14px;
		}

		/* Blue bottom border on topbar */
		.dash-topbar { border-bottom: 3px solid #0078a6; }

		/* Background logo - transparent and behind UI */
		.bg-logo {
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			width: 25%;
			max-width: 350px;
			opacity: 0.15;
			z-index: 0;
			pointer-events: none;
		}
		.bg-logo img {
			width: 100%;
			height: auto;
			display: block;
		}

		/* Ensure img logo fits like other icons */
		.dash-float-nav .dash-icon {
			width: 18px;
			height: 18px;
			justify-self: center;
			display: inline-block;
			object-fit: contain;
			color: currentColor;            /* ensure svg inherits link color */
			stroke-linecap: round;          /* crisper lines */
			stroke-linejoin: round;
			transition: transform .2s ease; /* subtle hover scale */
		}
		.dash-float-nav a:hover .dash-icon { transform: scale(1.1); }

		/* Ensure main content is above background */
		.dash-topbar {
			position: relative;
			z-index: 1;
		}
		.profile-bg {
			position: relative;
			z-index: 1;
			background: transparent !important;
		}
		.prof-container {
			position: relative;
			z-index: 1;
		}

		/* page override: white background */
		body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

		/* Tabs + cards (neutral, site-consistent) */
		.prof-tabs { margin-top: 14px; }
		.tabbar { display:flex; gap:8px; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:8px; }
		.tab-link {
			appearance:none; border:2px solid #e2e8f0; background:#fff; color:#0f172a; cursor:pointer;
			padding:8px 12px; border-radius:999px; font-weight:800; font-size:.92rem;
			transition: background .15s ease, color .15s ease, border-color .15s ease, transform .15s ease;
		}
		.tab-link:hover { background:#f8fafc; transform: translateY(-1px); }
		.tab-link.active { background:#0078a6; color:#fff; border-color:#0078a6; }

		.tab-panels { margin-top:12px; }
		.tab-panel[hidden]{ display:none; }

		.card {
			background:#fff; color:#0f172a; border:2px solid #e2e8f0;
			border-radius:12px; padding:14px; margin-bottom:12px;
			transition: box-shadow .15s ease, border-color .15s ease;
		}
		.card:hover { border-color:#0078a6; box-shadow:0 8px 20px rgba(0,0,0,.06); }
		.card h4 { margin:0 0 6px; }
		.card .muted { color:#64748b; }

		/* Wallet summary */
		.wallet { display:grid; gap:10px; }
		.wallet-row { display:flex; align-items:baseline; gap:10px; }
		.wallet-cur { color:#64748b; font-weight:700; }
		.wallet-amt { font-size:2rem; font-weight:900; color:#0f172a; line-height:1; }
		.wallet-actions { display:flex; gap:8px; flex-wrap:wrap; }
		.btn-chip {
			appearance:none; border:2px solid #e2e8f0; background:#fff; color:#0f172a; font-weight:800;
			padding:8px 12px; border-radius:10px; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px;
			transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease;
		}
		.btn-chip:hover { transform: translateY(-1px); box-shadow:0 6px 16px rgba(0,0,0,.08); border-color:#0078a6; background:#f8fafc; }
		.wallet-note { display:flex; align-items:center; gap:6px; color:#94a3b8; font-size:.9rem; }

		/* Transactions list */
		.txn-list { display:grid; gap:8px; }
		.txn-item {
			display:flex; align-items:center; justify-content:space-between; gap:10px;
			background:#fff; border:2px solid #e2e8f0; border-radius:10px; padding:10px 12px;
		}
		.txn-left { display:grid; gap:2px; }
		.txn-title { font-weight:800; color:#0f172a; }
		.txn-sub { color:#94a3b8; font-size:.9rem; }
		.txn-amt { font-weight:900; color:#0f172a; }

		/* Small helpers */
		.sep { height:1px; background:#e2e8f0; margin:8px 0; }

		/* Right-side full-height sidebar nav (smooth expand on hover) */
		.dash-float-nav {
			position: fixed;
			top: 0;
			right: 0;
			bottom: 0;
			z-index: 1000;
			display: flex !important;
			flex-direction: column;
			justify-content: flex-start;
			gap: 8px;
			padding-top: 12px;
			padding-bottom: 8px;
			padding-left: 8px;
			padding-right: 8px;
			border-right: 0;
			border-top: 2px solid color-mix(in srgb, #0078a6 75%, #0000); /* explicit top border */
			background: rgba(255,255,255,.95);
			backdrop-filter: saturate(1.15) blur(12px);
			border-top-left-radius: 16px;
			border-bottom-left-radius: 16px;
			border-top-right-radius: 0;
			border-bottom-right-radius: 0;
			box-shadow: 0 8px 24px rgba(0,120,166,.28), 0 0 0 1px rgba(255,255,255,.4) inset;
			transition: width .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s ease; /* removed transform transition */
			width: 56px;
			overflow: hidden;
		}
		.dash-float-nav:hover {
			width: 200px;
			/* removed: transform: translateY(-2px); */
			box-shadow: 0 12px 32px rgba(0,0,0,.28) !important; /* override hover inset shadow */
			border-top: none !important;
		}
		
		/* First group (all buttons except settings) sticks to top */
		.dash-float-nav > div:first-child {
			display: grid;
			gap: 8px;
			align-content: start;
		}
		/* Settings container pinned to bottom */
		.dash-float-nav .nav-settings {
			margin-top: auto;
		}

		/* Fix icon sizing/alignment */
		.dash-float-nav a {
			position: relative;
			width: 40px;
			height: 40px;
			display: grid;
			grid-template-columns: 40px 1fr; /* icon + label (when expanded) */
			align-items: center;
			border-radius: 12px;
			color: #0f172a;
			text-decoration: none;
			transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4, 0, 0.2, 1);
			outline: none;
			white-space: nowrap;
		}
		.dash-float-nav:hover a {
			width: 184px;
		}
		.dash-float-nav a:hover:not(.active) {
			background: rgba(255,255,255,.15) !important;
			color: #fff !important;
		}
		.dash-float-nav a.active {
			background: rgba(255,255,255,.22) !important;
			color: #fff !important;
			box-shadow: 0 6px 18px rgba(0,0,0,.22) !important;
		}
		.dash-float-nav a.active::after {
			content: ""; position: absolute; left: -5px; width: 3px; height: 18px;
			background: linear-gradient(180deg, #0078a6 0%, #0078a6 100%);
			border-radius: 2px; box-shadow: 0 0 0 2px rgba(255,255,255,.9), 0 0 12px rgba(0,120,166,.6);
		}

		/* Text label (smooth fade and slide in when expanded) */
		.dash-float-nav a .dash-text {
			opacity: 0;
			transform: translateX(-10px);
			transition: opacity .3s cubic-bezier(0.4, 0, 0.2, 1) .1s, transform .3s cubic-bezier(0.4, 0, 0.2, 1) .1s;
			font-weight: 800;
			font-size: .85rem;
			color: inherit;
			justify-self: start;
			padding-left: 8px;
		}
		.dash-float-nav:hover a .dash-text {
			opacity: 1;
			transform: translateX(0);
		}

		/* Hide tooltip labels when not expanded */
		.dash-float-nav a .dash-label {
			display: none;
		}

		/* Center the profile container - REMOVED margin-right adjustment */
		.prof-container {
			max-width: 480px;
			margin: 0 auto; /* center horizontally */
			padding: 0 16px;
		}

		/* Adjust topbar - REMOVED padding-right */
		.dash-topbar {
			position: relative;
		}

		/* Bigger logo for the first nav item only (Browse -> job_logo.png) */
		.dash-float-nav a.logo-link .dash-icon {
			width: 28px;  /* was ~16-22px */
			height: 28px;
			object-fit: contain;
		}

		/* Sidebar: stack from top; keep Settings pinned bottom */
		.dash-float-nav {
			/* ensure full height at the very top */
			position: fixed; top: 0; right: 0; bottom: 0;
			/* force flex layout so items start at top */
			display: flex !important;
			flex-direction: column;
			justify-content: flex-start;
			/* remove large top padding if set before */
			padding-top: 12px !important;
		}

		/* Top group (all nav buttons except settings) - align to top */
		.dash-float-nav > .nav-main {
			display: grid;
			gap: 8px;
			align-content: start;
		}

		/* Fallback: if you don't have .nav-main wrapper, keep first div at top */
		.dash-float-nav > div:first-child { align-content: start; }

		/* Bottom group (Settings) pinned to bottom */
		.dash-float-nav > .nav-settings {
			margin-top: auto !important;
			display: grid;
			gap: 8px;
		}

		/* Remove top bar on this page */
		.dash-topbar { display: none !important; }
		/* Remove any padding added for fixed/sticky topbar */
		body { padding-top: 0 !important; }
		/* Make sure the sidebar starts at the very top */
		.dash-float-nav { top: 0 !important; }

		/* Ensure sidebar stacks from top (brand • main • settings) */
		.dash-float-nav {
			/* ...existing code... */
			display: flex !important;
			flex-direction: column;
			justify-content: flex-start;
		}

		/* Top-centered brand with hover swap (job_logo.png -> bluefont.png) */
		.dash-float-nav .nav-brand {
			display: grid;
			place-items: center;
			position: relative;
			height: 56px;            /* reserve space at the very top */
			padding: 6px 0;
		}
		.dash-float-nav .nav-brand img {
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translate(-50%, -50%);
			display: block;
			object-fit: contain;
			transition: opacity .25s ease, transform .25s ease, width .3s ease;
			pointer-events: none;
		}
		/* Default: show job_logo.png (small mark) */
		.dash-float-nav .nav-brand .logo-small {
			width: 28px; /* was 30px */
			height: auto;
			opacity: 1;
		}
		/* Hidden wordmark until hover */
		.dash-float-nav .nav-brand .logo-wide {
			width: 160px;            /* fits when sidebar expands */
			height: auto;
			opacity: 0;
		}
		/* On hover: fade small out, wordmark in */
		.dash-float-nav:hover .nav-brand .logo-small {
			opacity: 0;
			transform: translate(-50%, -50%) scale(.96);
		}
		.dash-float-nav:hover .nav-brand .logo-wide {
			opacity: 1;
			transform: translate(-50%, -50%) scale(1);
		}

		/* Keep main icons at top and settings pinned to bottom */
		.dash-float-nav > .nav-main { display: grid; gap: 8px; align-content: start; }
		.dash-float-nav > .nav-settings { margin-top: auto; display: grid; gap: 8px; }

		/* Override: slightly smaller job_logo again */
		.dash-float-nav .nav-brand .logo-small {
			width: 26px; /* reduced from 28px */
			height: auto;
		}
		.dash-float-nav a.logo-link .dash-icon {
			width: 18px; /* reduced from 20px */
			height: 18px;
			object-fit: contain;
		}

		/* Sidebar nav: match settings.php colors and remove border/inset line */
		.dash-float-nav {
			background: #2596be !important;
			border: none !important;
			border-top: none !important; /* in case a top border was set */
			box-shadow: 0 8px 24px rgba(0,0,0,.24) !important; /* remove inset 1px line */
		}
		.dash-float-nav:hover {
			box-shadow: 0 12px 32px rgba(0,0,0,.28) !important; /* override hover inset shadow */
			border-top: none !important;
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
		.dash-float-nav a.active::after { display: none !important; }

		/* Reviews sub-tabs (Kasangga/Citizen) */
		.review-tabs {
			display: flex;
			gap: 12px;
			justify-content: center;
			margin: 10px 0 16px;
			width: 100%;
		}
		.review-tab {
			appearance: none;
			border: 2px solid #dbeafe; /* light blue outline */
			background: #fff;
			color: #0f172a;
			padding: 10px 18px;
			border-radius: 12px;
			font-weight: 800;
			cursor: pointer;
			transition: background .15s ease, color .15s ease, border-color .15s ease, transform .15s ease, box-shadow .15s ease;
			box-shadow: 0 2px 8px rgba(2,6,23,.06);
			flex: 1 1 0;           /* equal width for both buttons */
			text-align: center;    /* center labels */
			min-width: 0;          /* allow shrink without overflow */
			box-sizing: border-box;
		}
		.review-tab:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(2,6,23,.12); }
		.review-tab.active {
			background: #0078a6;
			color: #fff;
			border-color: #0078a6;
			box-shadow: 0 8px 20px rgba(0,120,166,.24);
		}
		.review-panel { padding: 4px 2px; }

		/* Optional spacing for unboxed Reviews content */
		.reviews-title { margin: 0 0 8px; font-weight: 800; color: #0f172a; }
		#tab-reviews { padding-top: 4px; }

		/* About Me: list rows, chips, and links */
		.about-verify { margin: 0 0 10px; font-weight: 800; }
		.about-verify a { color: #0078a6; text-decoration: none; }
		.about-verify a:hover { text-decoration: underline; }
		.about-list { list-style: none; margin: 8px 0 14px; padding: 0; display: grid; gap: 10px; }
		.about-row { display: flex; align-items: center; gap: 10px; color: #0f172a; }
		.about-row .ico { width: 18px; height: 18px; color: #64748b; flex: 0 0 18px; }
		.about-label { display: block; font-weight: 800; color: #0f172a; margin: 8px 0 6px; }
		.skill-chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 14px; }
		.skill-chip { border: 2px solid #e2e8f0; background: #fff; color: #0f172a; border-radius: 999px; padding: 6px 10px; font-weight: 800; font-size: .85rem; box-shadow: 0 2px 8px rgba(2,6,23,.06); }
		.about-links a { color: #0078a6; font-weight: 700; text-decoration: none; }
		.about-links a:hover { text-decoration: underline; }

		/* About Me spacing tweaks (scoped to About tab only) */
		#tab-about .card { padding: 18px 18px; }
		#tab-about #aboutme-title { margin-bottom: 12px; }
		#tab-about .muted { margin-bottom: 12px; }
		#tab-about .wallet-actions { margin: 8px 0 16px; }
		#tab-about .about-verify { margin: 14px 0; }
		#tab-about .about-list { margin: 12px 0 18px; gap: 12px; }
		#tab-about .about-row { line-height: 1.5; }
		#tab-about .about-label { margin: 16px 0 8px; }
		#tab-about .skill-chips { gap: 10px; margin-bottom: 18px; }
		#tab-about .about-links { margin-top: 8px; }

		/* Equal-size review buttons (override) */
		#tab-reviews .review-tabs {
			display: grid;                 /* override flex */
			grid-template-columns: 1fr 1fr;/* two equal columns */
			gap: 12px;
			width: 100%;
			justify-content: stretch;      /* ensure full width */
		}
		#tab-reviews .review-tab {
			display: block;                /* fill grid cell */
			width: 100%;
			flex: initial;                 /* neutralize earlier flex */
			min-width: 0;                  /* prevent overflow */
			text-align: center;
			box-sizing: border-box;
		}

		/* Ensure uploaded avatar images fill the circle nicely */
		.prof-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block; }

		/* Unbold all texts sitewide, except nav bars (keep this at the end) */
		:root { --fw-normal: 400; --fw-bold: 800; }
		body, body *:not(svg):not(path) { font-weight: var(--fw-normal) !important; }

		/* Keep navigation labels bold (left aside, right floating, bottom nav) */
		.dash-aside .dash-nav a,
		.dash-aside .dash-nav a span,
		.dash-float-nav a,
		.dash-float-nav a .dash-text,
		.dash-bottom-nav a,
		.dash-bottom-nav a span { font-weight: var(--fw-bold) !important; }

		/* Re-bold page titles and the displayed user name */
		:root { --fw-bold: 800; }
		h1, h2, h3, h4, h5, h6 { font-weight: var(--fw-bold) !important; }
		.prof-name,
		.page-title,
		.card > h4,
		.section-title { font-weight: var(--fw-bold) !important; }
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="profile-bg">
		<div class="prof-container">
			<!-- Profile card -->
			<section class="prof-kasangga" aria-label="Account">
				<div class="prof-avatar" aria-hidden="true">
					<?php if (!empty($avatarUrl)): ?>
						<img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="">
					<?php else: ?>
						<?php echo htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>
					<?php endif; ?>
				</div>
				<div>
					<p class="prof-name"><?php echo htmlspecialchars($display); ?></p>
					<?php if ($mobile && $display !== $mobile): ?>
						<p class="prof-meta"><?php echo htmlspecialchars($mobile); ?></p>
					<?php endif; ?>
					<a class="prof-edit" href="./edit-profile.php">Edit Profile</a>
				</div>
			</section>

			<!-- Tabs copied from the photo's structure (not its design) -->
			<section class="prof-tabs" aria-label="Profile sections">
				<div class="tabbar" role="tablist" aria-label="Profile tabs">
					<button type="button" class="tab-link active" data-tab="earnings" role="tab" aria-selected="true" aria-controls="tab-earnings">Earnings</button>
					<button type="button" class="tab-link" data-tab="reviews" role="tab" aria-selected="false" aria-controls="tab-reviews">Reviews</button>
					<button type="button" class="tab-link" data-tab="about" role="tab" aria-selected="false" aria-controls="tab-about">About me</button>
				</div>

				<div class="tab-panels">
					<!-- Earnings -->
					<div id="tab-earnings" class="tab-panel" role="tabpanel">
						<article class="card" aria-labelledby="prefer-kasangga-title">
							<h4 id="prefer-kasangga-title">Become a preferred Kasangga</h4>
							<p class="muted">Unlock more quests and higher earnings by getting the preferred Kasangga badge.</p>
							<div class="wallet-actions">
								<a class="btn-chip" href="">Check eligibility</a>
							</div>
						</article>

						<article class="card wallet" aria-labelledby="wallet-title">
							<h4 id="wallet-title">Wallet</h4>
							<div class="wallet-row">
								<span class="wallet-cur">PHP</span>
								<strong class="wallet-amt">0.00</strong>
							</div>
							<div class="wallet-actions">
								<a class="btn-chip" href="insights.php" aria-label="Insights">
									<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 13l3 3 7-7"/></svg>
									Insights
								</a>
								<a class="btn-chip" href="./withdraw.php" aria-label="Withdraw">
									<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v14"/><path d="M5 10l7 7 7-7"/></svg>
									Withdraw
								</a>
							</div>
							<div class="sep"></div>
							<div class="wallet-note">
								<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
								Your wallet is encrypted and secure.
							</div>
						</article>

						<article class="card" aria-labelledby="txn-title">
							<h4 id="txn-title">Transactions</h4>
							<div class="txn-list">
								<!-- Empty state -->
								<div class="txn-item" aria-label="No transactions yet">
									<div class="txn-left">
										<div class="txn-title">No transactions yet</div>
										<div class="txn-sub">When you receive payments, they will appear here.</div>
									</div>
									<div class="txn-amt">—</div>
								</div>
							</div>
						</article>
					</div>

					<!-- Reviews -->
					<div id="tab-reviews" class="tab-panel" role="tabpanel" hidden>
						<h4 id="reviews-title" class="reviews-title">Reviews</h4>

						<!-- Role toggle now outside the box -->
						<div class="review-tabs" role="tablist" aria-label="Review role">
							<!-- add active to default -->
							<button type="button" id="revKasanggaBtn" class="review-tab active" role="tab" aria-selected="true" aria-controls="revKasanggaPanel">As a Kasangga</button>
							<button type="button" id="revCitizenBtn" class="review-tab" role="tab" aria-selected="false" aria-controls="revCitizenPanel">As a Citizen</button>
						</div>

						<!-- Panels outside the box -->
						<div id="revKasanggaPanel" class="review-panel" role="tabpanel">
							<p class="review-empty">You don’t have any reviews yet.</p>
							<!-- ...existing code... -->
						</div>
						<div id="revCitizenPanel" class="review-panel" role="tabpanel" hidden>
							<p class="review-empty">You don’t have any reviews yet.</p>
							<!-- ...existing code... -->
						</div>
					</div>

					<!-- About me -->
					<div id="tab-about" class="tab-panel" role="tabpanel" hidden>
						<article class="card" aria-labelledby="aboutme-title">
							<h4 id="aboutme-title">About me</h4>
							<p class="muted">Add details about yourself in Edit Profile to help clients know you better.</p>
							<div class="wallet-actions">
								<a class="btn-chip" href="./edit-profile.php">Edit Profile</a>
							</div>

							<!-- Verification -->
							<p class="about-verify"><a href="./verification.php">Verification</a></p>

							<!-- Joined and quest stats -->
							<ul class="about-list">
								<li class="about-row">
									<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
									<span>Joined <?php echo htmlspecialchars($joined_date, ENT_QUOTES, 'UTF-8'); ?></span>
								</li>
								<li class="about-row">
									<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18M7 7v10m10-10v10M5 17h14"/></svg>
									<span><?php echo (int)$kasangga_done; ?> Quest(s) completed as a Kasangga</span>
								</li>
								<li class="about-row">
									<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
									<span><?php echo (int)$citizen_done; ?> Quest(s) completed as a Citizen</span>
								</li>
							</ul>

							<!-- Skills -->
							<label class="about-label">Skills:</label>
							<div class="skill-chips">
								<?php foreach ((array)$skills as $sk): ?>
									<span class="skill-chip"><?php echo htmlspecialchars((string)$sk, ENT_QUOTES, 'UTF-8'); ?></span>
								<?php endforeach; ?>
							</div>

							<!-- Links -->
							<label class="about-label">Links:</label>
							<div class="about-links">
								<?php if ($portfolio_url): ?>
									<a href="<?php echo htmlspecialchars($portfolio_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Portfolio</a>
								<?php else: ?>
									<a href="./edit-profile.php">Add Portfolio Link</a>
								<?php endif; ?>
							</div>
						</article>
					</div>
				</div>
			</section>
		</div>
	</div>

	<!-- Right-side full-height sidebar navigation (smooth expand, settings at bottom) -->
	<nav class="dash-float-nav" id="dashNav">
		<!-- Brand section at top (clickable logo) -->
		<div class="nav-brand">
			<a href="./home-gawain.php" title="" style="display:block; text-decoration:none;">
				<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub logo">
				<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub">
			</a>
		</div>

		<div class="nav-main">
			<a href="./profile.php" class="active" aria-current="page" aria-label="Profile">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					 stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
				</svg>
				<span class="dash-text">Profile</span>
			</a>
			<a href="./post.php" aria-label="Post">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					 stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/>
				</svg>
				<span class="dash-text">Post</span>
			</a>
			<a href="./my-gawain.php" aria-label="My Gawain">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					 stroke-linecap="round" stroke-linejoin="round">
					<path d="M4 7h16M4 12h10M4 17h7"/>
				</svg>
				<span class="dash-text">My Gawain</span>
			</a>
			<a href="./chats.php" aria-label="Chats">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					 stroke-linecap="round" stroke-linejoin="round">
					<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
				</svg>
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
	// Tabs behavior
	(function(){
		const links = document.querySelectorAll('.tab-link');
		const panels = {
			earnings: document.getElementById('tab-earnings'),
			reviews: document.getElementById('tab-reviews'),
			about: document.getElementById('tab-about')
		};
		links.forEach(btn=>{
			btn.addEventListener('click', ()=>{
				const tab = btn.dataset.tab;
				links.forEach(b=>{ b.classList.toggle('active', b===btn); b.setAttribute('aria-selected', b===btn ? 'true':'false'); });
				Object.keys(panels).forEach(k=> panels[k].hidden = (k!==tab));
			});
		});
	})();

	// Reviews role toggle (Kasangga/Citizen)
	(function(){
		// support both new (Kasangga) and old (Hero) ids if present
		const kasBtn   = document.getElementById('revKasanggaBtn') || document.getElementById('revKasanggaBtn');
		const citBtn   = document.getElementById('revCitizenBtn');
		const kasPanel = document.getElementById('revKasanggaPanel') || document.getElementById('revKasanggaPanel');
		const citPanel = document.getElementById('revCitizenPanel');
		if (!kasBtn || !citBtn || !kasPanel || !citPanel) return;

		function setRole(role){
			const isKas = role === 'kasangga' || role === 'kasangga';
			kasBtn.classList.toggle('active', isKas);
			citBtn.classList.toggle('active', !isKas);
			kasBtn.setAttribute('aria-selected', isKas ? 'true' : 'false');
			citBtn.setAttribute('aria-selected', !isKas ? 'true' : 'false');
			kasPanel.hidden = !isKas;
			citPanel.hidden = isKas;
		}

		kasBtn.addEventListener('click', ()=> setRole('kasangga'));
		citBtn.addEventListener('click', ()=> setRole('citizen'));

		// initialize state based on current visibility (defaults to Kasangga)
		setRole(!kasPanel.hidden ? 'kasangga' : 'citizen');
	})();
	</script>
</body>
</html>
