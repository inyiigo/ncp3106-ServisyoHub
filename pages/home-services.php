<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Home • Services • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
</head>
<body>
	<?php
		session_start();
		// Capture mobile from POST if present and keep in session for future requests
		if (!empty($_POST['mobile'])) {
			$_SESSION['mobile'] = trim($_POST['mobile']);
		}
		$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : '';
		if (!$display) {
			$display = isset($_SESSION['mobile']) ? $_SESSION['mobile'] : '';
		}
		if (!$display) { $display = 'there'; }
		$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
	?>
	<div class="dash-topbar center">
		<div class="dash-brand">Servisyo Hub</div>
	</div>

	<div class="dash-overlay"></div>
	<div class="dash-shell">
		<main class="dash-content">
			<!-- Hero banner -->
			<section class="home-hero">
				<div class="hero-copy">
					<h2 class="hero-title">Layla</h2>
					<p class="hero-sub">She is a student in the Rtawahist Darshan, specializing in Theoretical Astrology.</p>
					<a href="#available-services" class="hero-link">Read More</a>
					<div class="hero-dots">
						<span class="hero-dot active"></span>
						<span class="hero-dot"></span>
						<span class="hero-dot"></span>
					</div>
				</div>
				<div class="hero-art" aria-hidden="true">
					<!-- Placeholder art area; we can swap for an image later -->
					<svg width="140" height="140" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:.8"><circle cx="12" cy="12" r="10"/></svg>
				</div>
			</section>

			<!-- Sections row: Artwork / Recent Search / Info Cards -->
			<div class="home-sections">
				<!-- Artwork -->
				<section>
					<h3 class="section-title">Artwork <span class="arrow">›</span></h3>
					<div class="art-scroller">
						<a class="art-card" href="#"><div class="art-thumb"></div><div class="art-credit">by @Sukocchi</div></a>
						<a class="art-card" href="#"><div class="art-thumb"></div><div class="art-credit">by @RenChain</div></a>
						<a class="art-card" href="#"><div class="art-thumb"></div><div class="art-credit">by @Someone</div></a>
					</div>
					<a class="home-cta-wide" href="#available-services">View More</a>
				</section>

				<!-- Recent Search -->
				<section>
					<h3 class="section-title">Recent Search</h3>
					<div class="search-list">
						<div class="search-pill"><svg class="pill-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.4-3.4"/></svg><div class="pill-text">Layla ascension material</div><div class="pill-avatar"></div></div>
						<div class="pill-wrap">
							<div class="search-pill dark"><svg class="pill-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.4-3.4"/></svg><div class="pill-text">Layla build</div><div class="pill-avatar"></div></div>
							<div class="pill-close">×</div>
						</div>
						<div class="search-pill"><svg class="pill-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.4-3.4"/></svg><div class="pill-text">Kalpalata lotus locations</div><div class="pill-avatar"></div></div>
						<div class="pill-wrap">
							<div class="search-pill dark"><svg class="pill-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.4-3.4"/></svg><div class="pill-text">Layla cosplay</div><div class="pill-avatar"></div></div>
							<div class="pill-close">×</div>
						</div>
						<div class="search-pill"><svg class="pill-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.4-3.4"/></svg><div class="pill-text">Layla Wallpaper</div><div class="pill-avatar"></div></div>
					</div>
				</section>

				<!-- Info cards -->
				<section>
					<div class="info-cards">
						<a class="info-card" href="#">
							<div class="info-ico">❄</div>
							<div>
								<div class="title" style="font-weight:800">Cryo character</div>
								<div class="info-meta">Cryo is one of the seven elements...</div>
							</div>
						</a>
						<a class="info-card" href="#">
							<div class="info-ico">🌼</div>
							<div>
								<div class="title" style="font-weight:800">Sumeru</div>
								<div class="info-meta">Sumeru is one of the seven regions of Teyvat...</div>
							</div>
						</a>
						<a class="info-card" href="#">
							<div class="info-ico">🏛</div>
							<div>
								<div class="title" style="font-weight:800">Akademiya</div>
								<div class="info-meta">The Sumeru Akademiya is Sumeru's main governing body...</div>
							</div>
						</a>
					</div>
				</section>
			</div>

			<!-- Keep existing services below as your actual content -->
			<section class="dash-svc" id="available-services">
				<h4>Available Services</h4>

						<div class="dash-cat">
							<div class="dash-cat-title"><span>Home Service</span></div>
										<div class="dash-svc-grid">
											<a class="dash-svc-card" href="./services/cleaning.php">
												<div class="info"><div class="title">Cleaning</div><div class="sub">Home and office cleaning</div></div>
												<div class="pic svc-cleaning"></div>
											</a>
											<a class="dash-svc-card" href="./services/aircon.php">
												<div class="info"><div class="title">Aircon</div><div class="sub">Cleaning & maintenance</div></div>
												<div class="pic svc-aircon"></div>
											</a>
											<a class="dash-svc-card" href="./services/upholstery.php">
												<div class="info"><div class="title">Upholstery</div><div class="sub">Deep clean sofas & more</div></div>
												<div class="pic svc-upholstery"></div>
											</a>
											<a class="dash-svc-card" href="./services/electrical-appliance.php">
												<div class="info"><div class="title">Electrical & Appliance</div><div class="sub">Wiring & appliance fix</div></div>
												<div class="pic svc-electrical-appliance"></div>
											</a>
											<a class="dash-svc-card" href="./services/plumbing-handyman.php">
												<div class="info"><div class="title">Plumbing & Handyman</div><div class="sub">Repairs & installations</div></div>
												<div class="pic svc-plumbing-handyman"></div>
											</a>
											<a class="dash-svc-card" href="./services/pest-control.php">
												<div class="info"><div class="title">Pest Control</div><div class="sub">Termites, roaches, more</div></div>
												<div class="pic svc-pest-control"></div>
											</a>
											<a class="dash-svc-card" href="./services/ironing.php">
												<div class="info"><div class="title">Ironing</div><div class="sub">Clothes ironing service</div></div>
												<div class="pic svc-ironing"></div>
											</a>
										</div>
						</div>

						<div class="dash-cat">
							<div class="dash-cat-title"><span>Personal Care</span></div>
										<div class="dash-svc-grid">
											<a class="dash-svc-card" href="./services/beauty.php"><div class="info"><div class="title">Beauty</div><div class="sub">Skin & nails</div></div><div class="pic svc-beauty"></div></a>
											<a class="dash-svc-card" href="./services/massage.php"><div class="info"><div class="title">Massage</div><div class="sub">Relaxation & therapy</div></div><div class="pic svc-massage"></div></a>
											<a class="dash-svc-card" href="./services/hair-care.php"><div class="info"><div class="title">Hair Care</div><div class="sub">Cut, style, color</div></div><div class="pic svc-hair-care"></div></a>
											<a class="dash-svc-card" href="./services/wax.php"><div class="info"><div class="title">Wax</div><div class="sub">Body waxing</div></div><div class="pic svc-wax"></div></a>
										</div>
						</div>

						<div class="dash-cat">
							<div class="dash-cat-title"><span>Automotive Service</span></div>
										<div class="dash-svc-grid">
											<a class="dash-svc-card" href="./services/car-spa.php"><div class="info"><div class="title">Car Spa</div><div class="sub">Wash, wax, detail</div></div><div class="pic svc-car-spa"></div></a>
										</div>
						</div>

						<div class="dash-cat">
							<div class="dash-cat-title"><span>Pet Service</span></div>
										<div class="dash-svc-grid">
											<a class="dash-svc-card" href="./services/pet-care.php"><div class="info"><div class="title">Pet Care</div><div class="sub">Grooming & sitting</div></div><div class="pic svc-pet-care"></div></a>
										</div>
						</div>
					</section>
		</main>

		<aside class="dash-aside">
			<nav class="dash-nav">
				<h3>Navigation</h3>
				<a href="./home-services.php" class="active">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
					Home
				</a>
				<a href="./my-services.php">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
					My Services <span class="dash-badge">0</span>
				</a>
				<a href="./profile.php">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
					Profile
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
		<a href="./profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
</body>
</html>

