<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/ai_moderation.php';

$db = $conn ?? $mysqli ?? null;
$viewerId = (int)($_SESSION['user_id'] ?? 0);

if ($db) {
    @mysqli_select_db($db, 'login');
}

function notif_has_column($db, string $column): bool {
    $col = mysqli_real_escape_string($db, $column);
    $res = @mysqli_query($db, "SHOW COLUMNS FROM notifications LIKE '{$col}'");
    $ok = ($res && mysqli_num_rows($res) > 0);
    if ($res) { @mysqli_free_result($res); }
    return $ok;
}

function offer_has_column($db, string $column): bool {
    $col = mysqli_real_escape_string($db, $column);
    $res = @mysqli_query($db, "SHOW COLUMNS FROM offers LIKE '{$col}'");
    $ok = ($res && mysqli_num_rows($res) > 0);
    if ($res) { @mysqli_free_result($res); }
    return $ok;
}

function ensure_offer_review_columns($db): void {
    if (!offer_has_column($db, 'admin_status')) {
        @mysqli_query($db, "ALTER TABLE offers ADD COLUMN admin_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER status");
    }
    if (!offer_has_column($db, 'citizen_status')) {
        @mysqli_query($db, "ALTER TABLE offers ADD COLUMN citizen_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER admin_status");
    }
}

function ensure_notifications_table($db): void {
    @mysqli_query($db, "CREATE TABLE IF NOT EXISTS notifications (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT(10) UNSIGNED NOT NULL,
        actor_id INT(10) UNSIGNED NOT NULL,
        job_id INT(11) NOT NULL,
        comment_id INT UNSIGNED DEFAULT NULL,
        offer_id INT(11) DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        seen_at DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_user_seen (user_id, seen_at),
        KEY idx_job (job_id),
        KEY idx_actor (actor_id),
        KEY idx_offer (offer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    if (!notif_has_column($db, 'offer_id')) {
        @mysqli_query($db, "ALTER TABLE notifications ADD COLUMN offer_id INT(11) DEFAULT NULL AFTER comment_id");
        @mysqli_query($db, "ALTER TABLE notifications ADD KEY idx_offer (offer_id)");
    }
    if (!notif_has_column($db, 'seen_at')) {
        @mysqli_query($db, "ALTER TABLE notifications ADD COLUMN seen_at DATETIME DEFAULT NULL AFTER created_at");
    }
    if (!notif_has_column($db, 'created_at')) {
        @mysqli_query($db, "ALTER TABLE notifications ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db && $viewerId) {
    ai_ensure_moderation_schema($db);
    ensure_offer_review_columns($db);

    $jobId  = (int)($_POST['job_id'] ?? 0);
    $amount = isset($_POST['amount']) && trim($_POST['amount']) !== '' ? (float)$_POST['amount'] : null;
    
    if ($jobId > 0 && $amount !== null && $amount > 0) {
        // Insert offer with amount
        $sql = "INSERT INTO `login`.`offers` (job_id, user_id, amount, created_at) VALUES (?, ?, ?, NOW())";
        if ($stmt = @mysqli_prepare($db, $sql)) {
            mysqli_stmt_bind_param($stmt, 'iid', $jobId, $viewerId, $amount);
            if (@mysqli_stmt_execute($stmt)) {
                $offerId = (int)@mysqli_insert_id($db);
                @mysqli_stmt_close($stmt);
                
                // Notify job owner
                if ($offerId > 0) {
                    if ($up = @mysqli_prepare($db, "UPDATE `login`.`offers` SET status = 'pending', admin_status = 'pending', citizen_status = 'pending' WHERE id = ?")) {
                        mysqli_stmt_bind_param($up, 'i', $offerId);
                        @mysqli_stmt_execute($up);
                        @mysqli_stmt_close($up);
                    }

                    $jobContext = [
                        'job_title' => '',
                        'job_description' => '',
                        'job_location' => '',
                    ];
                    if ($ctx = @mysqli_prepare($db, "SELECT COALESCE(title,''), COALESCE(description,''), COALESCE(location,'') FROM `login`.`jobs` WHERE id = ? LIMIT 1")) {
                        mysqli_stmt_bind_param($ctx, 'i', $jobId);
                        if (@mysqli_stmt_execute($ctx)) {
                            $ctxRes = @mysqli_stmt_get_result($ctx);
                            if ($ctxRes && ($ctxRow = @mysqli_fetch_row($ctxRes))) {
                                $jobContext['job_title'] = (string)($ctxRow[0] ?? '');
                                $jobContext['job_description'] = (string)($ctxRow[1] ?? '');
                                $jobContext['job_location'] = (string)($ctxRow[2] ?? '');
                            }
                        }
                        @mysqli_stmt_close($ctx);
                    }

                    $moderation = ai_moderate_content('offer', [
                        'amount' => (float)$amount,
                        'job_id' => (int)$jobId,
                        'job_title' => $jobContext['job_title'],
                        'description' => $jobContext['job_description'],
                        'location' => $jobContext['job_location'],
                    ]);
                    ai_apply_offer_decision($db, (int)$offerId, $moderation);

                    $jobOwnerId = 0;
                    if ($g = @mysqli_prepare($db, "SELECT user_id FROM `login`.`jobs` WHERE id = ? LIMIT 1")) {
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
                        ensure_notifications_table($db);
                        if ($nst = @mysqli_prepare($db, "INSERT INTO `login`.`notifications` (user_id, actor_id, job_id, offer_id, created_at) VALUES (?, ?, ?, ?, NOW())")) {
                            mysqli_stmt_bind_param($nst, 'iiii', $jobOwnerId, $viewerId, $jobId, $offerId);
                            @mysqli_stmt_execute($nst);
                            @mysqli_stmt_close($nst);
                        }
                    }
                }
                
                // Redirect to success page
                $successQs = http_build_query([
                    'id' => (int)$jobId,
                    'amount' => number_format((float)$amount, 2, '.', ''),
                ]);
                header('Location: ./make-offer-success.php?' . $successQs);
                exit;
            }
            @mysqli_stmt_close($stmt);
        }
    }

    // Return user to offer flow if payload is incomplete/invalid.
    if ($jobId > 0) {
        $q = 'id=' . (int)$jobId;
        if ($amount !== null && $amount > 0) {
            $q .= '&amount=' . rawurlencode(number_format((float)$amount, 2, '.', ''));
        }
        header('Location: ./make-offer-compose.php?' . $q);
        exit;
    }
}

// Fallback redirect on error
header('Location: ./home-gawain.php');
exit;
