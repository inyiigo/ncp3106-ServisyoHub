<?php
session_start();
require_once "../config/db_connect.php";

if (empty($_SESSION['user_id'])) {
	header('Location: ./login.php');
	exit;
}

$db = $conn ?? null;
if (!$db instanceof mysqli) {
	http_response_code(500);
	exit('Database connection unavailable.');
}

$avatarStatusColumnExists = false;
if ($checkCol = @mysqli_query($db, "SHOW COLUMNS FROM users LIKE 'avatar_status'")) {
	$avatarStatusColumnExists = (@mysqli_num_rows($checkCol) > 0);
	@mysqli_free_result($checkCol);
}
if (!$avatarStatusColumnExists) {
	@mysqli_query($db, "ALTER TABLE users ADD COLUMN avatar_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER avatar");
}

$sessionUserId = (int)($_SESSION['user_id'] ?? 0);
$sessionRole = '';
if ($sessionUserId > 0 && ($roleStmt = mysqli_prepare($db, 'SELECT COALESCE(role,\'\') AS role FROM users WHERE id = ? LIMIT 1'))) {
	mysqli_stmt_bind_param($roleStmt, 'i', $sessionUserId);
	mysqli_stmt_execute($roleStmt);
	$roleRes = mysqli_stmt_get_result($roleStmt);
	if ($roleRes && ($roleRow = mysqli_fetch_assoc($roleRes))) {
		$sessionRole = strtolower(trim((string)($roleRow['role'] ?? '')));
	}
	if ($roleRes) {
		mysqli_free_result($roleRes);
	}
	mysqli_stmt_close($roleStmt);
}

if ($sessionRole !== 'admin') {
	http_response_code(403);
	exit('Forbidden.');
}

