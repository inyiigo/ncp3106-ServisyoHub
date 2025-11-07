<?php
session_start();
$display = $_SESSION['display_name'] ?? ($_SESSION['mobile'] ?? 'Guest');
// tab for role views: kasangga (default) or citizen
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'kasangga';
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

		/* Remove top bar on this page */
		.dash-topbar { display: none !important; }
		body { padding-top: 0 !important; }

		/* Right-side floating nav (icon-only, expand on hover) */
		.dash-float-nav {
			position: fixed; top: 0; right: 0; bottom: 0;
			z-index: 1000;
			display: flex; flex-direction: column; justify-content: flex-start;
			gap: 8px;
			padding: 12px 8px 8px 8px;
			border-right: 0;
			border-top-left-radius: 16px; border-bottom-left-radius: 16px;
			background: #2596be !important;
			box-shadow: 0 8px 24px rgba(0,0,0,.24) !important;
			transition: width .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s ease;
			width: 56px;
			overflow: hidden;
		}
		.dash-float-nav:hover { width: 200px; box-shadow: 0 12px 32px rgba(0,120,166,.35), 0 0 0 1px rgba(255,255,255,.5) inset; }
		.dash-float-nav .nav-brand { display:grid; place-items:center; position:relative; height:56px; padding:6px 0; }
		.dash-float-nav .nav-brand a { display:block; width:100%; height:100%; position:relative; text-decoration:none; }
		.dash-float-nav .nav-brand img {
			position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
			display:block; object-fit:contain; pointer-events:none; transition: opacity .25s ease, transform .25s ease, width .3s ease;
		}
		.dash-float-nav .nav-brand .logo-small { width:26px; height:auto; opacity:1; }
		.dash-float-nav .nav-brand .logo-wide { width:160px; height:auto; opacity:0; }
		.dash-float-nav:hover .nav-brand .logo-small { opacity:0; transform:translate(-50%,-50%) scale(.96); }
		.dash-float-nav:hover .nav-brand .logo-wide { opacity:1; transform:translate(-50%,-50%) scale(1); }

		.dash-float-nav > .nav-main { display:grid; gap:8px; align-content:start; }
		.dash-float-nav > .nav-settings { margin-top:auto; display:grid; gap:8px; }

		.dash-float-nav a {
			position: relative; width: 40px; height: 40px;
			display:grid; grid-template-columns:40px 1fr; place-items:center;
			border-radius: 12px; color:#fff !important; text-decoration:none; outline: none; white-space: nowrap;
			transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4,0,0.2,1);
		}
		.dash-float-nav:hover a { width:184px; }
		.dash-float-nav a:hover:not(.active) { background: rgba(255,255,255,.15) !important; color:#fff !important; }
		.dash-float-nav a.active {
			background: rgba(255,255,255,.22) !important; color:#fff !important;
			box-shadow: 0 6px 18px rgba(0,0,0,.22) !important;
		}
		.dash-float-nav a.active::after { display: none !important; }
		
		.dash-icon { width:18px; height:18px; justify-self:center; object-fit:contain; transition: transform .2s ease; }
		.dash-float-nav a:hover .dash-icon { transform: scale(1.1); }
		.dash-text { opacity:0; transform:translateX(-10px); transition: opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s; font-weight:800; font-size:.85rem; color:inherit; justify-self:start; padding-left:8px; }
		.dash-float-nav:hover .dash-text { opacity:1; transform:translateX(0); }

		/* Background logo (behind UI), small size */
		.bg-logo {
			position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
			width: 135px; max-width: 135px; opacity: .30; z-index: 0; pointer-events: none;
		}
		.bg-logo img { width: 100%; height: auto; display: block; }

		/* Chat tabs */
		.chat-tabs {
			display: flex; gap: 12px; justify-content: center;
			max-width: 520px; margin: 72px auto 16px; /* pushed down further */
			position: relative; z-index: 1;
		}
		.chat-tab {
			flex: 1; padding: 14px 22px; border-radius: 14px;
			border: 2px solid #e2e8f0; background: #fff; color: #0f172a;
			font-weight: 700; cursor: pointer; transition: all .15s ease;
			font-size: 1.05rem; min-height: 52px; line-height: 1.1;
		}
		.chat-tab.active { background:#0078a6; color:#fff; border-color:#0078a6; box-shadow: 0 8px 22px rgba(0,120,166,.28); }

		/* Empty state */
		.chat-empty {
			position: relative; z-index: 1;
			display: flex; flex-direction: column; align-items: center; justify-content: center;
			min-height: calc(100vh - 240px);
			padding: 20px; text-align: center; transition: padding-top .2s ease;
		}
		.empty-illustration { width: 240px; height: auto; margin-bottom: 24px; opacity: .9; }
		.empty-title { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0 0 8px; }
		.empty-text { font-size: 1rem; color: #64748b; margin: 0 0 24px; max-width: 420px; }
		.empty-btn {
			display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 12px;
			background: #7cd4c4; color: #0b2c24; font-weight: 800; text-decoration: none; border: none; cursor: pointer;
			transition: transform .15s ease, box-shadow .15s ease; box-shadow: 0 8px 20px rgba(0,120,166,.24);
		}
		.empty-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,120,166,.32); }

		@media (max-width:420px){
			.chat-tabs { margin-top: 48px; }
		}
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo (defaults to Kasangga) -->
	<div class="bg-logo">
		<img id="bgLogo" src="../assets/images/kasangga.png" alt="" onerror="this.style.display='none'">
	</div>

	<!-- Page title -->
	<header class="mq-header mq-header-centered">
		<h1 class="mq-title">Chats</h1>
	</header>

	<!-- Role Tabs (matching My Gawain style) -->
	<nav class="mq-tabs" role="tablist" aria-label="Chat role tabs">
		<a class="mq-tab <?php echo $tab==='kasangga'?'active':''; ?>" href="?tab=kasangga" role="tab" aria-selected="<?php echo $tab==='kasangga'?'true':'false'; ?>">As a Kasangga</a>
		<a class="mq-tab <?php echo $tab==='citizen'?'active':''; ?>" href="?tab=citizen" role="tab" aria-selected="<?php echo $tab==='citizen'?'true':'false'; ?>">As a Citizen</a>
	</nav>

	<!-- Empty State -->
	<div class="chat-empty" id="chatEmpty">
		<img src="../assets/images/empty-chat.svg" alt="No messages" class="empty-illustration" onerror="this.style.display='none'" />
		<h2 class="empty-title">It looks pretty empty here...</h2>
		<p class="empty-text" id="emptyHelper"><?php echo $tab==='kasangga' ? 'Why not help some citizens in need?' : 'Why not post some gawain?'; ?></p>
		<a href="<?php echo $tab==='kasangga' ? './home-services.php' : './clients-post.php'; ?>" class="empty-btn" id="emptyCta"><?php echo $tab==='kasangga' ? 'Get Started' : 'Post a Gawain'; ?></a>
	</div>

	<!-- Right-side full-height sidebar navigation -->
	<nav class="dash-float-nav" id="dashNav">
		<div class="nav-brand">
			<a href="./home-gawain.php" title="">
				<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub logo">
				<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub">
			</a>
		</div>

		<div class="nav-main">
			<a href="./profile.php" aria-label="Profile">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
				<span class="dash-text">Profile</span>
			</a>
			<a href="./post.php" aria-label="Post">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
				<span class="dash-text">Post</span>
			</a>
			<a href="./my-gawain.php" aria-label="My Gawain">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
				<span class="dash-text">My Gawain</span>
			</a>
			<a href="./chats.php" class="active" aria-current="page" aria-label="Chats">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
				<span class="dash-text">Chats</span>
			</a>
		</div>

		<div class="nav-settings">
			<a href="./about-us.php" aria-label="About Us">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
				</svg>
				<span class="dash-text">About Us</span>
			</a>
			<a href="./terms-and-conditions.php" aria-label="Terms & Conditions">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M6 4h12v16H6z"/><path d="M8 8h8M8 12h8M8 16h5"/>
				</svg>
				<span class="dash-text">Terms & Conditions</span>
			</a>
			<a href="./profile.php?logout=1" aria-label="Log out">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 21V3"/>
				</svg>
				<span class="dash-text">Log out</span>
			</a>
		</div>
	</nav>

	<script>
	// Adjust empty state offset and ensure correct background logo based on server-side tab
	(function(){
		var bgLogo = document.getElementById('bgLogo');
		var emptyBox = document.getElementById('chatEmpty');
		function adjustEmptyOffset(){
			var logo = bgLogo; var box = emptyBox;
			if(!logo || !box) return;
			var lr = logo.getBoundingClientRect();
			var br = box.getBoundingClientRect();
			var extra = Math.max(0, Math.round(lr.bottom + 12 - br.top));
			box.style.paddingTop = extra + 'px';
		}
		// set logo src once on load (server already set content text)
		if(bgLogo){ bgLogo.src = '<?php echo $tab==='kasangga' ? "../assets/images/kasangga.png" : "../assets/images/citizen.png"; ?>'; }
		window.addEventListener('load', adjustEmptyOffset);
		window.addEventListener('resize', adjustEmptyOffset);
	})();
    
	// Client-side tab handling: react to tab clicks emitted by mq-tabs script
	(function(){
		function onTabChange(e){
			var tab = (e && e.detail && e.detail.tab) || new URLSearchParams(window.location.search).get('tab') || '<?php echo $tab; ?>';
			var bg = document.getElementById('bgLogo');
			var helper = document.getElementById('emptyHelper');
			var cta = document.getElementById('emptyCta');
			if(!bg || !helper || !cta) return;
			if(tab === 'citizen'){
				bg.src = '../assets/images/citizen.png';
				helper.textContent = 'Why not post some gawain?';
				cta.href = './clients-post.php';
				cta.textContent = 'Post a Gawain';
			} else {
				bg.src = '../assets/images/kasangga.png';
				helper.textContent = 'Why not help some citizens in need?';
				cta.href = './home-services.php';
				cta.textContent = 'Get Started';
			}
			// small layout tweak to ensure empty box sits under logo
			setTimeout(function(){
				var box = document.getElementById('chatEmpty');
				if(!box || !bg) return;
				var lr = bg.getBoundingClientRect();
				var br = box.getBoundingClientRect();
				var extra = Math.max(0, Math.round(lr.bottom + 12 - br.top));
				box.style.paddingTop = extra + 'px';
			}, 60);
		}
		window.addEventListener('tabchange', onTabChange);
		// also run once on load to ensure correct state
		window.addEventListener('load', function(){ onTabChange(); });
	})();
	</script>
</body>
</html>