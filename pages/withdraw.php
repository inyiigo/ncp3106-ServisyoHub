<?php
session_start();
$balance = isset($_SESSION['wallet_balance']) ? (float)$_SESSION['wallet_balance'] : 0.00;
$email   = trim((string)($_SESSION['email'] ?? $_SESSION['user_email'] ?? ''));
function peso($v){ return 'PHP'.number_format((float)$v, 2); }
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Withdraw • ServisyoHub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">
	<style>
		/* ...shared site tokens... */
		body.theme-profile-bg { background:#ffffff !important; background-attachment: initial !important; }
		.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }
		.bg-logo { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:25%; max-width:350px; opacity:.15; z-index:0; pointer-events:none; }
		.bg-logo img { width:100%; height:auto; display:block; }

		/* Page container + blue card (same look used across pages) */
		.page-wrap { max-width: 980px; margin: 24px auto; padding: 18px; position: relative; z-index: 1; }
		.form-card.glass-card {
			background: #0078a6 !important;
			color: #fff;
			border-radius: 16px;
			padding: 16px 20px;
			box-shadow: 0 8px 24px rgba(0,120,166,.24);
			border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
		}
		.form-card.glass-card h2, .form-card.glass-card h3, .form-card.glass-card strong { color:#fff; }
		.form-card.glass-card .note { color: rgba(255,255,255,.85); }

		/* Wallet balance (soft pink card) */
		.w-balance {
			background: #ffffffff; border: 2px solid #dbeafe; color: #0f172a;
			border-radius: 14px; padding: 14px 16px; box-shadow: 0 6px 18px rgba(0,0,0,.06);
			max-width: 520px;
		}
		.w-bal-head { margin:0 0 8px; font-weight: 800; }
		.w-bal-note { margin:0 0 8px; color:#6b7280; font-size:.9rem; }
		.w-bal-row { display:grid; grid-template-columns: auto 1fr; gap:10px; align-items: baseline; }
		.w-cur { color:#6b7280; font-weight:700; }
		.w-amt { font-size: 2.2rem; font-weight: 900; color:#0f172a; line-height: 1; }

		/* Methods list (soft pink items) */
		.w-sec { margin-top: 16px; }
		.w-sec h3 { margin:0 0 6px; font-weight: 800; color: #fff; }
		.w-sec .w-sec-note { color:#e5e7eb; margin:0 0 10px; }

		.w-item {
			display:flex; align-items:center; justify-content: space-between; gap:12px;
			background: #ffffffff; border: 2px solid #dbeafe; color:#0f172a;
			border-radius: 14px; padding: 12px 14px; box-shadow: 0 6px 18px rgba(0,0,0,.06);
			text-decoration: none; max-width: 520px;
		}
		.w-left { display:flex; align-items:center; gap:10px; min-width:0; }
		.w-ico { width: 28px; height: 28px; border-radius: 8px; display:grid; place-items:center; color:#0078a6; background:#fff; border: 2px solid #0078a6; flex: 0 0 28px; }
		.w-main { display:grid; gap:2px; min-width:0; }
		.w-name { font-weight: 800; color:#0f172a; }
		.w-sub { color:#6b7280; font-size:.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
		.w-chev { width:18px; height:18px; color:#6b7280; flex:0 0 18px; }

		/* Bottom back button */
		.bottom-box { position: fixed; right: 20px; bottom: 20px; z-index: 999; background: transparent; border: none; padding: 0; box-shadow: none; }
		.back-box {
			display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:10px;
			background: #0078a6; color: #fff; text-decoration:none; font-weight:700;
			border:2px solid color-mix(in srgb, #0078a6 80%, #0000);
			transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease;
			box-shadow: 0 6px 18px rgba(0,120,166,.24);
		}
		.back-box:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 12px 28px rgba(0,120,166,.32); background: #006a94; border-color: color-mix(in srgb, #0078a6 60%, #0000); }
		@media (max-width:520px){ .bottom-box{ left:12px; right:12px; bottom:14px; display:flex; justify-content:center; } .back-box{ width:100%; justify-content:center; } }
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo"><img src="../assets/images/job_logo.png" alt=""></div>

	<!-- Topbar brand (same pattern as profile pages) -->
	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="ServisyoHub" class="dash-brand-logo" onerror="this.style.display='none'">
		</div>
	</div>

	<main class="page-wrap" role="main" aria-labelledby="w-title">
		<article class="form-card glass-card">
			<h2 id="w-title" class="no-margin">Withdraw</h2>
			<p class="note">Withdraw your funds using your linked methods.</p>

			<section aria-label="Wallet balance" style="margin-top:12px;">
				<div class="w-balance">
					<p class="w-bal-head">Wallet Balance</p>
					<p class="w-bal-note">Eligible Withdrawal Balance is <?php echo peso($balance); ?></p>
					<div class="w-bal-row">
						<span class="w-cur">PHP</span>
						<strong class="w-amt"><?php echo number_format($balance, 2); ?></strong>
					</div>
				</div>
			</section>

			<section class="w-sec" aria-label="Withdrawal Methods">
				<h3>Withdrawal Methods</h3>
				<p class="w-sec-note">Withdrawing of funds will have a processing time of 5 business days</p>

				<a class="w-item" href="./edit-profile.php" aria-label="Link email for withdrawals">
					<div class="w-left">
						<div class="w-ico" aria-hidden="true">✉</div>
						<div class="w-main">
							<div class="w-name">Email</div>
							<div class="w-sub"><?php echo $email !== '' ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : 'Not linked'; ?></div>
						</div>
					</div>
					<svg class="w-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
				</a>
			</section>
		</article>
	</main>

	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="./profile.php" class="back-box" title="Back to profile">← Back to profile</a>
	</div>
</body>
</html>
