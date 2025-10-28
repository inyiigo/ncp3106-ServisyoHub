<?php
// Start output buffering (prevents "headers already sent" warnings)
ob_start();

// Start session safely before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Capture mobile from POST if present and keep in session for future requests
if (!empty($_POST['mobile'])) {
    $_SESSION['mobile'] = trim($_POST['mobile']);
}

// Determine display name
$display = $_SESSION['display_name'] ?? $_SESSION['mobile'] ?? 'there';

// Create avatar initial
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));

// End buffering (send output)
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Home • Services • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
	<style>
		/* Side nav: compact by default, expand on hover */
		.dash-aside {
			width: 64px;                    /* compact */
			transition: width 200ms ease, box-shadow 180ms ease;
			overflow: hidden;               /* hide labels when compact */
		}
		.dash-aside:hover {
			width: 240px;                   /* expand */
			box-shadow: 0 12px 28px rgba(2,6,23,.12);
		}
		/* Keep nav items on one line and hide overflow when compact */
		.dash-aside .dash-nav a {
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.dash-aside .dash-nav .dash-icon {
			width: 20px; height: 20px; flex: 0 0 20px;
		}

		/* Bottom nav: centered at the bottom */
		.dash-bottom-nav {
			position: fixed;
			left: 50%;
			bottom: 16px;
			transform: translateX(-50%) scale(0.92); /* keep existing scale */
			transform-origin: bottom center;
			right: auto;
			margin: 0;
			width: max-content; /* shrink to content so centering is precise */
			transition: transform 180ms ease, box-shadow 180ms ease;
			z-index: 999; /* ensure above content */
		}
		.dash-bottom-nav:hover {
			transform: translateX(-50%) scale(1);
			box-shadow: 0 12px 28px rgba(2,6,23,.12);
		}

		@media (max-width:520px) {
			.dash-bottom-nav {
				bottom: 12px;
				transform: translateX(-50%); /* no scale on very small screens */
			}
		}

		/* Center the main content area */
		.dash-content { max-width: 1100px; margin: 0 auto; padding: 0 16px; }

		/* Center hero text */
		.home-hero { text-align: center; }

		/* Center category titles and grids */
		.home-sections .dash-cat { text-align: center; }
		.dash-cat-title { display: flex; justify-content: center; }
		.dash-svc-grid { justify-content: center; }
	</style>
</head>
<body class="theme-profile-bg">
	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" />
		</div>
	</div>

	<div class="dash-overlay"></div>
	<div class="dash-shell">
		<main class="dash-content">
			<!-- Hero banner -->
			<section class="home-hero">
				<p class="hero-tagline">Where skilled hands meet local demand.</p>
			</section>

			<!-- Category sections -->
			<div class="home-sections" id="available-services">
				<!-- Home Service -->
				<div class="dash-cat">
					<div class="dash-cat-title"><span>Home Service</span></div>
					<div class="dash-svc-grid">
						<a class="dash-svc-card glass-card" href="./services/cleaning.php">
							<div class="info"><div class="title">Cleaning</div><div class="sub">Home and office cleaning</div></div>
							<div class="pic svc-cleaning"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/aircon.php">
							<div class="info"><div class="title">Aircon</div><div class="sub">Cleaning & maintenance</div></div>
							<div class="pic svc-aircon"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/upholstery.php">
							<div class="info"><div class="title">Upholstery</div><div class="sub">Deep clean sofas & more</div></div>
							<div class="pic svc-upholstery"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/electrical-appliance.php">
							<div class="info"><div class="title">Electrical & Appliance</div><div class="sub">Wiring & appliance fix</div></div>
							<div class="pic svc-electrical-appliance"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/plumbing-handyman.php">
							<div class="info"><div class="title">Plumbing & Handyman</div><div class="sub">Repairs & installations</div></div>
							<div class="pic svc-plumbing-handyman"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/pest-control.php">
							<div class="info"><div class="title">Pest Control</div><div class="sub">Termites, roaches, more</div></div>
							<div class="pic svc-pest-control"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/ironing.php">
							<div class="info"><div class="title">Ironing</div><div class="sub">Clothes ironing service</div></div>
							<div class="pic svc-ironing"></div>
						</a>
					</div>
				</div>

				<!-- Personal Care -->
				<div class="dash-cat">
					<div class="dash-cat-title"><span>Personal Care</span></div>
					<div class="dash-svc-grid">
						<a class="dash-svc-card glass-card" href="./services/beauty.php"><div class="info"><div class="title">Beauty</div><div class="sub">Skin & nails</div></div><div class="pic svc-beauty"></div></a>
						<a class="dash-svc-card glass-card" href="./services/massage.php"><div class="info"><div class="title">Massage</div><div class="sub">Relax
				<a href="./home-services.php" class="active" aria-label="Home">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
					<span>Home</span>
				</a>
				<a href="./my-services.php" aria-label="My Services">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
					<span>My Services</span>
				</a>
				<a href="./clients-profile.php" aria-label="Profile">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
					<span>Profile</span>
				</a>
			</nav>
		</aside>
	</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./home-services.php" class="active" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
		</a>
		<a href="./my-services.php" aria-label="My Services">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Services</span>
		</a>
		<a href="./clients-profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
</body>
</html>
