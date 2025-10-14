<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// ...process registration (e.g., save to database)...
	// After successful registration, redirect to login page
	header('Location: login.php');
	exit();
}
?>
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
		<form action="signup.php" method="POST" novalidate>
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
				<a href="login.php" class="btn secondary" style="text-decoration:none; display:inline-block;">Back to Login</a>
			</div>
		</form>
	</main>
</body>
</html>
