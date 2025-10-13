<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Login / Sign Up • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<style>
		/* Minimal layout helpers in case styles.css lacks forms */
		.auth-body { display: grid; place-items: center; min-height: 100vh; background: #f8fafc; }
		.form-card { width: 100%; max-width: 420px; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 6px 24px rgba(0,0,0,.08); }
		.form-card h2 { margin: 0 0 8px; font-size: 1.4rem; }
		.form-card p { margin: 0 0 16px; color: #475569; }
		.field { display: grid; gap: 6px; margin: 12px 0; }
		.field label { font-weight: 600; font-size: .95rem; }
		.field input { padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; }
		.actions { margin-top: 16px; display: flex; gap: 12px; align-items: center; }
		.btn { appearance: none; border: none; background: #111827; color: #fff; padding: 12px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; }
		.btn.secondary { background: #e5e7eb; color: #111827; }
		.hint { font-size: .9rem; color: #64748b; }
	</style>
	<!-- If you already style forms in styles.css, the inline styles above are safe fallbacks. -->
</head>
<body class="auth-body">
	<main class="form-card">
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

