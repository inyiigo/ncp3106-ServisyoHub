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

		/* bottom back button */
		.bottom-box { position: fixed; right: 20px; bottom: 20px; z-index: 999; background: transparent; border: none; padding: 0; box-shadow: none; }
		.back-box { 
			display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:10px; 
			background: #0078a6; color: #fff; text-decoration:none; font-weight:700; 
			border:2px solid color-mix(in srgb, #0078a6 80%, #0000); 
			transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease; 
			box-shadow: 0 6px 18px rgba(0,120,166,.24); 
		}
		.back-box:hover { 
			transform: translateY(-4px) scale(1.02); 
			box-shadow: 0 12px 28px rgba(0,120,166,.32); 
			background: #006a94; 
			border-color: color-mix(in srgb, #0078a6 60%, #0000); 
		}
		@media (max-width:520px){
			.bottom-box{ left:12px; right:12px; bottom:14px; display:flex; justify-content:center; }
			.back-box{ width:100%; justify-content:center; }
		}
	</style>
</head>
<body class="theme-profile-bg">
	<div class="bg-logo"><img src="../assets/images/job_logo.png" alt="" /></div>

	<div class="dash-topbar center">
		<div class="dash-brand"><img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" /></div>
	</div>

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
                <a class="prof-item" href="./clients-profile.php?logout=1">
                    <svg class="prof-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4v4M14 10l5-5M9 7H7a4 4 0 0 0-4 4v5a4 4 0 0 0 4 4h5a4 4 0 0 0 4-4v-2"/></svg>
                    <span>Log out</span>
                    <svg class="prof-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
            </nav>
		</div>
	</div>

	<!-- Bottom back button -->
	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="./profile.php" class="back-box" title="Back to profile">← Back to profile</a>
	</div>
</body>
</html>
