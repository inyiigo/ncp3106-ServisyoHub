<?php
session_start();
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'there');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>My Services • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
	<style>
		.empty-wrap { display:grid; place-items:center; min-height: 0; padding: 24px; }
		.empty-card { max-width: 720px; width: 100%; background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow: var(--shadow); padding: 28px 22px; text-align:center; }
		.empty-title { margin: 0 0 8px; font-size: clamp(18px,2.4vw,22px); font-weight: 800; }
		.empty-text { margin: 0; color: var(--muted); }
	</style>
	</head>
<body>
	<div class="dash-topbar center">
		<div class="dash-brand">Servisyo Hub</div>
	</div>
	<div class="dash-tagline-wrap"><p class="dash-tagline">Where skilled hands meet local demand.</p></div>

		<!-- Browse CTA card placed above empty state -->
		<section class="dash-cards" aria-label="Browse services">
			<div class="dash-card blue">
				<div>
					<div class="dash-pill">Need to browse services?</div>
					<h3>Find a Provider</h3>
					<p>Explore verified services around you.</p>
				</div>
				<a href="./home-services.php" class="dash-pill">Browse</a>
			</div>
		</section>

		<div class="empty-wrap">
		<div class="empty-card">
			<p class="empty-title">No bookings yet</p>
			<p class="empty-text">When you book a service, it will appear here so you can track its progress.</p>
			</div>
		</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./home-services.php" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
		</a>
		<a href="./my-services.php" class="active" aria-label="My Services">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Services</span>
		</a>
		<a href="./profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
</body>
</html>
