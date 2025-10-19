<?php
session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/../config/db.php';

        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $gender = trim($_POST['gender'] ?? '');
        $profession = trim($_POST['profession'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($first === '' || $last === '' || $phone === '' || $email === '' || $password === '' || $gender === '' || $profession === '' || $address === '') {
            throw new RuntimeException('Please fill in all required fields.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email address.');
        }

        // Normalize phone to a consistent local format: digits-only, convert +63xxxxxxxxxx to 0xxxxxxxxxx
        $phone = preg_replace('/\D+/', '', $phone);
        if (strpos($phone, '63') === 0 && strlen($phone) === 12) { // e.g., 63917xxxxxxx
            $phone = '0' . substr($phone, 2); // 0917xxxxxxx
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new RuntimeException('An account with this email already exists.');
        }

        // Also prevent duplicate phone numbers for login consistency
        $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            throw new RuntimeException('An account with this mobile number already exists.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, phone, email, password_hash, gender, profession, address) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$first, $last, $phone, $email, $hash, $gender, $profession, $address]);
        $userId = (int)$pdo->lastInsertId();

        if (!empty($_FILES['application_files']) && is_array($_FILES['application_files']['name'])) {
            $uploadDir = dirname(__DIR__) . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
            $maxBytes = 5 * 1024 * 1024; // 5MB
            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $names = $_FILES['application_files']['name'];
            $tmpNames = $_FILES['application_files']['tmp_name'];
            $sizes = $_FILES['application_files']['size'];
            $errors = $_FILES['application_files']['error'];

            for ($i = 0, $n = count($names); $i < $n; $i++) {
                if ($errors[$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if ($sizes[$i] > $maxBytes) {
                    continue;
                }
                $mime = $finfo->file($tmpNames[$i]) ?: 'application/octet-stream';
                if (!in_array($mime, $allowed, true)) {
                    continue;
                }
                $ext = pathinfo($names[$i], PATHINFO_EXTENSION);
                $safeExt = preg_replace('/[^A-Za-z0-9]+/', '', $ext);
                $stored = bin2hex(random_bytes(8)) . '_' . time() . ($safeExt ? ('.' . strtolower($safeExt)) : '');
                $dest = $uploadDir . '/' . $stored;

                if (move_uploaded_file($tmpNames[$i], $dest)) {
                    $stmtF = $pdo->prepare('INSERT INTO user_files (user_id, original_name, stored_name, mime_type, size_bytes) VALUES (?,?,?,?,?)');
                    $stmtF->execute([$userId, $names[$i], $stored, $mime, $sizes[$i]]);
                }
            }
        }

        $_SESSION['display_name'] = $first;
        $_SESSION['flash_success'] = 'Registration completed successfully.';
        header('Location: ./home-jobs.php');
        exit;
    }
} catch (Throwable $e) {
    $_SESSION['flash_error'] = $e->getMessage();
}

$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : 'there';
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Home • Jobs • Servisyo Hub</title>
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <script defer src="../assets/js/script.js"></script>
</head>
<body class="theme-profile-bg">

    <div class="dash-topbar center">
        <div class="dash-brand">
            <img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" />
        </div>
    </div>

    <div class="dash-overlay"></div>

    <div class="dash-shell">
        <main class="dash-content">
            <h1 class="dash-greet">Hi <?php echo htmlspecialchars($display); ?>!</h1>
            <p class="dash-muted">Welcome to your job application dashboard.</p>
            <p class="dash-muted">Where skilled hands meet local demand.</p>

            <section class="dash-cards">
                <div class="dash-card green">
                    <div>
                        <div class="dash-pill">Ready to work?</div>
                        <h3>Browse Job Posts</h3>
                        <p>See opportunities that match your profession.</p>
                    </div>
                    <a href="./my-jobs.php" class="dash-pill">Explore</a>
                </div>

                <div class="dash-card blue">
                    <div>
                        <div class="dash-pill">Complete your details</div>
                        <h3>Update Your Profile</h3>
                        <p>Stand out with a complete profile.</p>
                    </div>
                    <a href="./profile.php" class="dash-pill">Update</a>
                </div>
            </section>
        </main>

        <aside class="dash-aside">
            <nav class="dash-nav">
                <h3>Navigation</h3>
                <a href="./home-jobs.php" class="active">
                    <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/>
                    </svg>
                    Home
                </a>
                <a href="./my-jobs.php">
                    <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 7h16M4 12h10M4 17h7"/>
                    </svg>
                    My Jobs <span class="dash-badge">0</span>
                </a>
                <a href="./profile.php">
                    <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
                    </svg>
                    Profile
                </a>
            </nav>
        </aside>
    </div>

    <!-- Floating bottom navigation -->
    <nav class="dash-bottom-nav">
        <a href="./home-jobs.php" class="active" aria-label="Home">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/>
            </svg>
            <span>Home</span>
        </a>
        <a href="./my-jobs.php" aria-label="My Jobs">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 7h16M4 12h10M4 17h7"/>
            </svg>
            <span>My Jobs</span>
        </a>
        <a href="./profile.php" aria-label="Profile">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
            </svg>
            <span>Profile</span>
        </a>
    </nav>
</body>
</html>
