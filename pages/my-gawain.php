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
		.dash-float-nav a.active::after {
			content: ""; position: absolute; left: -5px; width: 3px; height: 18px;
			background: linear-gradient(180deg, #0078a6 0%, #0078a6 100%);
			border-radius: 2px; box-shadow: 0 0 0 2px rgba(255,255,255,.9), 0 0 12px rgba(0,120,166,.6);
		}
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-shell">
		<main class="dash-content">
			<div class="dash-tagline-wrap"><p class="dash-tagline">Where skilled hands meet local demand.</p></div>

			<!-- Browse CTA card placed above empty state -->
			<section class="dash-cards" aria-label="Browse gawain">
				<div class="dash-card blue">
					<div>
						<div class="dash-pill">Need to browse gawain?</div>
						<h3>Find a Provider</h3>
						<p>Explore verified gawain around you.</p>
					</div>
					<a href="./home-gawain.php" class="dash-pill">Browse</a>
				</div>
			</section>

			<div class="empty-wrap">
				<div class="empty-card">
					<p class="empty-title">No bookings yet</p>
					<p class="empty-text">When you book a gawain, it will appear here so you can track its progress.</p>
				</div>
			</div>
		</main>
	</div>

	<!-- Replace previous tooltip nav with profile.php sidebar nav -->
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
			<a href="./settings.php" aria-label="Settings">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527c.45-.322 1.07-.26 1.45.12l.773.774c.38.38.442 1 .12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.322.45.26 1.07-.12 1.45l-.774.773c-.38.38-1 .442-1.45.12l-.737-.527c-.35-.25-.806-.272-1.204-.107-.397.165-.71.505-.78.93l-.15.893c-.09.542-.56.94-1.109.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.893c-.071-.425-.384-.765-.781-.93-.398-.165-.854-.143-1.204.107l-.738.527c-.45.322-1.07.26-1.45-.12l-.773-.774c-.38-.38-.442-1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15C3.4 13.02 3 12.55 3 12V10.906c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.764-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35 .25 .806 .272 1.204 .107 .397 -.165 .71 -.505 .78 -.93l .149 -.894z"/>
					<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
				</svg>
				<span class="dash-text">Settings</span>
			</a>
		</div>
	</nav>
</body>
</html>
