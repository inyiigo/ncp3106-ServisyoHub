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
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'Guest');
$mobile = isset($_SESSION['mobile']) ? $_SESSION['mobile'] : '';
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Client Profile • Servisyo Hub</title>
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

		.prof-hero {
			border-radius: 16px;
			box-shadow: 0 10px 28px rgba(2,6,23,.18);
			background: #0078a6;
			color: #fff;
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

	<div class="profile-bg">
		<div class="prof-container">
			<!-- Profile card -->
			<section class="prof-hero" aria-label="Account">
				<div class="prof-avatar" aria-hidden="true"><?php echo htmlspecialchars($avatar); ?></div>
				<div>
					<p class="prof-name"><?php echo htmlspecialchars($display); ?></p>
					<?php if ($mobile): ?><p class="prof-meta"><?php echo htmlspecialchars($mobile); ?></p><?php endif; ?>
					<a class="prof-edit" href="./edit-profile.php">Edit Profile</a>
				</div>
			</section>

			<!-- Menu list -->
			<nav class="prof-menu" aria-label="Profile options">
				<a class="prof-item" href="./manage-payment.php">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 7H3V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2Zm0 0v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7m18 0l-9 6-9-6"/></svg>
					<span>Manage Payment</span>
					<svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
				</a>
				<a class="prof-item" href="./my-services.php">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18v4H3zM3 10h18v10H3z"/></svg>
					<span>Service History</span>
					<svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
				</a>
				<a class="prof-item" href="./location.php">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
					<span>Location</span>
					<svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
				</a>
				<a class="prof-item" href="./favorite-pros.php">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-6-4.35-6-9a6 6 0 1 1 12 0c0 4.65-6 9-6 9Z"/></svg>
					<span>Favorite Pros</span>
					<svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
				</a>
				<a class="prof-item" href="./clients-about-us.php">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20v-6m0-4V4m0 6h.01M4 12a8 8 0 1 1 16 0 8 8 0 0 1-16 0Z"/></svg>
					<span>About Us</span>
					<svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
				</a>
				<a class="prof-item" href="./terms-and-conditions.php">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4h12v16H6zM8 8h8M8 12h8M8 16h5"/></svg>
					<span>Terms and Conditions</span>
					<svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
				</a>
				<a class="prof-item" href="./clients-profile.php?logout=1">
					<svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4v4M14 10l5-5M9 7H7a4 4 0 0 0-4 4v5a4 4 0 0 0 4 4h5a4 4 0 0 0 4-4v-2"/></svg>
					<span>Log out</span>
					<svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
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
		<a href="./clients-post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span>Post</span>
		</a>
		<a href="./my-services.php" aria-label="My Services">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Services</span>
		</a>
		<a href="./clients-profile.php" class="active" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
</body>
</html>
