<?php
// After verification, collect and set a password
session_start();
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
  <title>Set password • Servisyo Hub</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="auth-body theme-profile-bg">
  <main class="form-card narrow">
    <h2>Set your password</h2>
    <p class="hint">Mobile: <strong><?php echo htmlspecialchars($mobile); ?></strong></p>

  <form action="./home-gawain.php" method="POST" novalidate>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="At least 6 characters" minlength="6" required />
      </div>
      <div class="field">
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" placeholder="Repeat password" minlength="6" required />
      </div>

      <div class="actions">
        <button type="submit" class="btn">Finish</button>
        <a href="./signup-verify.php" class="btn secondary">Back</a>
      </div>
    </form>
  </main>
</body>
</html>
