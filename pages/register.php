<?php
<?php
session_start();
// registration page — simple form that sets session for demo
$type = $_GET['type'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['user_id'] = time();
    $_SESSION['username'] = trim($_POST['username'] ?: 'User');
    // normalize type values: 'job' or 'services'
    $_SESSION['user_choice'] = $_POST['type'] ?? $type ?? 'services';
    header('Location: home.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Register</title></head>
<body>
  <h1>Create Account</h1>
  <form method="post">
    <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
    <label>Full name<br><input name="username" required></label><br><br>
    <label>Email<br><input name="email" type="email"></label><br><br>
    <label>Password<br><input name="password" type="password"></label><br><br>
    <button type="submit">Register</button>
  </form>
  <p><a href="login.php?type=<?php echo urlencode($type); ?>">Already have account? Sign in</a></p>
</body>
</html>