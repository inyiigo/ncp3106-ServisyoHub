<?php
// Handle mobile submission and simulate OTP verification
session_start();
if (!empty($_POST['mobile'])) {
  $_SESSION['signup_mobile'] = trim($_POST['mobile']);
}
$mobile = isset($_SESSION['signup_mobile']) ? $_SESSION['signup_mobile'] : '';
if (!$mobile) {
  header('Location: ./signup.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verify mobile • Servisyo Hub</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="auth-body theme-profile-bg">
  <main class="form-card narrow">
    <h2>Verify your mobile</h2>
    <p class="hint">We sent a 6-digit code to <strong><?php echo htmlspecialchars($mobile); ?></strong></p>

    <form action="./signup-password.php" method="POST" novalidate>
      <div class="field">
        <label for="otp">Verification code</label>
        <input
          type="text"
          id="otp"
          name="otp"
          placeholder="Enter code"
          inputmode="numeric"
          pattern="[0-9]{4,6}"
          required
        />
      </div>

      <div class="actions">
        <button type="submit" class="btn">Verify</button>
        <a href="./signup.php" class="btn secondary">Back</a>
      </div>
    </form>
  </main>
</body>
</html>
