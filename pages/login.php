<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Login • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="auth-body theme-profile-bg">
	<main class="form-card narrow">
		<h2>Login</h2>
		<p class="hint">Enter your mobile number and password to sign in.</p>

		<form action="./login-password.php" method="POST" novalidate>
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
					placeholder="Enter your password"
					required
				/>
			</div>
			<div class="actions">
				<button type="submit" class="btn">Login</button>
				<a href="./user-choice.php" class="btn secondary" style="text-decoration:none; display:inline-block;">Back</a>
			</div>
		</form>
		<p style="text-align:center; margin-top:1em;">
			Don't have an account?
			<a href="./signup.php">Sign Up</a>
		</p>
	</main>
</body>
</html>

