<?php
session_start();
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
$title='Cleaning';
$subtitle='Home and office cleaning';
$parent='Home Service';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($title); ?> • ServisyoHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles.css">
<style>
/* page override: white background */
body.theme-profile-bg { background:#ffffff !important; background-attachment: initial !important; }
/* Blue bottom border on topbar */
.dash-topbar { border-bottom:3px solid #0078a6; position:relative; z-index:1; }
/* Background logo - transparent and behind UI */
.bg-logo { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:25%; max-width:350px; opacity:.15; z-index:0; pointer-events:none; }
.bg-logo img { width:100%; height:auto; display:block; }
/* Ensure main content is above background */
.dash-shell { position:relative; z-index:1; }
/* Center content width */
.dash-content { max-width: 980px; margin: 0 auto; padding: 0 16px; }
/* Service hero card */
.svc-hero.form-card.glass-card {
	background: #0078a6 !important; color: #fff;
	border-radius: 16px; padding: 22px 22px;
	box-shadow: 0 12px 32px rgba(0,120,166,.28);
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
	margin: 18px 0;
}
.svc-hero h1 { margin: 0 0 8px; color:#fff; }
.svc-hero .sub { color: rgba(255,255,255,.85); margin:0 0 14px; }
.svc-hero .actions { display:flex; gap:10px; flex-wrap:wrap; }
.btn-primary, .btn-ghost {
	display:inline-flex; align-items:center; justify-content:center; gap:8px;
	font-weight:800; text-decoration:none; cursor:pointer;
	border-radius:12px; padding:10px 14px;
	transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
}
.btn-primary { background:#fff; color:#0078a6; border:0; box-shadow:0 8px 20px rgba(255,255,255,.2); }
.btn-primary:hover { transform: translateY(-1px); box-shadow:0 12px 28px rgba(255,255,255,.3); }
.btn-ghost { background: rgba(255,255,255,.15); color:#fff; border:2px solid rgba(255,255,255,.5); }
.btn-ghost:hover { background:#0078a6; transform: translateY(-1px); box-shadow:0 8px 20px rgba(0,0,0,.2); }
/* centered floating bottom navigation */
.dash-bottom-nav {
	position: fixed; left: 50%; right: auto; bottom: 16px; z-index: 1000; width: max-content;
	transform: translateX(-50%) scale(0.92); transform-origin: bottom center;
	transition: transform 180ms ease, box-shadow 180ms ease; border: 3px solid #0078a6; background: transparent;
}
.dash-bottom-nav:hover { transform: translateX(-50%) scale(1); box-shadow: 0 12px 28px rgba(2,6,23,.12); }
</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo"><img src="../../assets/images/job_logo.png" alt=""></div>

	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../../assets/images/bluefont.png" alt="ServisyoHub" class="dash-brand-logo">
		</div>
	</div>

	<div class="dash-shell">
		<main class="dash-content">
			<section class="svc-hero form-card glass-card" aria-label="Service details">
				<h1><?php echo e($title); ?></h1>
				<p class="sub"><?php echo e($subtitle); ?></p>
				<div class="actions">
					<a class="btn-ghost" href="../home-gawain.php">← Back to Gawain</a>
					<a class="btn-primary" href="../clients-post.php">Post a request</a>
				</div>
			</section>
		</main>
	</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="../home-gawain.php" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
		</a>
		<a href="../my-gawain.php" aria-label="My Gawain">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Gawain</span>
		</a>
		<a href="../clients-profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
</body>
</html>