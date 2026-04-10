<?php
session_start();
include './config/db_connect.php';

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_admin'])) {
	// First, ensure role column exists
	$alter_query = "ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'user'";
	if (!$conn->query($alter_query)) {
		$message = "Error preparing table: " . $conn->error;
		$message_type = "error";
	} else {
		// Insert admin account
		$mobile = "12345";
		$password = "12345";
		$role = "admin";
		
		// Check if admin already exists
		$check_stmt = $conn->prepare("SELECT id FROM users WHERE mobile = ? AND role = 'admin'");
		$check_stmt->bind_param("s", $mobile);
		$check_stmt->execute();
		$result = $check_stmt->get_result();
		
		if ($result->num_rows > 0) {
			$message = "Admin account already exists!";
			$message_type = "warning";
		} else {
			$stmt = $conn->prepare("INSERT INTO users (mobile, password, role, created_at) VALUES (?, ?, ?, NOW())");
			$stmt->bind_param("sss", $mobile, $password, $role);
			
			if ($stmt->execute()) {
				$message = "✓ Admin account created successfully!<br>Mobile: <strong>12345</strong><br>Password: <strong>12345</strong>";
				$message_type = "success";
			} else {
				$message = "Error creating admin account: " . $stmt->error;
				$message_type = "error";
			}
			$stmt->close();
		}
		$check_stmt->close();
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Setup - ServisyoHub</title>
	<style>
		body {
			margin: 0;
			padding: 20px;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			background: linear-gradient(135deg, #2596be 0%, #1a7a9e 100%);
			min-height: 100vh;
			display: grid;
			place-items: center;
		}
		.container {
			background: white;
			border-radius: 20px;
			padding: 40px;
			max-width: 500px;
			width: 100%;
			box-shadow: 0 20px 60px rgba(0,0,0,0.3);
		}
		h1 {
			margin: 0 0 12px;
			color: #2596be;
			font-size: 28px;
		}
		.subtitle {
			color: #666;
			margin: 0 0 30px;
			font-size: 14px;
		}
		.message {
			padding: 16px;
			border-radius: 12px;
			margin-bottom: 20px;
			font-weight: 600;
		}
		.message.success {
			background: #d1fae5;
			color: #065f46;
			border: 1px solid #6ee7b7;
		}
		.message.error {
			background: #fee2e2;
			color: #991b1b;
			border: 1px solid #fca5a5;
		}
		.message.warning {
			background: #fef3c7;
			color: #78350f;
			border: 1px solid #fcd34d;
		}
		.form-group {
			margin-bottom: 20px;
		}
		label {
			display: block;
			margin-bottom: 8px;
			color: #333;
			font-weight: 600;
			font-size: 14px;
		}
		.info-box {
			background: #f0f9ff;
			border: 1px solid #bfdbfe;
			border-radius: 12px;
			padding: 16px;
			margin-bottom: 20px;
			font-size: 13px;
			color: #1e40af;
			line-height: 1.6;
		}
		button {
			width: 100%;
			padding: 12px;
			background: linear-gradient(135deg, #7cd4c4 0%, #5fc9b8 100%);
			color: #0b2c24;
			border: none;
			border-radius: 12px;
			font-size: 16px;
			font-weight: 700;
			cursor: pointer;
			transition: all 0.3s ease;
		}
		button:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 20px rgba(124, 212, 196, 0.4);
		}
		button:active {
			transform: translateY(0);
		}
		.credentials {
			background: #f9fafb;
			border: 2px solid #e5e7eb;
			border-radius: 12px;
			padding: 16px;
			margin: 20px 0;
			font-family: 'Courier New', monospace;
			font-size: 14px;
		}
		.credentials-row {
			display: flex;
			justify-content: space-between;
			margin-bottom: 8px;
			align-items: center;
		}
		.credentials-row:last-child {
			margin-bottom: 0;
		}
		.label-text {
			font-weight: 600;
			color: #333;
		}
		.value-text {
			background: #2596be;
			color: white;
			padding: 6px 12px;
			border-radius: 8px;
			font-weight: 700;
			letter-spacing: 1px;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>🔐 Admin Setup</h1>
		<p class="subtitle">Create admin account for full system access</p>
		
		<?php if ($message): ?>
			<div class="message <?php echo $message_type; ?>">
				<?php echo $message; ?>
			</div>
		<?php endif; ?>
		
		<div class="info-box">
			⚠️ <strong>First time only:</strong> Click the button below to create an admin account with the credentials shown.
		</div>
		
		<div class="credentials">
			<div class="credentials-row">
				<span class="label-text">Mobile Number:</span>
				<span class="value-text">12345</span>
			</div>
			<div class="credentials-row">
				<span class="label-text">Password:</span>
				<span class="value-text">12345</span>
			</div>
		</div>
		
		<form method="POST">
			<button type="submit" name="create_admin" value="1">
				✓ Create Admin Account
			</button>
		</form>
		
		<div class="info-box" style="margin-top: 20px; background: #f5f3ff; border-color: #c4b5fd; color: #5b21b6;">
			💡 <strong>After creation:</strong> Login with mobile <strong>12345</strong> and password <strong>12345</strong> to access the admin dashboard.
		</div>
	</div>
</body>
</html>
