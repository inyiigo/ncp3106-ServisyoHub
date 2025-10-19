<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Terms &amp; Conditions • ServisyoHub</title>
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* small page-scoped tweaks */
/* center the card in the remaining viewport below the topbar */
.container {
	max-width: 920px;
	margin: 0 auto;
	padding: 18px;
	display: grid;
	place-items: center;               /* horizontally & vertically center */
	min-height: calc(100vh - 64px);    /* leave space for topbar */
	box-sizing: border-box;

	/* Fade-in animation */
	opacity: 0;
	animation: fadeIn 0.8s ease-in-out forwards;
}

/* center the heading inside the card */
.header { margin-bottom: 18px; display:flex; align-items:center; gap:12px; justify-content:center; }
.header h1 { margin:0; text-align:center; width:100%; }
/* keep card sizing */
.terms-card { padding: 22px; border-radius:12px; width:100%; max-width:900px; }
.section { margin-bottom: 18px; }
.section h3 { margin:0 0 8px; font-size:1.05rem; }
.section p { margin:0 0 8px; color:var(--muted); line-height:1.45; }
.small { font-size:0.92rem; color:var(--muted); }

/* --- added bottom back button styles (same look/behavior as manage-payment) --- */
@keyframes floatUp {
	0%   { transform: translateY(0); }
	50%  { transform: translateY(-6px); }
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

	/* Fade-in animation for button */
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
		<article class="form-card glass-card terms-card" role="main" aria-labelledby="terms-title">
			<header class="header">
				<h1 id="terms-title" style="margin:0;">Terms &amp; Conditions</h1>
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

			<footer class="small" style="margin-top:12px;">
				<p>Effective date: <?php echo date('Y-m-d'); ?></p>
			</footer>
		</article>
	</main>

	<!-- add bottom back button -->
	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="profile.php" class="back-box" title="Back to profile">← Back to profile</a>
	</div>

</body>
</html>
