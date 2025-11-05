<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>About Us • ServisyoHub</title>
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* small page-scoped tweaks */
.container {
	max-width: 920px;
	margin: 0 auto;
	padding: 18px;
	display: grid;
	place-items: center;
	min-height: calc(100vh - 64px);
	box-sizing: border-box;
	position: relative;
	z-index: 1;

	/* Fade-in animation */
	opacity: 0;
	animation: fadeIn 0.8s ease-in-out forwards;
}

/* center the heading */
.header { margin-bottom: 18px; display:flex; align-items:center; gap:12px; justify-content:center; }
.header h1 { margin:0; text-align:center; width:100%; }

/* card styling */
.about-card { 
	padding: 22px; 
	border-radius:12px; 
	width:100%; 
	max-width:900px;
	background: #0078a6 !important;
	color: #fff;
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
	box-shadow: 0 8px 24px rgba(0,120,166,.24);
}
.section { margin-bottom: 18px; }
.section h3 { margin:0 0 8px; font-size:1.05rem; color: #fff; }
.section p { margin:0 0 8px; color: rgba(255,255,255,.85); line-height:1.45; }
.section p strong { color: #fff; }
.section p a { color: #fff; text-decoration: underline; }
.small { font-size:0.92rem; color: rgba(255,255,255,.75); }

/* bottom back button */
@keyframes floatUp {
	0% { transform: translateY(0); }
	50% { transform: translateY(-6px); }
	100% { transform: translateY(0); }
}
.back-box {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 8px 12px;
	border-radius: 10px;
	background: #0078a6;
	color: #fff;
	text-decoration: none;
	font-weight: 700;
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
	transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease;
	box-shadow: 0 6px 18px rgba(0,120,166,.24);
}
.back-box:hover {
	transform: translateY(-4px) scale(1.02);
	box-shadow: 0 12px 28px rgba(0,120,166,.32);
	background: #006a94;
	border-color: color-mix(in srgb, #0078a6 60%, #0000);
}

/* Fade-in keyframes */
@keyframes fadeIn {
	from { opacity: 0; transform: translateY(10px); }
	to { opacity: 1; transform: translateY(0); }
}

/* page override: white background */
body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

/* Blue bottom border on topbar */
.dash-topbar, .top-bar { display: none !important; }
/* --- Floating nav styles from profile.php, right side --- */
.dash-float-nav {
	position: fixed;
	top: 0;
	right: 0;
	left: auto;
	bottom: 0;
	z-index: 1000;
	display: flex !important;
	flex-direction: column;
	justify-content: flex-start;
	gap: 8px;
	padding: 12px 8px 8px 8px;
	background: #2596be !important;
	backdrop-filter: saturate(1.15) blur(12px);
	border-top-left-radius: 16px;
	border-bottom-left-radius: 16px;
	border-top-right-radius: 0;
	border-bottom-right-radius: 0;
	box-shadow: 0 8px 24px rgba(0,0,0,.24) !important;
	transition: width .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s ease;
	width: 56px;
	overflow: hidden;
	border: none !important;
}
.dash-float-nav:hover {
	width: 200px;
	box-shadow: 0 12px 32px rgba(0,120,166,.35), 0 0 0 1px rgba(255,255,255,.5) inset !important;
}
.dash-float-nav .nav-brand {
	display: grid;
	place-items: center;
	position: relative;
	height: 56px;
	padding: 6px 0;
}
.dash-float-nav .nav-brand a {
	display: block; width: 100%; height: 100%; position: relative; text-decoration: none;
}
.dash-float-nav .nav-brand img {
	position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
	display: block; object-fit: contain; pointer-events: none;
	transition: opacity .25s ease, transform .25s ease, width .3s ease;
}
.dash-float-nav .nav-brand .logo-small { width: 26px; height: auto; opacity: 1; }
.dash-float-nav .nav-brand .logo-wide { width: 160px; height: auto; opacity: 0; }
.dash-float-nav:hover .nav-brand .logo-small { opacity: 0; transform: translate(-50%, -50%) scale(.96); }
.dash-float-nav:hover .nav-brand .logo-wide { opacity: 1; transform: translate(-50%, -50%) scale(1); }
.dash-float-nav > .nav-main { display: grid; gap: 8px; align-content: start; }
.dash-float-nav > .nav-settings { margin-top: auto; display: grid; gap: 8px; }
.dash-float-nav a {
	position: relative;
	width: 40px; height: 40px;
	display: grid; grid-template-columns: 40px 1fr;
	place-items: center; align-items: center;
	border-radius: 12px; color: #fff !important; text-decoration: none;
	transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4,0,0.2,1);
	outline: none; white-space: nowrap;
}
.dash-float-nav:hover a { width: 184px; }
.dash-float-nav a:hover:not(.active) {
	background: rgba(255,255,255,.15) !important;
	color: #fff !important;
	transform: scale(1.05);
}
.dash-float-nav a:focus-visible { box-shadow: 0 0 0 3px rgba(0,120,166,.3); }
.dash-float-nav a.active {
	background: rgba(255,255,255,.22) !important;
	color: #fff !important;
	box-shadow: 0 6px 18px rgba(0,0,0,.22) !important;
}
.dash-float-nav a.active::after { display: none !important; }
.dash-float-nav .dash-icon,
.dash-float-nav a > svg {
	width: 18px; height: 18px; display: block; line-height: 0;
	justify-self: center; align-self: center;
}
.dash-float-nav a .dash-text {
	opacity: 0; transform: translateX(-10px);
	transition: opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s;
	font-weight: 800; font-size: .85rem; color: inherit; justify-self: start; padding-left: 8px;
	align-self: center;
}
.dash-float-nav:hover a .dash-text { opacity: 1; transform: translateX(0); }
@media (max-width:520px) {
	.bottom-box { left: 12px; right: 12px; bottom: 14px; display:flex; justify-content:center; }
	.back-box { width:100%; justify-content:center; }
}
@media (min-width: 768px) {
	.dash-shell { margin-right: 64px; }
}
</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<main class="container">
		<article class="form-card glass-card about-card" role="main" aria-labelledby="about-title">
			<header class="header">
				<h1 id="about-title" style="margin:0;">About Us</h1>
			</header>

			<section class="section" aria-labelledby="who-we-are">
				<h3 id="who-we-are">Who We Are</h3>
				<p>ServisyoHub is an online platform designed to connect customers with trusted service providers in their local area. From home repairs to professional assistance, we make it easier to find, hire, and manage reliable services in one convenient place.</p>
			</section>

			<section class="section" aria-labelledby="mission">
				<h3 id="mission">Our Mission</h3>
				<p>Our mission is to empower both customers and providers through a transparent, efficient, and user-friendly system. We aim to make every service experience smooth, fair, and trustworthy.</p>
			</section>

			<section class="section" aria-labelledby="vision">
				<h3 id="vision">Our Vision</h3>
				<p>We envision a connected community where technology bridges the gap between people who need help and those who can provide it. ServisyoHub strives to become the go-to digital service marketplace in the Philippines.</p>
			</section>

			<section class="section" aria-labelledby="values">
				<h3 id="values">Our Core Values</h3>
				<p><strong>Trust:</strong> We value honesty and integrity in every transaction.</p>
				<p><strong>Quality:</strong> We ensure every service provider meets our community standards.</p>
				<p><strong>Innovation:</strong> We continuously improve our platform for a better user experience.</p>
				<p><strong>Community:</strong> We foster collaboration between users and providers to build long-lasting relationships.</p>
			</section>

			<section class="section" aria-labelledby="team">
				<h3 id="team">Our Team</h3>
				<p>ServisyoHub is powered by a passionate team of developers, designers, and community builders who are dedicated to improving local service accessibility and reliability.</p>
			</section>

			<section class="section" aria-labelledby="contact">
				<h3 id="contact">Contact Us</h3>
				<p class="small">Have questions or feedback? Reach out to us at <a href="mailto:support@servisyohub.example">support@servisyohub.example</a>.</p>
			</section>

			<footer class="small" style="margin-top:12px;">
				<p>Last updated: <?php echo date('Y-m-d'); ?></p>
			</footer>
		</article>
	</main>

	<nav class="dash-float-nav" id="dashNav">
		<div class="nav-brand">
			<a href="./home-gawain.php" title="">
				<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub logo">
				<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub">
			</a>
		</div>
		<div class="nav-main">
			<a href="./profile.php" aria-label="Profile">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
				</svg>
				<span class="dash-text">Profile</span>
			</a>
			<a href="./post.php" aria-label="Post">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/>
				</svg>
				<span class="dash-text">Post</span>
			</a>
			<a href="./my-gawain.php" aria-label="My Gawain">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M4 7h16M4 12h10M4 17h7"/>
				</svg>
				<span class="dash-text">My Gawain</span>
			</a>
			<a href="./chats.php" aria-label="Chats">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
				</svg>
				<span class="dash-text">Chats</span>
			</a>
		</div>
		<div class="nav-settings">
			<a href="./about-us.php" class="active" aria-current="page" aria-label="About Us">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
				</svg>
				<span class="dash-text">About Us</span>
			</a>
			<a href="./terms-and-conditions.php" aria-label="Terms and Conditions">
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

</body>
</html>
