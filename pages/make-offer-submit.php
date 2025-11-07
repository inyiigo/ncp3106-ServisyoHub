<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/db_connect.php';

$db = $conn ?? $mysqli ?? null;
$viewerId = (int)($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db && $viewerId) {
    $jobId  = (int)($_POST['job_id'] ?? 0);
    $amount = isset($_POST['amount']) && trim($_POST['amount']) !== '' ? (float)$_POST['amount'] : null;
    
    if ($jobId > 0 && $amount !== null && $amount > 0) {
        // Insert offer with amount
        $sql = "INSERT INTO offers (job_id, user_id, amount, created_at) VALUES (?, ?, ?, NOW())";
        if ($stmt = @mysqli_prepare($db, $sql)) {
            mysqli_stmt_bind_param($stmt, 'iid', $jobId, $viewerId, $amount);
            if (@mysqli_stmt_execute($stmt)) {
                $offerId = (int)@mysqli_insert_id($db);
                @mysqli_stmt_close($stmt);
                
                // Notify job owner
                if ($offerId > 0) {
                    $jobOwnerId = 0;
                    if ($g = @mysqli_prepare($db, "SELECT user_id FROM jobs WHERE id = ? LIMIT 1")) {
                        mysqli_stmt_bind_param($g, 'i', $jobId);
                        if (@mysqli_stmt_execute($g)) {
                            $gr = @mysqli_stmt_get_result($g);
                            if ($gr && ($grow = @mysqli_fetch_assoc($gr))) {
                                $jobOwnerId = (int)($grow['user_id'] ?? 0);
                            }
                        }
                        @mysqli_stmt_close($g);
                    }
                    if ($jobOwnerId > 0 && $jobOwnerId !== $viewerId) {
                        if ($nst = @mysqli_prepare($db, "INSERT INTO notifications (user_id, actor_id, job_id, offer_id, created_at) VALUES (?, ?, ?, ?, NOW())")) {
                            mysqli_stmt_bind_param($nst, 'iiii', $jobOwnerId, $viewerId, $jobId, $offerId);
                            @mysqli_stmt_execute($nst);
                            @mysqli_stmt_close($nst);
                        }
                    }
                }
                
                // Redirect to My Gawain Offered tab
                header('Location: ./my-gawain.php?tab=offered');
                exit;
            }
            @mysqli_stmt_close($stmt);
        }
    }
}

// Fallback redirect on error
header('Location: ./home-gawain.php');
exit;
