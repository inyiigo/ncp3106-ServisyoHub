<?php
session_start();
$display = $_SESSION['display_name'] ?? $_SESSION['mobile'] ?? 'Guest';
$email   = $_SESSION['email'] ?? ($_SESSION['user_email'] ?? 'you@example.com');
$phone   = $_SESSION['mobile'] ?? '';
$emailVerified = isset($_SESSION['email_verified']) ? (bool)$_SESSION['email_verified'] : !empty($email);
$phoneVerified = isset($_SESSION['phone_verified']) ? (bool)$_SESSION['phone_verified'] : false;

/* Force empty and unverified state */
$email = '';
$phone = '';
$emailVerified = false;
$phoneVerified = false;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Verify • ServisyoHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* Page: copy overall look from profile.php */
body.theme-profile-bg { background:#ffffff !important; background-attachment: initial !important; }
.dash-topbar { display: none !important; border: 0 !important; position: static !important; z-index: auto !important; }
.bg-logo { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:25%; max-width:350px; opacity:.15; z-index:0; pointer-events:none; }
.bg-logo img { width:100%; height:auto; display:block; }

/* Container */
.page-wrap { max-width: 660px; margin: 22px auto; padding: 0 16px; position: relative; z-index: 1; }

/* Header area (simple, bold like profile headings) */
.v-head { text-align:center; margin-bottom: 14px; }
.v-head h1 { margin: 0 0 6px; font-weight: 900; color:#0f172a; }
.v-head .v-note { color:#64748b; }

/* Status list tiles (neutral cards with rounded corners) */
.v-list { display:grid; gap:10px; }
.v-item {
	display:flex; align-items:center; justify-content:space-between; gap:12px;
	background:#f3f4f6; border:2px solid #e5e7eb; border-radius:14px; padding:14px 16px;
	box-shadow: 0 4px 12px rgba(0,0,0,.06);
}
.v-left { display:flex; align-items:center; gap:12px; min-width:0; }
.v-ico {
	width:24px; height:24px; border-radius:50%;
	display:grid; place-items:center; color:#fff; font-weight:900; font-size:.9rem; flex:0 0 24px;
}
.v-ico.fail { background:#ef4444; }
.v-ico.ok { background:#22c55e; }
.v-title { font-weight:800; color:#0f172a; }
.v-sub { color:#6b7280; font-size:.9rem; }
.v-right { text-align:right; }
.v-link {
	color:#0078a6; text-decoration:none; font-weight:800; white-space:nowrap;
}
.v-link:hover { text-decoration:underline; }

/* Email row detail text truncation */
.v-detail { color:#6b7280; font-size:.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width: 280px; }

/* Optional top illustration spacing (kept minimal for consistency) */
.v-hero { margin: 6px auto 14px; display: grid; place-items: center; }
.v-hero img { width: 140px; height: auto; opacity: .95; }

/* Bottom back button (matches other pages) */
.bottom-box { position: fixed; right: 20px; bottom: 20px; z-index: 999; background: transparent; border: none; padding: 0; box-shadow: none; }
.back-box {
	display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:10px;
	background: #7cd4c4; color: #0b2c24; text-decoration:none;
	font-weight: var(--fw-normal) !important;
	border:2px solid color-mix(in srgb, #7cd4c4 80%, #0b2c24);
	transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease;
	box-shadow: 0 6px 18px rgba(124,212,196,.24);
}
.back-box:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 12px 28px rgba(124,212,196,.32); background: #7cd4c4; border-color: color-mix(in srgb, #7cd4c4 60%, #0b2c24); }
@media (max-width:520px){ .bottom-box{ left:12px; right:12px; bottom:14px; display:flex; justify-content:center; } .back-box{ width:100%; justify-content:center; } }
</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="">
	</div>

	<main class="page-wrap" role="main" aria-labelledby="v-title">
		<div class="v-head">
			<h1 id="v-title">Verify</h1>
			<p class="v-note">Complete the pending verifications to be fully verified.</p>
		</div>

		<!-- Optional illustration (can be removed if not needed) -->
		<div class="v-hero" aria-hidden="true">
			<img src="../assets/images/job_logo.png" alt="">
		</div>

		<section class="v-list" aria-label="Verification items">
			<!-- Phone -->
			<div class="v-item">
				<div class="v-left">
					<div class="v-ico <?php echo $phoneVerified ? 'ok' : 'fail'; ?>">
						<?php echo $phoneVerified ? '✓' : '✕'; ?>
					</div>
					<div>
						<div class="v-title"><?php echo $phoneVerified ? 'Phone Verified' : 'Phone Verification Pending'; ?></div>
						<div class="v-sub"><?php echo $phone ? htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') : 'No number on file'; ?></div>
					</div>
				</div>
				<div class="v-right">
					<?php if (!$phoneVerified): ?>
						<a class="v-link" href="./edit-profile.php">Add phone number</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Email -->
			<?php $emailText = $email !== '' ? $email : 'No email on file'; ?>
			<div class="v-item">
				<div class="v-left">
					<div class="v-ico <?php echo $emailVerified ? 'ok' : 'fail'; ?>">
						<?php echo $emailVerified ? '✓' : '✕'; ?>
					</div>
					<div>
						<div class="v-title"><?php echo $emailVerified ? 'Email Verified' : 'Email Verification Pending'; ?></div>
						<div class="v-detail" title="<?php echo htmlspecialchars($emailText, ENT_QUOTES, 'UTF-8'); ?>">
							<?php echo htmlspecialchars($emailText, ENT_QUOTES, 'UTF-8'); ?>
						</div>
					</div>
				</div>
				<div class="v-right">
					<?php if (!$emailVerified): ?>
						<a class="v-link" href="./edit-profile.php">Add email address</a>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</main>

	<!-- Bottom back button -->
	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="./profile.php" class="back-box" title="Back to profile">← Back to profile</a>
	</div>
</body>
</html>
