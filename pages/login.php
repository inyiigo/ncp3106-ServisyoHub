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

<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT id, mobile, password FROM users WHERE mobile = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $dbPassword = $row['password'];
            $authenticated = false;

            if (password_get_info($dbPassword)['algo']) {
                if (password_verify($password, $dbPassword)) {
                    $authenticated = true;
                    if (password_needs_rehash($dbPassword, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $rehashStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        if ($rehashStmt) {
                            $rehashStmt->bind_param("si", $newHash, $row['id']);
                            $rehashStmt->execute();
                            $rehashStmt->close();
                        }
                    }
                }
            } elseif (hash_equals($dbPassword, $password)) {
                $authenticated = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upgradeStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($upgradeStmt) {
                    $upgradeStmt->bind_param("si", $newHash, $row['id']);
                    $upgradeStmt->execute();
                    $upgradeStmt->close();
                }
            }

            if ($authenticated) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['mobile'] = $row['mobile'];
                header("Location: home-services.php");
                exit();
            }

            $error = "Incorrect password. Please try again.";
        } else {
            $error = "No account found for this mobile number.";
        }
        $stmt->close();
    } else {
        $error = "Something went wrong. Please try again later.";
    }
=======
    $query = "SELECT * FROM users where mobile = '$mobile'";
	$res = mysqli_query($conn, $query);
	while($row = mysqli_fetch_assoc($res)){
		$db_password = $row['password'];
		if($password == $db_password){
			$_SESSION['user_id'] = $row['id'];
			$_SESSION['mobile'] = $row['mobile'];
			header("Location: home-gawain.php");
			exit();
		} else {
			$error = "Incorrect password. Please try again.";
		}
	}
>>>>>>> 36296daeac2a6becfe614ffdf3bd8f605993a44b
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Login • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="auth-body theme-profile-bg">
	<main class="form-card narrow">
		<h2>Login</h2>
		<p class="hint">Enter your mobile number and password to sign in.</p>

        <?php if (!empty($error)): ?>
			<p class="text-error text-center"><?php echo htmlspecialchars($error); ?></p>
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