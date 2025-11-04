<?php
session_start();
$display = $_SESSION['display_name'] ?? ($_SESSION['mobile'] ?? 'Guest');
$mobile = $_SESSION['mobile'] ?? '';
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Settings • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<style>
		/* Hide any global top bar if present */
		.dash-topbar, .top-bar { display: none !important; }

		/* Center content both vertically and horizontally */
		.page-center {
			min-height: 100vh;
			display: grid;
			place-items: center;
			padding: 24px;
			box-sizing: border-box;
			background: #ffffff; /* match site white pages */
		}

		/* Optional: constrain the settings card width */
		.settings-card { width: 100%; max-width: 720px; }

		/* minimal local styles to match profile menu look */
		body.theme-profile-bg { background:#ffffff !important; background-attachment: initial !important; }
		.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }
		.bg-logo { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:25%; max-width:350px; opacity:.15; z-index:0; pointer-events:none; }
		.bg-logo img { width:100%; height:auto; display:block; }
		.profile-bg { position: relative; z-index: 1; background: transparent !important; }
		.prof-container { max-width:480px; margin: 0 auto; padding: 16px; }

		.prof-hero { border-radius:16px; box-shadow:0 10px 28px rgba(2,6,23,.18); background:#0078a6; color:#fff; display:flex; gap:12px; align-items:center; padding:14px; }
		.prof-avatar { width:42px; height:42px; border-radius:50%; background:#fff; color:#0078a6; display:grid; place-items:center; font-weight:800; }
		.prof-name, .prof-meta { margin:0; color:#fff; }
		.prof-meta { opacity:.9; font-size:.9rem; }

		.prof-menu { display:grid; gap:10px; padding:12px; border-radius:16px; background:#0078a6; box-shadow:0 10px 28px rgba(0,120,166,.24); margin-top:12px; }
		.prof-item {
			/* switch to grid so text is perfectly centered between equal icon/chevron columns */
			display: grid;
			grid-template-columns: 24px 1fr 24px;
			align-items: center;
			gap: 12px;
			padding:14px 16px; border-radius:14px; background:rgba(255,255,255,.15); color:#fff;
			border:2px solid rgba(255,255,255,.5); text-decoration:none; box-shadow:0 4px 12px rgba(0,0,0,.15);
			transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
		}
		.prof-item span { text-align: center; font-weight: 800; }
		.prof-ico { width:20px; height:20px; color:#fff; opacity:.95; }
		.prof-chev { width:18px; height:18px; color:#fff; opacity:.9; }
		.prof-item:hover { transform: translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,.2); background:#0078a6; color:#fff; }
		.prof-sep { display:none; }

		/* Right-side full-height sidebar nav (copied from profile.php) */
		.dash-float-nav {
			position: fixed; top: 0; right: 0; bottom: 0;
			z-index: 1000;
			display: flex !important; flex-direction: column; justify-content: flex-start;
			gap: 8px;
			padding: 12px 8px 8px 8px;
			border-right: 0;
			background: rgba(255,255,255,.95);
			backdrop-filter: saturate(1.15) blur(12px);
			border-top-left-radius: 16px; border-bottom-left-radius: 16px;
			border-top-right-radius: 0; border-bottom-right-radius: 0;
			box-shadow: 0 8px 24px rgba(0,120,166,.28), 0 0 0 1px rgba(255,255,255,.4) inset;
			transition: width .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s ease;
			width: 56px; overflow: hidden;
		}
		.dash-float-nav:hover { width: 200px; box-shadow: 0 12px 32px rgba(0,120,166,.35), 0 0 0 1px rgba(255,255,255,.5) inset; }

		/* Brand at top: job_logo by default, bluefont on hover */
		.dash-float-nav .nav-brand { display: grid; place-items: center; position: relative; height: 56px; padding: 6px 0; }
		.dash-float-nav .nav-brand a { display:block; width:100%; height:100%; position:relative; text-decoration:none; }
		.dash-float-nav .nav-brand img {
			position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
			display:block; object-fit:contain; pointer-events:none;
			transition: opacity .25s ease, transform .25s ease, width .3s ease;
		}
		.dash-float-nav .nav-brand .logo-small { width:26px; height:auto; opacity:1; }
		.dash-float-nav .nav-brand .logo-wide { width:160px; height:auto; opacity:0; }
		.dash-float-nav:hover .nav-brand .logo-small { opacity:0; transform:translate(-50%,-50%) scale(.96); }
		.dash-float-nav:hover .nav-brand .logo-wide { opacity:1; transform:translate(-50%,-50%) scale(1); }

		/* Groups */
		.dash-float-nav > .nav-main { display:grid; gap:8px; align-content:start; }
		.dash-float-nav > .nav-settings { margin-top:auto; display:grid; gap:8px; }

		/* Links and icons */
		.dash-float-nav a {
			position: relative;
			width: 40px; height: 40px;
			display: grid; grid-template-columns: 40px 1fr; place-items: center; align-items: center;
			border-radius: 12px; color: #0f172a; text-decoration: none; outline: none; white-space: nowrap;
			transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4,0,0.2,1);
		}
		.dash-float-nav:hover a { width: 184px; }
		.dash-float-nav a:hover:not(.active) { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); transform: scale(1.05); }
		.dash-float-nav a:focus-visible { box-shadow: 0 0 0 3px rgba(0,120,166,.3); }
		.dash-float-nav a.active { background: linear-gradient(135deg, #0078a6 0%, #006a94 100%); color:#fff; box-shadow: 0 6px 18px rgba(0,120,166,.4); }
		.dash-float-nav a.active::after {
			content: ""; position: absolute; left: -5px; width: 3px; height: 18px;
			background: linear-gradient(180deg, #0078a6 0%, #0078a6 100%); border-radius: 2px;
			box-shadow: 0 0 0 2px rgba(255,255,255,.9), 0 0 12px rgba(0,120,166,.6);
		}
		.dash-float-nav .dash-icon { width:18px; height:18px; justify-self:center; object-fit:contain; transition: transform .2s ease; }
		.dash-float-nav a:hover .dash-icon { transform: scale(1.1); }
		.dash-float-nav a .dash-text {
			opacity:0; transform:translateX(-10px);
			transition: opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s;
			font-weight:800; font-size:.85rem; color:inherit; justify-self:start; padding-left:8px;
		}
		.dash-float-nav:hover a .dash-text { opacity:1; transform:translateX(0); }

		/* Remove top bar on this page */
		.dash-topbar, .top-bar { display: none !important; }

		/* Sidebar nav: blue background + readable link colors */
		.dash-float-nav { background: #2596be !important; }
		.dash-float-nav a { color: #fff !important; }
		.dash-float-nav a:hover:not(.active) { background: rgba(255,255,255,.15) !important; }
		.dash-float-nav a.active { background: rgba(255,255,255,.2) !important; color: #fff !important; }

		/* Media Queries */
		@media (max-width:520px){
			.bottom-box{ left:12px; right:12px; bottom:14px; display:flex; justify-content:center; }
			.back-box{ width:100%; justify-content:center; }
		}
	</style>
</head>
<body class="theme-profile-bg">
	<div class="bg-logo"><img src="../assets/images/job_logo.png" alt="" /></div>

	<div class="profile-bg">
		<div class="prof-container">
			<nav class="prof-menu" aria-label="Profile options">
                <a class="prof-item" href="./manage-payment.php">
                    <svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 7H3V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2Zm0 0v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7m18 0l-9 6-9-6"/></svg>
                    <span>Manage Payment</span>
                    <svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
                <a class="prof-item" href="./my-gawain.php">
                    <svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18v4H3zM3 10h18v10H3z"/></svg>
                    <span>Gawain History</span>
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
                <a class="prof-item" href="./about-us.php">
                    <svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20v-6m0-4V4m0 6h.01M4 12a8 8 0 1 1 16 0 8 8 0 0 1-16 0Z"/></svg>
                    <span>About Us</span>
                    <svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
                <a class="prof-item" href="./terms-and-conditions.php">
                    <svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4h12v16H6zM8 8h8M8 12h8M8 16h5"/></svg>
                    <span>Terms and Conditions</span>
                    <svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
                <a class="prof-item" href="./profile.php?logout=1">
                    <svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4v4M14 10l5-5M9 7H7a4 4 0 0 0-4 4v5a4 4 0 0 0 4 4h5a4 4 0 0 0 4-4v-2"/></svg>
                    <span>Log out</span>
                    <svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
            </nav>
		</div>
	</div>

	<!-- Right-side full-height sidebar navigation -->
	<nav class="dash-float-nav" id="dashNav">
		<div class="nav-brand">
			<a href="./home-gawain.php" title="Go to home">
				<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub">
				<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub">
			</a>
		</div>

		<div class="nav-main">
            <a href="./profile.php" aria-label="Profile">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
				<span class="dash-text">Profile</span>
			</a>
			<a href="./post.php" aria-label="Post">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/>
				</svg>
				<span class="dash-text">Post</span>
			</a>
			<a href="./my-gawain.php" aria-label="My Gawain">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
				<span class="dash-text">My Gawain</span>
			</a>
			<a href="./chats.php" aria-label="Chats">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
				<span class="dash-text">Chats</span>
			</a>
		</div>

		<div class="nav-settings">
			<a href="./settings.php" class="active" aria-current="page" aria-label="Settings">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527c.45-.322 1.07-.26 1.45.12l.773.774c.38.38.442 1 .12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.322.45.26 1.07-.12 1.45l-.774.773c.38.38-1 .442-1.45.12l-.737-.527c-.35-.25-.806-.272-1.204-.107-.397.165-.71.505-.78.93l-.15.893c-.09.542-.56.94-1.109.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.893c-.071-.425-.384-.765-.781-.93-.398-.165-.854-.143-1.204.107l-.738.527c-.45.322-1.07.26-1.45-.12l-.773-.774c-.38-.38-.442-1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15C3.4 13.02 3 12.55 3 12V10.906c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.764-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35 .25 .806 .272 1.204 .107 .397 -.165 .71 -.505 .78 -.93l .149 -.894z"/>
					<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
				</svg>
				<span class="dash-text">Settings</span>
			</a>
		</div>
	</nav>

	<div class="page-center">
		<article class="form-card glass-card settings-card" role="main" aria-labelledby="settings-title">
			<h2 id="settings-title" class="no-margin">Settings</h2>
			<p class="note">Place your settings content here.</p>
			<!-- ...existing code... -->
		</article>
	</div>
</body>
</html>
