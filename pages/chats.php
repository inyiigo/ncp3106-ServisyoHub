<?php
session_start();
$display = $_SESSION['display_name'] ?? ($_SESSION['mobile'] ?? 'Guest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Chats • Servisyo Hub</title>
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

		/* Empty state container (maximize use of space) */
		.chat-empty {
			position: relative;
			z-index: 1;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			min-height: calc(100vh - 140px);
			padding: 24px;
			text-align: center;
			gap: 14px;
		}

		.empty-illustration {
			width: clamp(260px, 54vw, 420px);
			height: auto;
			margin: 4px 0 10px;
			opacity: 0.95;
		}

		.empty-title {
			font-size: clamp(1.5rem, 2.8vw, 2rem);
			font-weight: 900;
			color: #0f172a;
			margin: 4px 0 2px;
			letter-spacing: .2px;
		}

		.empty-text {
			font-size: clamp(1.02rem, 1.8vw, 1.1rem);
			line-height: 1.55;
			color: #475569;
			margin: 0 0 12px;
			max-width: 560px;
		}

		.empty-btn {
			display: inline-flex;
			align-items: center;
			gap: 10px;
			padding: 14px 28px;
			border-radius: 14px;
			background: #0078a6;
			color: #fff;
			font-weight: 900;
			font-size: 1.05rem;
			text-decoration: none;
			border: none;
			cursor: pointer;
			transition: transform .15s ease, box-shadow .15s ease;
			box-shadow: 0 10px 26px rgba(0,120,166,.28);
		}
		.empty-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 16px 34px rgba(0,120,166,.34);
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

		/* Tabs: As a Kasangga / As a Citizen */
		.chat-tabs {
			display: flex;
			gap: 12px;
			justify-content: center;
			margin: 16px auto 10px;
			max-width: 640px;
			padding: 0 14px;
		}
		.chat-tab {
			flex: 1;
			padding: 12px 18px;
			border-radius: 12px;
			border: 2px solid #e2e8f0;
			background: #fff;
			color: #0f172a;
			font-weight: 800;
			font-size: 1rem;
			cursor: pointer;
			transition: all .15s ease;
		}
		.chat-tab.active {
			background: #0078a6;
			color: #fff;
			border-color: #0078a6;
		}

		@media (max-width: 480px){
			.empty-illustration { width: min(72vw, 360px); }
			.empty-text { font-size: 1rem; max-width: 90vw; }
			.chat-tabs { max-width: 94vw; }
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
		<button type="button" class="chat-tab active" id="heroTab">As a Kasangga</button>
		<button type="button" class="chat-tab" id="citizenTab">As a Citizen</button>
	</div>

	<!-- Empty State -->
	<div class="chat-empty">
		<img src="../assets/images/empty-chat.svg" alt="No messages" class="empty-illustration" onerror="this.style.display='none'" />
		<h2 class="empty-title">It looks pretty empty here...</h2>
		<p class="empty-text">Why not help some citizens in need?</p>
		<a href="./home-gawain.php" class="empty-btn">
			Get Started
		</a>
	</div>

	<!-- Bottom Navigation -->
	<nav class="dash-bottom-nav">
		<a href="./post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span>Post</span>
		</a>
		<a href="./home-gawain.php" aria-label="Browse">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
			</svg>
			<span>Browse</span>
		</a>
		<a href="./my-gawain.php" aria-label="My Gawain">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Gawain</span>
        </a>
        <a href="./chats.php" class="active" aria-label="Chats">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			<span>Chats</span>
		</a>
		<a href="./profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>

	<script>
	// Tab switching behavior
	(function(){
		const heroTab = document.getElementById('heroTab');
		const citizenTab = document.getElementById('citizenTab');
		const emptyTitle = document.querySelector('.empty-title');
		const emptyText = document.querySelector('.empty-text');

		heroTab.addEventListener('click', function() {
			heroTab.classList.add('active');
			citizenTab.classList.remove('active');
			if(emptyTitle) emptyTitle.textContent = 'It looks pretty empty here...';
			if(emptyText) emptyText.textContent = 'Why not help some citizens in need?';
		});

		citizenTab.addEventListener('click', function() {
			citizenTab.classList.add('active');
			heroTab.classList.remove('active');
			if(emptyTitle) emptyTitle.textContent = 'No conversations yet';
			if(emptyText) emptyText.textContent = 'Post a gawain or browse offers to start a chat.';
		});
	})();
	</script>
</body>
</html>
