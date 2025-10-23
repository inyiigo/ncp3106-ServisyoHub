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

	/* Fade-in animation */
	opacity: 0;
	animation: fadeIn 0.8s ease-in-out forwards;
}

/* center the heading */
.header { margin-bottom: 18px; display:flex; align-items:center; gap:12px; justify-content:center; }
.header h1 { margin:0; text-align:center; width:100%; }

/* card styling */
.about-card { padding: 22px; border-radius:12px; width:100%; max-width:900px; }
.section { margin-bottom: 18px; }
.section h3 { margin:0 0 8px; font-size:1.05rem; }
.section p { margin:0 0 8px; color:var(--muted); line-height:1.45; }
.small { font-size:0.92rem; color:var(--muted); }

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
	background: var(--card);
	color: var(--text);
	text-decoration: none;
	font-weight: 700;
	border: 1px solid var(--line);
	transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease, color 200ms ease;
	box-shadow: 0 6px 18px rgba(2,6,23,0.06);
}
.back-box:hover {
	transform: translateY(-4px) scale(1.02);
	box-shadow: 0 12px 28px rgba(2,6,23,0.12);
	background: var(--pal-4);
	color: #fff;
	border-color: color-mix(in srgb, var(--pal-4) 60%, #0000);
}

/* Fade-in keyframes */
@keyframes fadeIn {
	from { opacity: 0; transform: translateY(10px); }
	to { opacity: 1; transform: translateY(0); }
}

@media (max-width:520px) {
	.bottom-box { left: 12px; right: 12px; bottom: 14px; display:flex; justify-content:center; }
	.back-box { width:100%; justify-content:center; }
}
</style>
</head>
<body class="theme-profile-bg">
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
		<a href="./clients-profile.php" class="back-box" title="Back to profile">← Back to profile</a>
	</div>

</body>
</html>