function h(?string $value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function avatar_public_url(?string $avatar): ?string {
	$avatar = trim((string)$avatar);
	if ($avatar === '') {
		return null;
	}

	$avatar = str_replace('\\', '/', $avatar);
	if (strpos($avatar, '..') !== false) {
		return null;
	}

	$avatar = ltrim($avatar, '/');
	if (stripos($avatar, 'assets/') === 0 || stripos($avatar, 'uploads/') === 0) {
		return '../' . $avatar;
	}

	return null;
}

function avatar_storage_path(?string $avatar): ?string {
	$avatar = trim((string)$avatar);
	if ($avatar === '') {
		return null;
	}

	$avatar = str_replace('\\', '/', $avatar);
	if (strpos($avatar, '..') !== false) {
		return null;
	}

	$avatar = ltrim($avatar, '/');
	$full = realpath(__DIR__ . '/../' . $avatar);
	if ($full === false) {
		return null;
	}

	$allowedRoots = [
		realpath(__DIR__ . '/../assets/uploads/avatars'),
		realpath(__DIR__ . '/../uploads/avatars'),
	];

	foreach ($allowedRoots as $root) {
		if ($root && strpos($full, $root) === 0) {
			return $full;
		}
	}

	return null;
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$statusMsg = '';
$statusType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id']) && in_array((string)$_POST['action'], ['reject_avatar', 'approve_avatar'], true)) {
	$targetUserId = (int)$_POST['user_id'];
	$oldAvatar = '';
	$action = (string)$_POST['action'];

	if ($targetUserId > 0 && ($findStmt = mysqli_prepare($db, 'SELECT COALESCE(avatar,\'\') AS avatar FROM users WHERE id = ? LIMIT 1'))) {
		mysqli_stmt_bind_param($findStmt, 'i', $targetUserId);
		mysqli_stmt_execute($findStmt);
		$findRes = mysqli_stmt_get_result($findStmt);
		if ($findRes && ($findRow = mysqli_fetch_assoc($findRes))) {
			$oldAvatar = trim((string)($findRow['avatar'] ?? ''));
		}
		if ($findRes) {
			mysqli_free_result($findRes);
		}
		mysqli_stmt_close($findStmt);
	}

	if ($targetUserId <= 0) {
		$statusMsg = 'Invalid user.';
		$statusType = 'error';
	} elseif ($oldAvatar === '' && $action === 'approve_avatar') {
		$statusMsg = 'No avatar found to approve.';
		$statusType = 'error';
	} elseif ($oldAvatar === '' && $action === 'reject_avatar') {
		$statusMsg = 'No avatar found to reject.';
		$statusType = 'error';
	} elseif ($action === 'approve_avatar' && ($updStmt = mysqli_prepare($db, "UPDATE users SET avatar_status = 'approved' WHERE id = ? LIMIT 1"))) {
		mysqli_stmt_bind_param($updStmt, 'i', $targetUserId);
		$ok = mysqli_stmt_execute($updStmt);
		mysqli_stmt_close($updStmt);

		if ($ok) {
			header('Location: ./view-profile-picture.php?user_id=' . $targetUserId . '&approved=1');
			exit;
		}

		$statusMsg = 'Failed to approve avatar.';
		$statusType = 'error';
	} elseif ($action === 'reject_avatar' && ($updStmt = mysqli_prepare($db, "UPDATE users SET avatar = NULL, avatar_status = 'rejected' WHERE id = ? LIMIT 1"))) {
		mysqli_stmt_bind_param($updStmt, 'i', $targetUserId);
		$ok = mysqli_stmt_execute($updStmt);
		mysqli_stmt_close($updStmt);

		if ($ok) {
			$storedPath = avatar_storage_path($oldAvatar);
			if ($storedPath && is_file($storedPath)) {
				@unlink($storedPath);
			}
			header('Location: ./view-profile-picture.php?user_id=' . $targetUserId . '&rejected=1');
			exit;
		}

		$statusMsg = 'Failed to reject avatar.';
		$statusType = 'error';
	} else {
		$statusMsg = 'Failed to update avatar moderation status.';
		$statusType = 'error';
	}
}

if (isset($_GET['rejected']) && (int)$_GET['rejected'] === 1) {
	$statusMsg = 'Profile picture rejected successfully.';
	$statusType = 'success';
}
if (isset($_GET['approved']) && (int)$_GET['approved'] === 1) {
	$statusMsg = 'Profile picture approved successfully.';
	$statusType = 'success';
}

$user = null;

if ($userId > 0) {
	if ($stmt = mysqli_prepare($db, "
		SELECT id,
		       CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) AS name,
		       COALESCE(username, '') AS username,
		       COALESCE(email, '') AS email,
		       COALESCE(mobile, '') AS mobile,
		       COALESCE(avatar, '') AS avatar,
		       LOWER(COALESCE(avatar_status, CASE WHEN COALESCE(avatar, '') <> '' THEN 'approved' ELSE 'none' END)) AS avatar_status
		FROM users
		WHERE id = ?
		LIMIT 1
	")) {
		mysqli_stmt_bind_param($stmt, 'i', $userId);
		mysqli_stmt_execute($stmt);
		$res = mysqli_stmt_get_result($stmt);
		$user = $res ? mysqli_fetch_assoc($res) : null;
		if ($res) {
			mysqli_free_result($res);
		}
		mysqli_stmt_close($stmt);
	}
}

if ($user) {
	$name = trim((string)($user['name'] ?? ''));
	if ($name === '') {
		$name = 'User #' . (int)$user['id'];
	}
	$user['name'] = $name;
	$user['avatar_url'] = avatar_public_url((string)($user['avatar'] ?? ''));
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>View Profile Picture • ServisyoHub</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		* { box-sizing: border-box; }
		body {
			margin: 0;
			font-family: Montserrat, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
			background: linear-gradient(180deg, #f8fbfd, #edf5f9);
			color: #0f172a;
			padding: 24px;
		}
		.card {
			max-width: 860px;
			margin: 0 auto;
			background: #ffffff;
			border-radius: 18px;
			border: 1px solid rgba(15, 23, 42, .08);
			box-shadow: 0 18px 50px rgba(2, 6, 23, .10);
			padding: 20px;
		}
		h1 { margin: 0 0 12px; font-size: 1.25rem; }
		.meta {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 10px;
			margin-bottom: 16px;
		}
		.meta-item {
			background: #f8fafc;
			border: 1px solid rgba(15, 23, 42, .08);
			border-radius: 10px;
			padding: 10px;
			font-size: .9rem;
		}
		.meta-item strong {
			display: block;
			font-size: .8rem;
			color: #64748b;
			margin-bottom: 4px;
		}
		.image-wrap {
			display: grid;
			place-items: center;
			min-height: 260px;
			border-radius: 14px;
			border: 1px dashed rgba(15, 23, 42, .2);
			background: #f8fafc;
			overflow: hidden;
		}
		.image-wrap img {
			max-width: 100%;
			max-height: 70vh;
			display: block;
			object-fit: contain;
		}
		.muted { color: #64748b; font-weight: 700; }
		.actions {
			margin-top: 14px;
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			justify-content: flex-end;
		}
		.btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 10px 12px;
			border-radius: 10px;
			border: 1px solid #cbd5e1;
			background: #fff;
			color: #0f172a;
			font-weight: 800;
			text-decoration: none;
			cursor: pointer;
		}
		.btn.approve {
			background: #22c55e;
			border-color: #22c55e;
			color: #fff;
		}
		.btn.reject {
			background: #ef4444;
			border-color: #ef4444;
			color: #fff;
		}
		.status-pill {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 6px 10px;
			border-radius: 999px;
			font-size: .78rem;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: .03em;
		}
		.status-pending { background: #fef3c7; color: #92400e; }
		.status-approved { background: #dcfce7; color: #166534; }
		.status-rejected { background: #fee2e2; color: #991b1b; }
		.status-none { background: #e2e8f0; color: #334155; }
		.actions form { margin: 0; }
		.alert {
			margin: 0 0 14px;
			padding: 10px 12px;
			border-radius: 10px;
			font-weight: 700;
			font-size: .9rem;
		}
		.alert-success {
			background: #dcfce7;
			color: #166534;
			border: 1px solid #86efac;
		}
		.alert-error {
			background: #fee2e2;
			color: #991b1b;
			border: 1px solid #fca5a5;
		}
		@media (max-width: 680px) {
			.meta { grid-template-columns: 1fr; }
		}
	</style>
</head>
<body>
	<div class="card">
		<?php if ($statusMsg !== ''): ?>
			<p class="alert <?php echo $statusType === 'success' ? 'alert-success' : 'alert-error'; ?>"><?php echo h($statusMsg); ?></p>
		<?php endif; ?>

		<?php if (!$user): ?>
			<h1>Profile Picture Not Found</h1>
			<p class="muted">The selected user does not exist or cannot be loaded.</p>
			<div class="actions"><a class="btn" href="./manage-users.php">Back to Manage Users</a></div>
		<?php else: ?>
			<h1>Profile Picture Review</h1>
			<div class="meta">
				<div class="meta-item"><strong>User</strong><?php echo h((string)$user['name']); ?></div>
				<div class="meta-item"><strong>User ID</strong><?php echo (int)$user['id']; ?></div>
				<div class="meta-item"><strong>Avatar Status</strong>
					<?php $avatarStatus = (string)($user['avatar_status'] ?? 'none'); ?>
					<?php if ($avatarStatus === 'approved'): ?>
						<span class="status-pill status-approved">Approved</span>
					<?php elseif ($avatarStatus === 'rejected'): ?>
						<span class="status-pill status-rejected">Rejected</span>
					<?php elseif ($avatarStatus === 'pending'): ?>
						<span class="status-pill status-pending">Pending</span>
					<?php else: ?>
						<span class="status-pill status-none">None</span>
					<?php endif; ?>
				</div>
				<div class="meta-item"><strong>Email</strong><?php echo h((string)($user['email'] ?: 'N/A')); ?></div>
				<div class="meta-item"><strong>Mobile</strong><?php echo h((string)($user['mobile'] ?: 'N/A')); ?></div>
				<div class="meta-item" style="grid-column: 1 / -1;"><strong>File</strong><?php echo h((string)($user['avatar'] ?: 'No file uploaded')); ?></div>
			</div>
			<div class="image-wrap">
				<?php if (!empty($user['avatar_url'])): ?>
					<img src="<?php echo h((string)$user['avatar_url']); ?>" alt="<?php echo h((string)$user['name']); ?> profile picture">
				<?php else: ?>
					<p class="muted">No valid profile picture file available for this user.</p>
				<?php endif; ?>
			</div>
			<div class="actions">
				<a class="btn" href="./manage-users.php">Back to Manage Users</a>
				<?php if (!empty($user['avatar_url'])): ?>
					<?php if (($user['avatar_status'] ?? '') !== 'approved'): ?>
						<form method="post" onsubmit="return confirm('Approve this profile picture?');">
							<input type="hidden" name="action" value="approve_avatar">
							<input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
							<button type="submit" class="btn approve">Approve Picture</button>
						</form>
					<?php endif; ?>
					<form method="post" onsubmit="return confirm('Reject this profile picture? This will remove it from the user profile across the site.');">
						<input type="hidden" name="action" value="reject_avatar">
						<input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
						<button type="submit" class="btn reject">Reject Picture</button>
					</form>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</body>
</html>
