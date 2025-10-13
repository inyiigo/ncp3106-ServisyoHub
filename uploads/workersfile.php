<?php
// Basic handler for registration form submissions and file uploads.
// NOTE: Replace this with database persistence and stronger validation as needed.

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		http_response_code(405);
		echo 'Method Not Allowed';
		exit;
}

// Create an uploads directory if it doesn't exist
$uploadBase = __DIR__ . DIRECTORY_SEPARATOR . 'storage';
if (!is_dir($uploadBase)) {
		@mkdir($uploadBase, 0775, true);
}

// Collect fields
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$address = trim($_POST['address'] ?? '');
$profession = trim($_POST['profession'] ?? '');

$errors = [];
foreach ([
		'first_name' => $firstName,
		'last_name' => $lastName,
		'phone' => $phone,
		'email' => $email,
		'gender' => $gender,
		'address' => $address,
		'profession' => $profession,
] as $field => $value) {
		if ($value === '') { $errors[] = strtoupper($field) . ' is required.'; }
}

// Basic email validation
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = 'EMAIL ADDRESS is invalid.';
}

// Handle profile image
$savedImagePath = '';
if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
		$errors[] = 'Profile IMAGE is required.';
} else {
		$img = $_FILES['image'];
		if ($img['error'] !== UPLOAD_ERR_OK) {
				$errors[] = 'Image upload failed (error code ' . $img['error'] . ').';
		} else {
				// Detect MIME type (prefer finfo, fallback to exif_imagetype)
				$mime = null;
				if (class_exists('finfo')) {
					$finfo = new finfo(FILEINFO_MIME_TYPE);
					$mime = $finfo->file($img['tmp_name']);
				} elseif (function_exists('exif_imagetype')) {
					$type = @exif_imagetype($img['tmp_name']);
					$map = [
						IMAGETYPE_JPEG => 'image/jpeg',
						IMAGETYPE_PNG => 'image/png',
						IMAGETYPE_GIF => 'image/gif',
						IMAGETYPE_BMP => 'image/bmp',
						IMAGETYPE_WEBP => 'image/webp',
						IMAGETYPE_TIFF_II => 'image/tiff',
						IMAGETYPE_TIFF_MM => 'image/tiff',
					];
					$mime = $map[$type] ?? null;
				}
				$allowed = [
					'image/jpeg' => 'jpg',
					'image/pjpeg' => 'jpg',
					'image/png' => 'png',
					'image/webp' => 'webp',
					'image/gif' => 'gif',
					'image/bmp' => 'bmp',
					'image/tiff' => 'tiff',
					'image/heic' => 'heic',
					'image/heif' => 'heif'
				];
				if (!isset($allowed[$mime])) {
					$errors[] = 'Image must be a valid picture (JPG, PNG, WEBP, GIF, BMP, TIFF, HEIC/HEIF).';
				} else {
						$safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', strtolower($firstName . '_' . $lastName));
						$filename = $safeBase . '_profile_' . time() . '.' . $allowed[$mime];
						$dest = $uploadBase . DIRECTORY_SEPARATOR . $filename;
						if (!move_uploaded_file($img['tmp_name'], $dest)) {
								$errors[] = 'Failed to save profile image.';
						} else {
								$savedImagePath = $dest;
						}
				}
		}
}

// Handle multiple application files (optional)
$savedFiles = [];
if (isset($_FILES['application_files']) && is_array($_FILES['application_files']['name'])) {
		$names = $_FILES['application_files']['name'];
		$tmps = $_FILES['application_files']['tmp_name'];
		$errorsArr = $_FILES['application_files']['error'];
		for ($i = 0; $i < count($names); $i++) {
				if ($errorsArr[$i] === UPLOAD_ERR_NO_FILE) { continue; }
				if ($errorsArr[$i] !== UPLOAD_ERR_OK) { $errors[] = 'A file failed to upload (error code ' . $errorsArr[$i] . ').'; continue; }
				$orig = $names[$i];
				$ext = pathinfo($orig, PATHINFO_EXTENSION);
				$safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', strtolower($firstName . '_' . $lastName));
				$dest = $uploadBase . DIRECTORY_SEPARATOR . $safeBase . '_' . time() . '_' . $i . ($ext ? ('.' . $ext) : '');
				if (move_uploaded_file($tmps[$i], $dest)) {
						$savedFiles[] = $dest;
				} else {
						$errors[] = 'Failed to save an uploaded file: ' . htmlspecialchars($orig);
				}
		}
}

if (!empty($errors)) {
		http_response_code(400);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Application <?php echo empty($errors) ? 'Received' : 'Error'; ?> • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="notify-body">
	<div class="card">
		<?php if (!empty($errors)): ?>
			<h2>Submission Error</h2>
			<div class="errors">
				<strong>Please fix the following:</strong>
				<ul>
					<?php foreach ($errors as $err): ?>
						<li><?php echo htmlspecialchars($err); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="actions">
				<a class="btn secondary" href="../pages/registration.php">Back to registration</a>
			</div>
		<?php else: ?>
			<h2>Application Received</h2>
			<p class="meta">Thank you, <strong><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></strong>. We received your application.</p>
			<ul>
				<li>Email: <?php echo htmlspecialchars($email); ?></li>
				<li>Phone: <?php echo htmlspecialchars($phone); ?></li>
				<li>Gender: <?php echo htmlspecialchars($gender); ?></li>
				<li>Profession: <?php echo htmlspecialchars($profession); ?></li>
				<li>Address: <?php echo nl2br(htmlspecialchars($address)); ?></li>
				<li>Profile Image: <?php echo $savedImagePath ? htmlspecialchars(basename($savedImagePath)) : 'N/A'; ?></li>
				<li>Uploaded Files: <?php echo count($savedFiles); ?></li>
			</ul>
			<div class="actions">
				<a class="btn" href="../pages/home.php">Go to Home</a>
				<a class="btn secondary" href="../pages/registration.php">Submit another</a>
			</div>
		<?php endif; ?>
	</div>
</body>
</html>

