<?php
session_start();
include '../config/db_connect.php';

// Initialize variables
$error = "";
$mobile = "";

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mobile = trim($_POST['mobile']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users where mobile = '$mobile'";
    $res = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($res) > 0) {
        // Mobile number exists
        $row = mysqli_fetch_assoc($res);
        $db_password = $row['password'];
        
        if($password == $db_password){
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['mobile'] = $row['mobile'];
            $_SESSION['display_name'] = $row['first_name'] ?? 'User';
            header("Location: home-gawain.php");
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        // Mobile number not found
        $error = "No account exists with this mobile number.";
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
    <style>
        .text-error {
            color: #dc2626;
            background: #fee2e2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: 500;
            text-align: center;
        }
    </style>
</head>
<body class="auth-body theme-profile-bg">
    <main class="form-card narrow">
        <h2>Login</h2>
        <p class="hint">Enter your mobile number and password to sign in.</p>

        <?php if (!empty($error)): ?>
            <p class="text-error"><?php echo htmlspecialchars($error); ?></p>
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
            <a href="./signup.php">Sign Up</a>
        </p>
    </main>
</body>
</html>