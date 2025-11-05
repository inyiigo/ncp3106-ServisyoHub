<?php
session_start();
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'there');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>My Gawain • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
	<style>
		/* Match bottom nav behavior from home-gawain.php and center at bottom */
		.dash-bottom-nav {
			position: fixed;
			left: 50%;
			right: auto;
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

		/* page override: white background */
		body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

		/* Blue bottom border on topbar */
		.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }

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

		/* Ensure main content is above background */
		.dash-shell {
			position: relative;
			z-index: 1;
		}

		/* Replace old tooltip nav with profile.php sidebar nav */
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
			padding: 12px 8px 8px 8px;
			border-right: 0;
			background: rgba(255,255,255,.95);
			backdrop-filter: saturate(1.15) blur(12px);
			border-top-left-radius: 16px;
			border-bottom-left-radius: 16px;
			border-top-right-radius: 0;
			border-bottom-right-radius: 0;
			box-shadow: 0 8px 24px rgba(0,120,166,.28), 0 0 0 1px rgba(255,255,255,.4) inset;
			transition: width .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s ease;
			width: 56px;
			overflow: hidden;
		}
		.dash-float-nav:hover {
			width: 200px;
			box-shadow: 0 12px 32px rgba(0,120,166,.35), 0 0 0 1px rgba(255,255,255,.5) inset;
		}

		/* Brand (top-centered) with hover swap: job_logo -> bluefont */
		.dash-float-nav .nav-brand {
			display: grid;
			place-items: center;
			position: relative;
			height: 56px;
			padding: 6px 0;
		}
		.dash-float-nav .nav-brand a {
			display: block; width: 100%; height: 100%; position: relative; text-decoration: none;
		}
		.dash-float-nav .nav-brand img {
			position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
			display: block; object-fit: contain; pointer-events: none;
			transition: opacity .25s ease, transform .25s ease, width .3s ease;
		}
		.dash-float-nav .nav-brand .logo-small { width: 26px; height: auto; opacity: 1; }
		.dash-float-nav .nav-brand .logo-wide { width: 160px; height: auto; opacity: 0; }
		.dash-float-nav:hover .nav-brand .logo-small { opacity: 0; transform: translate(-50%, -50%) scale(.96); }
		.dash-float-nav:hover .nav-brand .logo-wide { opacity: 1; transform: translate(-50%, -50%) scale(1); }

		/* Groups */
		.dash-float-nav > .nav-main { display: grid; gap: 8px; align-content: start; }
		.dash-float-nav > .nav-settings { margin-top: auto; display: grid; gap: 8px; }

		/* Links and icons */
		.dash-float-nav a {
			position: relative;
			width: 40px; height: 40px;
			display: grid; grid-template-columns: 40px 1fr;
			place-items: center; align-items: center;
			border-radius: 12px; color: #0f172a; text-decoration: none;
			transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4,0,0.2,1);
			outline: none; white-space: nowrap;
		}
		.dash-float-nav:hover a { width: 184px; }
		.dash-float-nav a:hover:not(.active) {
			background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
			transform: scale(1.05);
		}
		.dash-float-nav a:focus-visible { box-shadow: 0 0 0 3px rgba(0,120,166,.3); }
		.dash-float-nav a.active {
			background: linear-gradient(135deg, #0078a6 0%, #006a94 100%);
			color: #fff; box-shadow: 0 6px 18px rgba(0,120,166,.4);
		}
		.dash-float-nav .dash-icon {
			width: 18px; height: 18px; justify-self: center; object-fit: contain; transition: transform .2s ease;
		}
		.dash-float-nav a:hover .dash-icon { transform: scale(1.1); }
		/* Browse uses the logo image and no text */
		.dash-float-nav a .dash-text {
			opacity: 0; transform: translateX(-10px);
			transition: opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s;
			font-weight: 800; font-size: .85rem; color: inherit; justify-self: start; padding-left: 8px;
		}
		.dash-float-nav:hover a .dash-text { opacity: 1; transform: translateX(0); }

		/* Remove top bar on this page */
		.dash-topbar { display: none !important; }

		/* Sidebar nav: match settings.php colors and remove border/inset line */
		.dash-float-nav {
			background: #2596be !important;
			border: none !important;
			box-shadow: 0 8px 24px rgba(0,0,0,.24) !important; /* no inset line */
		}
		.dash-float-nav a { color: #fff !important; }
		.dash-float-nav a:hover:not(.active) {
			background: rgba(255,255,255,.15) !important;
			color: #fff !important;
		}
		/* Active state readable on blue background */
		.dash-float-nav a.active {
			background: rgba(255,255,255,.22) !important;
			color: #fff !important;
			box-shadow: 0 6px 18px rgba(0,0,0,.22) !important;
		}
		.dash-float-nav a.active::after { display: none !important; }

		/* Unbold all texts, except title and nav bar */
		:root { --fw-normal: 400; --fw-bold: 800; }
		body, body *:not(svg):not(path) { font-weight: var(--fw-normal) !important; }

		/* Keep page title bold */
		.mq-title { font-weight: var(--fw-bold) !important; }

		/* Keep right floating nav labels bold */
		.dash-float-nav a,
		.dash-float-nav a .dash-text,
		.dash-float-nav a span { font-weight: var(--fw-bold) !important; }

		/* Slightly shift content downward for breathing room */
		.dash-content { margin-top: clamp(12px, 4vh, 28px) !important; }
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-shell">
		<main class="dash-content">
			<header class="mq-header">
				<h1 class="mq-title">My Gawain</h1>
				<nav class="mq-tabs" role="tablist" aria-label="Gawain tabs">
					<?php $tab = isset($_GET['tab']) ? $_GET['tab'] : 'offered'; ?>
					<a class="mq-tab <?php echo $tab==='offered'?'active':''; ?>" href="?tab=offered" role="tab" aria-selected="<?php echo $tab==='offered'?'true':'false'; ?>">Offered</a>
					<a class="mq-tab <?php echo $tab==='posted'?'active':''; ?>" href="?tab=posted" role="tab" aria-selected="<?php echo $tab==='posted'?'true':'false'; ?>">Posted</a>
				</nav>
				<div class="mq-filter-row">
					<button class="mq-filter" id="mqFilterBtn" type="button" aria-haspopup="dialog" aria-controls="mqFilterModal" aria-expanded="false">Filter: <strong id="mqFilterLabel">All</strong></button>
				</div>
			</header>

			<section class="mq-empty" aria-label="Empty state">
				<p class="empty-text">Uh oh! You don't have any activity yet. Head over to the homepage to make offers to gawain that interest you.</p>
				<a class="btn mq-browse" href="./home-gawain.php">Browse gawain</a>
			</section>

			<!-- Filter Bottom Sheet Modal -->
			<div class="mq-filter-modal" id="mqFilterModal" role="dialog" aria-modal="true" aria-labelledby="mqFilterTitle" aria-hidden="true">
				<div class="mq-filter-backdrop" data-filter-close></div>
				<div class="mq-filter-sheet" role="document">
					<div class="mq-filter-header">
						<button class="mq-filter-reset" id="mqFilterReset" type="button">Reset</button>
						<h3 class="mq-filter-title" id="mqFilterTitle">Apply filters</h3>
						<button class="mq-filter-close" type="button" aria-label="Close" data-filter-close>&times;</button>
					</div>
					<form class="mq-filter-form" id="mqFilterForm">
						<label class="mq-filter-item"><input type="checkbox" name="status" value="pending"> <span>Pending offers</span></label>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="inprogress"> <span>In-progress</span></label>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="completed"> <span>Completed</span></label>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="cancelled"> <span>Cancellations</span></label>

						<button class="mq-filter-apply" id="mqFilterApply" type="button">Apply</button>
					</form>
				</div>
			</div>
		</main>
	</div>
	<!-- Floating right-side navigation (replaces bottom nav) -->
	<nav class="dash-float-nav" id="dashNav">
		<div class="nav-brand">
			<a href="./home-gawain.php" title="">
				<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub logo">
				<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub">
			</a>
		</div>

		<div class="nav-main">
			<a href="./profile.php" aria-label="Profile">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
				</svg>
				<span class="dash-text">Profile</span>
			</a>
			<a href="./post.php" aria-label="Post">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/>
				</svg>
				<span class="dash-text">Post</span>
			</a>
			<a href="./my-gawain.php" class="active" aria-current="page" aria-label="My Gawain">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M4 7h16M4 12h10M4 17h7"/>
				</svg>
				<span class="dash-text">My Gawain</span>
			</a>
			<a href="./chats.php" aria-label="Chats">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

</body>
</html>
