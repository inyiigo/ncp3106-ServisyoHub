<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Login / Sign Up • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="auth-body theme-profile-bg">
	<main class="form-card narrow">
		<h2>Sign in or Sign up</h2>
		<p class="hint">Enter your mobile number to continue.</p>

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

			<div class="actions">
				<button type="submit" class="btn">Continue</button>
				<a href="./user-choice.php" class="btn secondary" style="text-decoration:none; display:inline-block;">Back</a>
			</div>
		</form>
	</main>
</body>
</html>

