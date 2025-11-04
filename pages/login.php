<?php
session_start();
include '../config/db_connect.php';

// Initialize variables
$error = "";
$mobile = "";

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$mobile = trim($_POST['mobile'] ?? '');
	$password = trim($_POST['password'] ?? '');

	if ($mobile !== '' && $password !== '') {
		// Query all columns to be compatible with schemas that may or may not have password_hash
		if ($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE mobile = ? LIMIT 1")) {
			mysqli_stmt_bind_param($stmt, 's', $mobile);
			if (mysqli_stmt_execute($stmt)) {
				$res = mysqli_stmt_get_result($stmt);
				if ($row = mysqli_fetch_assoc($res)) {
					$ok = false;
					// Prefer hashed password when present
					if (isset($row['password_hash']) && $row['password_hash'] !== '') {
						$ok = password_verify($password, $row['password_hash']);
					} else if (isset($row['password'])) {
						// Legacy plain-text fallback (not recommended in production)
						$ok = hash_equals((string)$row['password'], (string)$password);
					}

					if ($ok) {
						$_SESSION['user_id'] = (int)($row['id'] ?? 0);
						$_SESSION['mobile'] = (string)($row['mobile'] ?? $mobile);
						header("Location: home-gawain.php");
						exit();
					} else {
						$error = "Incorrect password. Please try again.";
					}
				} else {
					$error = "Mobile number not found.";
				}
			} else {
				$error = "Login unavailable right now. Please try again later.";
			}
			mysqli_stmt_close($stmt);
		} else {
			$error = "Login unavailable right now. Please try again later.";
		}
	} else {
		$error = "Please enter your mobile and password.";
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Login • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<style>
		/* Screen background and layout: full-height, left-aligned */
		body.login-bg{ min-height:100dvh; margin:0; display:flex; align-items:center; justify-content:flex-start; padding: clamp(20px, 6vw, 80px); padding-left: clamp(48px, 14vw, 260px); background:
			radial-gradient(1200px 700px at 20% -10%, rgba(255,255,255,.18), rgba(255,255,255,0) 60%),
			linear-gradient(180deg, rgba(0,0,0,.18), rgba(0,0,0,.18)),
			url('../assets/images/login background.png') center/cover no-repeat fixed;
		}
		/* Form wrapper (no card) */
		.login-panel{ width:min(520px,92vw); min-height:auto; border-radius:0; overflow:visible; display:block; box-shadow:none; background: transparent; position:relative; }

		/* Left white visual */
		.login-visual{ background:#ffffff; display:grid; align-content:center; justify-items:center; padding:20px; position:relative; }
		.login-visual-inner{ width:min(92%, 640px); min-height:420px; border-radius:34px; background:#f7fafc; border:1px solid #eef2f7; box-shadow: inset 0 0 0 1px rgba(2,6,23,.06); position:relative; display:grid; place-items:center; }
		.login-brand{ position:absolute; top:18px; left:18px; }
		.login-brand img{ height:44px; }
		/* Optional decorative blob */
		.visual-blob{ position:absolute; inset:20px; border-radius:40px; background: radial-gradient(500px 260px at 35% 40%, rgba(14,116,144,.08), rgba(14,116,144,.02)); pointer-events:none; }

		/* Right dark form */
		.login-form{ padding: 0; color:#e7f5ef; display:grid; align-content:center; }
		.login-title{ margin:0 0 10px; font-size: clamp(28px, 5vw, 48px); font-weight:800; }
		.login-sub{ margin:0 0 22px; color: rgba(231,245,239,.88); font-weight:700; }
		.field{ display:grid; gap:6px; margin:10px 0; }
		.label{ font-weight:800; opacity:.95; }
		.pill{ width:100%; height:48px; border-radius:999px; border:1px solid rgba(255,255,255,.22); background: rgba(0,0,0,.28); color:#fff; font: inherit; padding:0 16px; }
		.pill::placeholder{ color: rgba(255,255,255,.65); }
		.row{ display:flex; align-items:center; justify-content:flex-end; }
		.link{ color:#9de0d5; text-decoration:none; font-weight:700; }
		.cta{ margin-top:10px; height:52px; border-radius:999px; border:0; background:#7cd4c4; color:#0b2c24; font-weight:900; cursor:pointer; box-shadow:none; width:100%; }
		.cta:hover{ filter:brightness(1.03); }
		.meta{ color: rgba(231,245,239,.9); }
		.error{ background: rgba(239,68,68,.12); color: #fecaca; border:1px solid rgba(239,68,68,.35); padding:10px 12px; border-radius:10px; text-align:center; font-weight:700; }
		.tight{ max-width:460px; }
	</style>
</head>
<body class="login-bg">
	<div class="login-panel">

		<!-- Right form area -->
		<div class="login-form">
			<div class="tight">
				<h1 class="login-title">Login</h1>
				<p class="login-sub">Enter your credentials to continue.</p>

				<?php if (!empty($error)): ?>
				<div class="error"><?php echo htmlspecialchars($error); ?></div>
				<?php endif; ?>

				<form action="" method="POST" novalidate>
					<div class="field">
						<label class="label" for="mobile">Mobile number</label>
						<input type="tel" id="mobile" name="mobile" class="pill" value="<?php echo htmlspecialchars($mobile); ?>" placeholder="Enter your mobile number" inputmode="tel" pattern="[0-9\s+()-]{7,}" required />
					</div>

					<div class="field">
						<label class="label" for="password">Password</label>
						<input type="password" id="password" name="password" class="pill" placeholder="Enter your password" required />
					</div>

					<div class="row" style="margin: 6px 0 10px;">
						<a class="link" href="./login-password.php">Forgot Password?</a>
					</div>

					<button type="submit" class="cta">Login</button>

					<p class="meta" style="margin-top:14px;">Don’t have an account? <a class="link" href="./signup.php">Register Now</a></p>
					<p class="meta" style="margin-top:4px;"><a class="link" href="./terms-and-conditions.php">Terms and Services</a></p>
				</form>
			</div>
		</div>
	</div>
</body>
</html>