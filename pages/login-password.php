<?php
session_start();
require_once('../config/db.php');

// Get mobile from previous form
$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
// Normalize phone: strip non-digits; handle +63 -> 0; prepare both variants
$normalized = preg_replace('/\D+/', '', $mobile);
if (strpos($normalized, '63') === 0 && strlen($normalized) === 12) {
    $local = '0' . substr($normalized, 2);
} else {
    $local = $normalized;
}
$intl = (strlen($local) === 11 && $local[0] === '0') ? ('63' . substr($local, 1)) : $normalized;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
    $password = trim($_POST['password']);

    // Check if mobile exists in database (try both normalized variants, prefer latest)
    $stmt = $pdo->prepare("SELECT id, phone, password_hash FROM users WHERE phone IN (?, ?) ORDER BY id DESC LIMIT 1");
    $stmt->execute([$local, $intl]);
    $user = $stmt->fetch();

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Login success — set session and redirect
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['mobile'] = $user['phone'];

            header("Location: ./home-jobs.php");
            exit;
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "No account found for this mobile number.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Enter Password • Servisyo Hub</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="auth-body theme-profile-bg">
  <main class="form-card narrow">
    <h2>Enter your password</h2>
    <p class="hint">
      We found mobile number:
      <strong><?php echo htmlspecialchars($mobile ?: 'Unknown'); ?></strong>
    </p>

    <?php if (!empty($error)): ?>
  <p class="text-error text-center"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="" method="POST" novalidate>
      <input type="hidden" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" />

      <div class="field">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Your password"
          minlength="6"
          required
        />
      </div>

      <div class="actions">
        <button type="submit" class="btn">Sign in</button>
  <a href="./login.php" class="btn secondary">Back</a>
      </div>
    </form>
  </main>
</body>
</html>
