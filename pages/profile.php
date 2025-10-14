<?php
session_start();
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'Guest');
$mobile = isset($_SESSION['mobile']) ? $_SESSION['mobile'] : '';
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
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
		/* Page-specific styling to match the mockup */
		.profile-bg { background: radial-gradient(900px 450px at 50% -10%, rgba(255,255,255,.35) 0%, rgba(255,255,255,0) 60%), linear-gradient(180deg, #0ea5e9 0%, #06b6d4 55%, #22d3ee 100%); min-height: calc(100vh - 64px); padding: 18px; }
		.prof-container { width: 100%; max-width: 420px; margin: 0 auto; display: grid; gap: 14px; padding-bottom: calc(90px + env(safe-area-inset-bottom)); }
		.prof-hero { display: grid; grid-template-columns: 56px 1fr; gap: 12px; align-items: center; background: rgba(255,255,255,.35); border: 2px solid rgba(255,255,255,.6); backdrop-filter: blur(4px); color: #0f172a; border-radius: 16px; padding: 12px; box-shadow: 0 8px 20px rgba(2,6,23,.15); }
		.prof-avatar { width: 56px; height: 56px; border-radius: 50%; display: grid; place-items: center; font-weight: 800; color: #0f172a; background: #e0f2fe; border: 2px solid rgba(255,255,255,.8); }
		.prof-name { margin: 0; font-weight: 800; font-size: 1.02rem; }
		.prof-meta { margin: 2px 0 0; color: #0f172a; opacity: .9; font-size: .92rem; }
		.prof-edit { display: inline-block; margin-top: 2px; color: #0f172a; opacity: .9; font-weight: 700; text-decoration: none; font-size: .9rem; }

		.prof-menu { background: rgba(255,255,255,.35); border: 2px solid rgba(255,255,255,.6); border-radius: 16px; box-shadow: 0 8px 20px rgba(2,6,23,.15); backdrop-filter: blur(4px); padding: 10px; }
		.prof-item { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 12px; color: #0f172a; text-decoration: none; font-weight: 700; }
		.prof-item:hover { background: rgba(255,255,255,.5); }
		.prof-ico { width: 20px; height: 20px; color: #0f172a; opacity: .9; }
		.prof-sep { height: 1px; background: rgba(255,255,255,.6); margin: 4px 0; border-radius: 999px; }
	</style>
	</head>
<body>
	<div class="dash-topbar center">
		<div class="dash-brand">Servisyo Hub</div>
	</div>

	<div class="profile-bg">
		<div class="prof-container">
			<!-- Profile card -->
			<section class="prof-hero" aria-label="Account">
				<div class="prof-avatar" aria-hidden="true"><?php echo htmlspecialchars($avatar); ?></div>
				<div>
					<p class="prof-name"><?php echo htmlspecialchars($display); ?></p>
					<?php if ($mobile): ?><p class="prof-meta"><?php echo htmlspecialchars($mobile); ?></p><?php endif; ?>
					<a class="prof-edit" href="#">Edit Profile</a>
				</div>
			</section>

			<!-- Menu list -->
			<nav class="prof-menu" aria-label="Profile options">
				<a class="prof-item" href="#">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 7H3V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2Zm0 0v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7m18 0l-9 6-9-6"/></svg>
					<span>Manage Payment</span>
				</a>
				<div class="prof-sep"></div>
				<a class="prof-item" href="./my-services.php">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18v4H3zM3 10h18v10H3z"/></svg>
					<span>Service History</span>
				</a>
				<div class="prof-sep"></div>
				<a class="prof-item" href="#">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
					<span>Location</span>
				</a>
				<div class="prof-sep"></div>
				<a class="prof-item" href="#">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-6-4.35-6-9a6 6 0 1 1 12 0c0 4.65-6 9-6 9Z"/></svg>
					<span>Favorite Pros</span>
				</a>
				<div class="prof-sep"></div>
				<a class="prof-item" href="#">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20v-6m0-4V4m0 6h.01M4 12a8 8 0 1 1 16 0 8 8 0 0 1-16 0Z"/></svg>
					<span>About Us</span>
				</a>
				<div class="prof-sep"></div>
				<a class="prof-item" href="#">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4h12v16H6zM8 8h8M8 12h8M8 16h5"/></svg>
					<span>Terms and Conditions</span>
				</a>
				<div class="prof-sep"></div>
				<a class="prof-item" href="#">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4v4M14 10l5-5M9 7H7a4 4 0 0 0-4 4v5a4 4 0 0 0 4 4h5a4 4 0 0 0 4-4v-2"/></svg>
					<span>Log out</span>
				</a>
			</nav>
		</div>
	</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./home-services.php" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
		</a>
		<a href="./my-services.php" aria-label="My Services">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Services</span>
		</a>
		<a href="./profile.php" class="active" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
</body>
</html>

