<?php
session_start();
require_once '../config/db.php';

// Initialize variables
$error = "";
$mobile = "";

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mobile = trim($_POST['mobile']);
    $password = trim($_POST['password']);

    // Normalize phone: strip non-digits, map +63########### to 0##########, and query both variants
    $normalized = preg_replace('/\D+/', '', $mobile);
    if (strpos($normalized, '63') === 0 && strlen($normalized) === 12) {
        $local = '0' . substr($normalized, 2); // 63917xxxxxxx -> 0917xxxxxxx
    } else {
        $local = $normalized;
    }
    $intl = (strlen($local) === 11 && $local[0] === '0') ? ('63' . substr($local, 1)) : $normalized;

    try {
        $stmt = $pdo->prepare('SELECT id, phone, password_hash FROM users WHERE phone IN (?, ?) ORDER BY id DESC LIMIT 1');
        $stmt->execute([$local, $intl]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['mobile'] = $user['phone'];
            header('Location: ./home-jobs.php');
            exit();
        } else {
            $error = $user ? 'Incorrect password. Please try again.' : 'No account found for this mobile number.';
        }
    } catch (Throwable $e) {
        $error = 'Login error. Please try again.';
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
</head>
<body class="auth-body theme-profile-bg">
	<main class="form-card narrow">
		<h2>Login</h2>
		<p class="hint">Enter your mobile number and password to sign in.</p>

        <?php if (!empty($error)): ?>
			<p class="text-error text-center"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

		<form action="" method="POST" novalidate>
			<div class="field">
				<label for="mobile">Mobile number</label>
				<input
					type="tel"
					id="mobile"
					name="mobile"
					value="<?php echo htmlspecialchars($mobile); ?>"
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
				<a href="./user-choice.php" class="btn secondary">Back</a>
			</div>
		</form>

	<p class="text-center" style="margin-top:1em;">
			Don't have an account?
			<a href="./registration.php">Sign Up</a>
		</p>
	</main>
</body>
</html>