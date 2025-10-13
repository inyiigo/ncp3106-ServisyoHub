<?php
// Simple user-type selection
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Get Started — Servisyo Hub</title>
<style>
  body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:0;display:flex;min-height:100vh;align-items:center;justify-content:center;background:#fff}
  .card{max-width:640px;padding:28px;border-radius:10px;box-shadow:0 6px 30px rgba(0,0,0,.06);text-align:center}
  .choices{display:flex;gap:12px;margin-top:18px;justify-content:center}
  .btn{padding:12px 20px;border-radius:8px;text-decoration:none;color:#fff;background:#0b6b3a}
  .btn.secondary{background:#6c757d}
</style>
</head>
<body>
  <div class="card">
    <h2>You are here for</h2>
    <p>Choose one to continue</p>
    <div class="choices">
      <a class="btn" href="login.php?type=services">A Service</a>
      <a class="btn secondary" href="register.php?type=job">A Job</a>
    </div>
  </div>
</body>
</html>
