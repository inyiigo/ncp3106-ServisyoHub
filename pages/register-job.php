<?php
session_start();

// On POST create a simple session and redirect to home
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['user_id'] = time();
    $_SESSION['username'] = trim($_POST['username'] ?? 'JobUser');
    $_SESSION['user_choice'] = 'job';
    header('Location: home.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Register — Servisyo Hub</title></head>
<body>
    <h1>Register as Job Seeker</h1>
    <form method="post">
        <label>Full name<br><input name="username" required></label><br><br>
        <label>Email<br><input name="email" type="email"></label><br><br>
        <label>Password<br><input name="password" type="password"></label><br><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>