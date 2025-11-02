<?php
session_start();

header('Content-Type: application/json');

/* Safe DB connection */
$configPath = __DIR__ . '/config.php';
$mysqli = null;
$dbAvailable = false;

if (file_exists($configPath)) { require_once $configPath; }
$attempts = [];
if (isset($db_host, $db_user, $db_pass, $db_name)) $attempts[] = [$db_host, $db_user, $db_pass, $db_name];
$attempts[] = ['localhost', 'root', '', 'servisyohub'];

foreach ($attempts as $creds) {
	list($h,$u,$p,$n) = $creds;
	mysqli_report(MYSQLI_REPORT_OFF);
	try {
		$conn = @mysqli_connect($h,$u,$p,$n);
		if ($conn && !mysqli_connect_errno()) { $mysqli = $conn; $dbAvailable = true; break; }
	} catch (Throwable $ex) {
		// Silent fail
	} finally {
		mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);
	}
}

if (!$dbAvailable) {
	echo json_encode(['success' => false, 'message' => 'Database unavailable']);
	exit;
}

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$search_query = isset($_POST['query']) ? trim($_POST['query']) : '';

if ($search_query === '') {
	echo json_encode(['success' => false, 'message' => 'Empty query']);
	exit;
}

// Save the search query
$sql = "INSERT INTO search_history (user_id, search_query, searched_at) VALUES (?, ?, NOW())";
if ($stmt = mysqli_prepare($mysqli, $sql)) {
	mysqli_stmt_bind_param($stmt, 'is', $user_id, $search_query);
	if (mysqli_stmt_execute($stmt)) {
		echo json_encode(['success' => true]);
	} else {
		echo json_encode(['success' => false, 'message' => 'Failed to save']);
	}
	mysqli_stmt_close($stmt);
} else {
	echo json_encode(['success' => false, 'message' => 'Database error']);
}

if ($mysqli) {
	mysqli_close($mysqli);
}
