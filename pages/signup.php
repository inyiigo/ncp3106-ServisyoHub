<?php
// Signup: collect mobile first
session_start();
$mobile = isset($_SESSION['signup_mobile']) ? $_SESSION['signup_mobile'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Sign up • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="auth-body theme-profile-bg">
	<main class="form-card narrow">
		<h2>Create your account</h2>
		<p class="hint">Enter your mobile number to start sign up. We’ll verify it before you set a password.</p>

		<form action="./signup-verify.php" method="POST" novalidate>
			<div class="field">
				<label for="mobile">Mobile number</label>
				<input
					type="tel"
					id="mobile"
					name="mobile"
					placeholder="e.g. 0917 123 4567"
					inputmode="tel"
					pattern="[0-9\s+()-]{7,}"
					value="<?php echo htmlspecialchars($mobile); ?>"
					required
				/>
			</div>

			<div class="actions">
				<button type="submit" class="btn">Continue</button>
				<a href="./user-choice.php" class="btn secondary">Back</a>
			</div>
		</form>
	</main>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Sign Up • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="auth-body">
	<main class="form-card narrow">
		<h2>Sign Up</h2>
		<p class="hint">Create your account below.</p>
		<form action="../config/signup_act.php" method="POST" novalidate>
			<div class="field">
				<label for="mobile">Mobile number</label>
				<input
					type="tel"
					id="mobile"
					name="mobile"
					placeholder="e.g. 0917 123 4567"
					inputmode="tel"
					pattern="[0-9\s+()-]{7,}"
					required
				/>
			</div>
			<div class="field">
				<label for="password">Password</label>
				<input
					type="password"
					id="password"
					name="password"
					placeholder="Create a password"
					required
				/>
			</div>
			<div class="actions">
				<button type="submit" class="btn">Sign Up</button>
				<a href="login.php" class="btn secondary">Back to Login</a>
			</div>
		</form>
	</main>
</body>
</html>
