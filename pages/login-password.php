<?php
session_start();
include('../config/db_connect.php');

// Get mobile from previous form
$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
    $password = trim($_POST['password']);

    // Check if mobile exists in database
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE mobile = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login success — set session and redirect
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['mobile'] = $mobile;

            header("Location: ./home-services.php");
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
      <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
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
        <a href="./login.php" class="btn secondary" style="text-decoration:none; display:inline-block;">Back</a>
      </div>
    </form>
  </main>
</body>
</html>
