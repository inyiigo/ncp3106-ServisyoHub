<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Terms &amp; Conditions • ServisyoHub</title>
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
.terms-card { 
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
.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }

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
@media (min-width: 768px) {
	.dash-shell { margin-right: 64px; }
}

/* page override: white background */
body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

/* Blue bottom border on topbar */
.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }

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

.dash-topbar, .top-bar { display: none !important; }

@media (max-width:520px) {
	.bottom-box { left: 12px; right: 12px; bottom: 14px; display:flex; justify-content:center; }
	.back-box { width:100%; justify-content:center; }
}
</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<main class="container">
		<article class="form-card glass-card terms-card" role="main" aria-labelledby="terms-title">
			<header class="header">
				<h1 id="terms-title" class="no-margin">Terms &amp; Conditions</h1>
			</header>

			<section class="section" aria-labelledby="intro">
				<h3 id="intro">Introduction</h3>
				<p>Welcome to ServisyoHub. These Terms &amp; Conditions govern your use of our website and services. By accessing or using ServisyoHub you agree to these terms. If you do not agree, please do not use our services.</p>
			</section>

			<section class="section" aria-labelledby="services">
				<h3 id="services">Our Services</h3>
				<p>We provide a platform to connect service providers and customers. Descriptions, availability, and pricing of services are provided for convenience and may change. We make reasonable efforts to keep information accurate but do not guarantee it.</p>
			</section>

			<section class="section" aria-labelledby="user-obligations">
				<h3 id="user-obligations">User obligations</h3>
				<p>When you create an account or submit information you confirm it is accurate and up-to-date. You are responsible for maintaining the confidentiality of your account credentials and for all activity under your account.</p>
			</section>

			<section class="section" aria-labelledby="payments">
				<h3 id="payments">Payments &amp; Fees</h3>
				<p>Payments for services are made between users and providers unless otherwise stated. Fees and payment terms are set on each transaction page. Refunds and cancellations follow the policy provided at the time of booking.</p>
			</section>

			<section class="section" aria-labelledby="content-ip">
				<h3 id="content-ip">Intellectual Property</h3>
				<p>All content, trademarks and design on ServisyoHub are owned by us or licensed to us. You may not reproduce or use our intellectual property without prior written consent.</p>
			</section>

			<section class="section" aria-labelledby="liability">
				<h3 id="liability">Limitation of Liability</h3>
				<p>To the maximum extent permitted by law, ServisyoHub and its affiliates are not liable for indirect, special, incidental or consequential damages arising from your use of the platform. Your use is at your own risk.</p>
			</section>

			<section class="section" aria-labelledby="privacy">
				<h3 id="privacy">Privacy &amp; Data</h3>
				<p>We process personal data in accordance with our Privacy Policy. By using the site you consent to such processing and to the use of cookies as described in the Privacy Policy.</p>
			</section>

			<section class="section" aria-labelledby="changes">
				<h3 id="changes">Changes to Terms</h3>
				<p>We may update these Terms from time to time. Material changes will be communicated reasonably. Continued use after changes constitutes acceptance of the new Terms.</p>
			</section>

			<section class="section" aria-labelledby="contact">
				<h3 id="contact">Contact</h3>
				<p class="small">If you have questions about these Terms, contact us at <a href="mailto:support@servisyohub.example">support@servisyohub.example</a>.</p>
			</section>

			<footer class="small margin-top-12">
				<p>Effective date: <?php echo date('Y-m-d'); ?></p>
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
			<a href="./about-us.php" aria-label="About Us">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
				</svg>
				<span class="dash-text">About Us</span>
			</a>
			<a href="./terms-and-conditions.php" class="active" aria-current="page" aria-label="Terms & Conditions">
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
