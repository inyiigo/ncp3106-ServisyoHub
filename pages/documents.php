<?php
session_start();

if (empty($_SESSION['user_id'])) {
	header('Location: ./login.php');
	exit;
}

require_once __DIR__ . '/../config/db_connect.php';

$db = $conn ?? ($mysqli ?? null);
if (!$db instanceof mysqli) {
	http_response_code(500);
	exit('Database connection unavailable.');
}

function h(?string $value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function db_has_column(mysqli $db, string $table, string $column): bool {
	$table = mysqli_real_escape_string($db, $table);
	$column = mysqli_real_escape_string($db, $column);
	$res = @mysqli_query($db, "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
	if (!$res) {
		return false;
	}
	$exists = @mysqli_num_rows($res) > 0;
	@mysqli_free_result($res);
	return $exists;
}

function format_size(int $bytes): string {
	if ($bytes < 1024) return $bytes . ' B';
	if ($bytes < 1024 * 1024) return number_format($bytes / 1024, 1) . ' KB';
	return number_format($bytes / (1024 * 1024), 2) . ' MB';
}

$userId = (int)$_SESSION['user_id'];
$isAdmin = false;
if ($stmt = mysqli_prepare($db, 'SELECT role FROM users WHERE id = ?')) {
	mysqli_stmt_bind_param($stmt, 'i', $userId);
	mysqli_stmt_execute($stmt);
	$res = mysqli_stmt_get_result($stmt);
	$row = $res ? mysqli_fetch_assoc($res) : null;
	$role = strtolower((string)($row['role'] ?? 'user'));
	$isAdmin = $role === 'admin';
	mysqli_stmt_close($stmt);
}

$files = [];
$imageColumn = '';
if (db_has_column($db, 'jobs', 'image_path')) {
	$imageColumn = 'image_path';
} elseif (db_has_column($db, 'jobs', 'task_image')) {
	$imageColumn = 'task_image';
}
$missingImageColumn = $imageColumn === '';

if (!$missingImageColumn) {
	if ($isAdmin) {
		$sql = "
			SELECT
				j.id,
				j.user_id,
				j.title,
				j.{$imageColumn} AS image_path,
				j.posted_at,
				COALESCE(u.username, TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), CONCAT('User #', j.user_id)) AS owner_name
			FROM jobs j
			LEFT JOIN users u ON u.id = j.user_id
			WHERE j.{$imageColumn} IS NOT NULL AND j.{$imageColumn} <> ''
			ORDER BY j.posted_at DESC, j.id DESC
		";
		$res = @mysqli_query($db, $sql);
		if ($res) {
			while ($row = mysqli_fetch_assoc($res)) {
				$files[] = $row;
			}
			mysqli_free_result($res);
		}
	} else {
		$sql = "
			SELECT
				j.id,
				j.user_id,
				j.title,
				j.{$imageColumn} AS image_path,
				j.posted_at,
				'You' AS owner_name
			FROM jobs j
			WHERE j.user_id = ?
				AND j.{$imageColumn} IS NOT NULL
				AND j.{$imageColumn} <> ''
			ORDER BY j.posted_at DESC, j.id DESC
		";
		if ($stmt = mysqli_prepare($db, $sql)) {
			mysqli_stmt_bind_param($stmt, 'i', $userId);
			mysqli_stmt_execute($stmt);
			$res = mysqli_stmt_get_result($stmt);
			if ($res) {
				while ($row = mysqli_fetch_assoc($res)) {
					$files[] = $row;
				}
				mysqli_free_result($res);
			}
			mysqli_stmt_close($stmt);
		}
	}
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Documents • ServisyoHub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">
	<style>
		:root {
			--brand: #0078a6;
			--ink: #0f172a;
			--muted: #64748b;
			--line: rgba(15, 23, 42, .10);
			--panel: rgba(255, 255, 255, .9);
		}
		body {
			margin: 0;
			color: var(--ink);
			background: linear-gradient(180deg, #f6fbfe 0%, #edf7fb 100%);
			font-family: Montserrat, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
		}
		.page {
			max-width: 1100px;
			margin: 0 auto;
			padding: 24px 16px 38px;
		}
		.hero-card {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 24px;
			min-height: 220px;
			padding: 28px 30px;
			margin-bottom: 16px;
			border-radius: 24px;
			background: linear-gradient(135deg, #0078a6 0%, #2f8fbb 100%);
			box-shadow: 0 18px 50px rgba(2, 6, 23, .14);
			overflow: hidden;
			position: relative;
		}
		.hero-content {
			position: relative;
			z-index: 1;
			max-width: 720px;
		}
		.hero-kicker {
			margin: 0 0 12px;
			font-size: .82rem;
			font-weight: 800;
			letter-spacing: .16em;
			text-transform: uppercase;
			color: rgba(255, 255, 255, .9);
		}
		.hero-title {
			margin: 0;
			font-size: clamp(1.8rem, 3vw, 3rem);
			line-height: 1.05;
			font-weight: 900;
			color: #fff;
		}
		.hero-copy {
			margin: 14px 0 0;
			max-width: 52ch;
			font-size: 1rem;
			line-height: 1.6;
			color: rgba(255, 255, 255, .92);
		}
		.hero-right {
			position: relative;
			z-index: 1;
			flex: 0 0 auto;
			display: flex;
			align-items: center;
			justify-content: flex-end;
		}
		.hero-card::before,
		.hero-card::after {
			content: '';
			position: absolute;
			border-radius: 999px;
			background: rgba(255, 255, 255, .12);
			pointer-events: none;
		}
		.hero-card::before {
			width: 180px;
			height: 180px;
			top: -50px;
			right: -45px;
		}
		.hero-card::after {
			width: 120px;
			height: 120px;
			left: -35px;
			bottom: -30px;
		}
		.hero-logo {
			width: min(160px, 22vw);
			height: auto;
			display: block;
			position: relative;
			z-index: 1;
		}
		.head {
			display: flex;
			justify-content: flex-start;
			align-items: center;
			gap: 12px;
			margin-bottom: 16px;
		}
		.head h1 {
			margin: 0;
			font-size: clamp(1.3rem, 2.3vw, 2rem);
		}
		.head p {
			margin: 8px 0 0;
			color: var(--muted);
		}
		.back-link {
			display: inline-flex;
			align-items: center;
			padding: 9px 12px;
			border-radius: 10px;
			background: var(--brand);
			color: #fff;
			font-weight: 700;
			text-decoration: none;
		}
		.panel {
			background: var(--panel);
			backdrop-filter: blur(8px);
			border: 1px solid var(--line);
			border-radius: 16px;
			padding: 14px;
			box-shadow: 0 14px 36px rgba(2, 6, 23, .08);
		}
		.notice {
			padding: 12px;
			border-radius: 12px;
			background: #fff7ed;
			border: 1px solid #fed7aa;
			color: #9a3412;
			margin-bottom: 12px;
		}
		.table-wrap {
			overflow: auto;
		}
		table {
			width: 100%;
			border-collapse: collapse;
			min-width: 760px;
		}
		th,
		td {
			padding: 10px;
			border-bottom: 1px solid var(--line);
			text-align: left;
			vertical-align: middle;
		}
		th {
			font-size: .78rem;
			color: var(--muted);
			letter-spacing: .08em;
			text-transform: uppercase;
		}
		.preview {
			width: 64px;
			height: 64px;
			border-radius: 10px;
			object-fit: cover;
			border: 1px solid var(--line);
			background: #f8fafc;
		}
		.job-title {
			font-weight: 700;
			color: #000;
		}
		.meta {
			font-size: .86rem;
			color: var(--muted);
			margin-top: 4px;
		}
		.file-actions {
			display: flex;
			gap: 8px;
		}
		.file-actions a {
			display: inline-flex;
			align-items: center;
			padding: 7px 10px;
			border-radius: 9px;
			font-weight: 700;
			text-decoration: none;
			border: 1px solid var(--line);
			color: var(--ink);
			background: #fff;
		}
		.file-actions a.primary {
			background: var(--brand);
			color: #fff;
			border-color: var(--brand);
		}
		.empty {
			padding: 16px;
			border-radius: 12px;
			border: 1px dashed var(--line);
			color: var(--muted);
			background: rgba(255, 255, 255, .7);
		}
		@media (max-width: 820px) {
			.hero-card {
				flex-direction: column;
				align-items: flex-start;
			}
			.hero-right {
				width: 100%;
				justify-content: flex-start;
			}
			.hero-logo {
				width: min(140px, 44vw);
			}
			.head {
				flex-direction: column;
				align-items: flex-start;
			}
		}
	</style>
</head>
<body>
	<div class="page">
		<div class="hero-card" aria-label="Documents header">
			<div class="hero-content">
				<p class="hero-kicker">Documents</p>
				<h1 class="hero-title">Uploaded files from posts</h1>
				<p class="hero-copy">Files selected in Post Service Request are listed here<?php echo $isAdmin ? ' for all users.' : ' for your account.'; ?></p>
			</div>
			<div class="hero-right">
				<img class="hero-logo" src="../assets/images/job_logo.png" alt="Job logo">
			</div>
		</div>

		<div class="panel">
			<?php if ($missingImageColumn): ?>
				<div class="notice">No uploaded post files found yet. The jobs table has no image_path or task_image column.</div>
			<?php endif; ?>

			<?php if (empty($files)): ?>
				<div class="empty">No post uploads available yet.</div>
			<?php else: ?>
				<div class="table-wrap">
					<table>
						<thead>
							<tr>
								<th>Preview</th>
								<th>Post</th>
								<th>Owner</th>
								<th>Uploaded</th>
								<th>Size</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($files as $file): ?>
								<?php
								$rel = ltrim((string)$file['image_path'], '/');
								$abs = __DIR__ . '/../' . $rel;
								$href = '../' . $rel;
								$size = is_file($abs) ? format_size((int)filesize($abs)) : 'Missing';
								?>
								<tr>
									<td>
										<?php if (is_file($abs)): ?>
											<img class="preview" src="<?php echo h($href); ?>" alt="Post image">
										<?php else: ?>
											<div class="meta">Missing file</div>
										<?php endif; ?>
									</td>
									<td>
										<div class="job-title"><?php echo h((string)$file['title']); ?></div>
										<div class="meta">Job #<?php echo (int)$file['id']; ?></div>
									</td>
									<td><?php echo h((string)$file['owner_name']); ?></td>
									<td><?php echo h(date('M j, Y g:i A', strtotime((string)$file['posted_at']))); ?></td>
									<td><?php echo h($size); ?></td>
									<td>
										<div class="file-actions">
											<a class="primary" href="<?php echo h($href); ?>" target="_blank" rel="noopener">View</a>
											<a href="<?php echo h($isAdmin ? './post-approvals.php?job_id=' . (int)$file['id'] : './gawain-detail.php?id=' . (int)$file['id']); ?>">Manage post</a>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>
