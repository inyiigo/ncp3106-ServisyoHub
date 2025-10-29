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
.bottom-box {
	position: fixed;
	right: 20px;
	bottom: 20px;
	z-index: 999;
	background: transparent;
	border: none;
	padding: 0;
	border-radius: 0;
	box-shadow: none;
	opacity: 0;
	animation: fadeIn 1s ease-in-out 0.3s forwards;
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

/* centered floating bottom navigation */
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

	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="ServisyoHub" class="dash-brand-logo" onerror="this.style.display='none'">
		</div>
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

	<!-- bottom back button -->
	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="./jobs-profile.php" class="back-box" title="Back to profile">← Back to profile</a>
	</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./home-jobs.php" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
		</a>
		<a href="./my-jobs.php" aria-label="My Jobs">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Jobs</span>
		</a>
		<a href="./jobs-profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>

</body>
</html>
