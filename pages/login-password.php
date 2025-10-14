<?php
$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
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
    <p class="hint">We found mobile number: <strong><?php echo htmlspecialchars($mobile ?: 'Unknown'); ?></strong></p>

  <form action="./home-services.php" method="POST" novalidate>
      <input type="hidden" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" />
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Your password" minlength="6" required />
      </div>

      <div class="actions">
        <button type="submit" class="btn">Sign in</button>
        <a href="./login.php" class="btn secondary" style="text-decoration:none; display:inline-block;">Back</a>
      </div>
    </form>
  </main>
</body>
</html>
