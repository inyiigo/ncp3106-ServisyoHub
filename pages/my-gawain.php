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

		/* Right-side floating nav (icon-only, show label tooltip on hover) */
		.dash-float-nav {
			position: fixed;
			top: 84px;              /* sits below the topbar */
			right: 16px;
			z-index: 1000;
			display: grid;
			gap: 10px;
			padding: 10px;
			border: 3px solid #0078a6;
			background: #fff;
			border-radius: 16px;
			box-shadow: 0 8px 20px rgba(0,120,166,.24);
		}
		.dash-float-nav a {
			position: relative;
			width: 44px;
			height: 44px;
			display: grid;
			place-items: center;
			border-radius: 12px;
			color: #0f172a;
			text-decoration: none;
			transition: background .15s ease, color .15s ease;
		}
		.dash-float-nav a.active { background: #0078a6; color: #fff; }
		.dash-float-nav a:hover:not(.active) { background: #f0f9ff; }
		.dash-float-nav .dash-icon { width: 20px; height: 20px; }

		/* Tooltip label that pops out on hover */
		.dash-float-nav a .dash-label {
			position: absolute;
			right: 100%;
			top: 50%;
			transform: translateY(-50%) scale(.95);
			transform-origin: right center;
			margin-right: 8px;
			background: #0f172a;
			color: #fff;
			font-weight: 800;
			font-size: .85rem;
			padding: 6px 10px;
			border-radius: 10px;
			white-space: nowrap;
			opacity: 0;
			pointer-events: none;
			box-shadow: 0 8px 20px rgba(2,6,23,.2);
			transition: opacity .15s ease, transform .15s ease;
		}
		.dash-float-nav a:hover .dash-label { opacity: 1; transform: translateY(-50%) scale(1); }
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-topbar center">
		<div class="dash-brand"><img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" /></div>
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

	<!-- Floating right-side navigation (replaces bottom nav) -->
	<nav class="dash-float-nav">
		<a href="./home-gawain.php" aria-label="Browse">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
			</svg>
			<span class="dash-label">Browse</span>
		</a>
		<a href="./post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/>
			</svg>
			<span class="dash-label">Post</span>
		</a>
		<a href="./my-gawain.php" class="active" aria-label="My Gawain">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M4 7h16M4 12h10M4 17h7"/>
			</svg>
			<span class="dash-label">My Gawain</span>
		</a>
		<a href="./chats.php" aria-label="Chats">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
			</svg>
			<span class="dash-label">Chats</span>
		</a>
		<a href="./profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
			</svg>
			<span class="dash-label">Profile</span>
		</a>
	</nav>
</body>
</html>