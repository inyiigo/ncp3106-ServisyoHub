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
  <style>
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
</head>
<body class="auth-body">
  <main class="form-card">
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
