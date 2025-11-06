<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/db_connect.php';
$db = $conn ?? $mysqli ?? null;

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId <= 0) {
  header('Location: ./login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

$job_id = (int)($_POST['job_id'] ?? 0);
$amount_raw = trim((string)($_POST['amount'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$amount = ($amount_raw === '') ? null : (float)$amount_raw;

if ($job_id <= 0 || !$db) {
  $_SESSION['offer_error'] = 'Invalid request.';
  header('Location: ./gawain-detail.php?id=' . $job_id);
  exit;
}

// Prevent owner from offering on own post
$ownerId = 0;
if ($st = @mysqli_prepare($db, "SELECT user_id FROM jobs WHERE id=? LIMIT 1")) {
  mysqli_stmt_bind_param($st, 'i', $job_id);
  if (@mysqli_stmt_execute($st)) {
    $rs = @mysqli_stmt_get_result($st);
    if ($rs && ($rw = @mysqli_fetch_assoc($rs))) $ownerId = (int)$rw['user_id'];
  }
  @mysqli_stmt_close($st);
}
if ($ownerId !== 0 && $ownerId === $currentUserId) {
  $_SESSION['offer_error'] = 'You cannot offer on your own post.';
  header('Location: ./gawain-detail.php?id=' . $job_id);
  exit;
}

// Insert offer into database
$now = date('Y-m-d H:i:s');
$ok = false;

if ($amount === null) {
  $sql = "INSERT INTO offers (job_id, user_id, amount, message, status, created_at) VALUES (?, ?, NULL, ?, 'pending', ?)";
  if ($st = @mysqli_prepare($db, $sql)) {
    mysqli_stmt_bind_param($st, 'iiss', $job_id, $currentUserId, $message, $now);
    $ok = @mysqli_stmt_execute($st);
    @mysqli_stmt_close($st);
  }
} else {
  $sql = "INSERT INTO offers (job_id, user_id, amount, message, status, created_at) VALUES (?, ?, ?, ?, 'pending', ?)";
  if ($st = @mysqli_prepare($db, $sql)) {
    mysqli_stmt_bind_param($st, 'iidss', $job_id, $currentUserId, $amount, $message, $now);
    $ok = @mysqli_stmt_execute($st);
    @mysqli_stmt_close($st);
  }
}

if ($ok) {
  $_SESSION['offer_success'] = 'Your offer has been sent to the job owner!';
} else {
  $_SESSION['offer_error'] = 'Failed to submit offer. Please try again.';
}

// Redirect back to job detail
// Owner will see updated "Offers Received (N)" and "View offers (N)" when they refresh
header('Location: ./gawain-detail.php?id=' . (int)$job_id);
exit;
