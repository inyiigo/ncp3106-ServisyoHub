<?php
session_start();
$display = $_SESSION['display_name'] ?? ($_SESSION['mobile'] ?? 'Guest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Chat • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<style>
		/* page override: white background */
		body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

		/* Blue bottom border on topbar */
		.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }

		/* Background logo */
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
		.bg-logo img { width: 100%; height: auto; display: block; }

		/* Empty state container */
		.chat-empty {
			position: relative;
			z-index: 1;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			min-height: calc(100vh - 180px);
			padding: 20px;
			text-align: center;
		}

		.empty-illustration {
			width: 240px;
			height: auto;
			margin-bottom: 24px;
			opacity: 0.9;
		}

		.empty-title {
			font-size: 1.4rem;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 8px;
		}

		.empty-text {
			font-size: 1rem;
			color: #64748b;
			margin: 0 0 24px;
			max-width: 400px;
		}

		.empty-btn {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 12px 24px;
			border-radius: 12px;
			background: #0078a6;
			color: #fff;
			font-weight: 800;
			text-decoration: none;
			border: none;
			cursor: pointer;
			transition: transform .15s ease, box-shadow .15s ease;
			box-shadow: 0 8px 20px rgba(0,120,166,.24);
		}
		.empty-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 12px 28px rgba(0,120,166,.32);
		}

		/* Bottom navigation */
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

		/* Tabs: As a Hero / As a Citizen */
		.chat-tabs {
			display: flex;
			gap: 10px;
			justify-content: center;
			margin: 20px auto;
			max-width: 400px;
		}
		.chat-tab {
			flex: 1;
			padding: 10px 20px;
			border-radius: 12px;
			border: 2px solid #e2e8f0;
			background: #fff;
			color: #0f172a;
			font-weight: 700;
			cursor: pointer;
			transition: all .15s ease;
		}
		.chat-tab.active {
			background: #0078a6;
			color: #fff;
			border-color: #0078a6;
		}

		/* Right-side floating nav (icon-only, show label on item hover) */
		.dash-float-nav {
			position: fixed;
			top: 84px;              /* below topbar */
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
		.dash-float-nav a.active {
			background: #0078a6;
			color: #fff;
		}
		.dash-float-nav a:hover:not(.active) {
			background: #f0f9ff;
		}
		.dash-float-nav .dash-icon { width: 20px; height: 20px; }

		/* Per-item label tooltip */
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
		.dash-float-nav a:hover .dash-label {
			opacity: 1;
			transform: translateY(-50%) scale(1);
		}
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" />
		</div>
	</div>

	<!-- Tabs -->
	<div class="chat-tabs">
		<button type="button" class="chat-tab active" id="heroTab">As a Hero</button>
		<button type="button" class="chat-tab" id="citizenTab">As a Citizen</button>
	</div>

	<!-- Empty State -->
	<div class="chat-empty">
		<img src="../assets/images/empty-chat.svg" alt="No messages" class="empty-illustration" onerror="this.style.display='none'" />
		<h2 class="empty-title">It looks pretty empty here...</h2>
		<p class="empty-text">Why not help some citizens in need?</p>
		<a href="./home-services.php" class="empty-btn">
			Get Started
		</a>
	</div>

	<!-- Floating right-side navigation (replaces bottom nav) -->
	<nav class="dash-float-nav">
		<a href="./home-gawain.php" aria-label="Browse">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
			<span class="dash-label">Browse</span>
		</a>
		<a href="./post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span class="dash-label">Post</span>
		</a>
		<a href="./my-gawain.php" aria-label="My Gawain">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span class="dash-label">My Gawain</span>
		</a>
        <a href="./chats.php" class="active" aria-label="Chats">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			<span class="dash-label">Chats</span>
		</a>
		<a href="./profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span class="dash-label">Profile</span>
		</a>
	</nav>

	<script>
	// Tab switching behavior
	(function(){
		const heroTab = document.getElementById('heroTab');
		const citizenTab = document.getElementById('citizenTab');

		heroTab.addEventListener('click', function() {
			heroTab.classList.add('active');
			citizenTab.classList.remove('active');
		});

		citizenTab.addEventListener('click', function() {
			citizenTab.classList.add('active');
			heroTab.classList.remove('active');
		});
	})();
	</script>
</body>
</html>
