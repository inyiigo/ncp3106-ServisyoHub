<?php
session_start();

/* Safe DB connection (tries config then localhost fallback, no fatal errors) */
$configPath = __DIR__ . '/../includes/config.php';
$mysqli = null;
$dbAvailable = false;
$lastConnError = '';

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
		else { $lastConnError = mysqli_connect_error() ?: 'Connection failed'; if ($conn) { @mysqli_close($conn); } }
	} catch (Throwable $ex) {
		$lastConnError = $ex->getMessage();
	} finally {
		mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);
	}
}

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$errors = [];
$success = '';
$not_logged_in = empty($_SESSION['user_id']);
$user_id = $not_logged_in ? 0 : intval($_SESSION['user_id']);

/* Handle upload/delete */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_logged_in && $dbAvailable) {
	$action = $_POST['action'] ?? '';

	if ($action === 'upload') {
		$title = trim($_POST['title'] ?? '');
		if (empty($_FILES['doc']) || $_FILES['doc']['error'] === UPLOAD_ERR_NO_FILE) {
			$errors[] = 'Please select a file to upload.';
		} else {
			$f = $_FILES['doc'];
			if ($f['error'] !== UPLOAD_ERR_OK) {
				$errors[] = 'Upload error.';
			} else {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $f['tmp_name']);
				finfo_close($finfo);

				$allowed = [
					'application/pdf',
					'image/png', 'image/jpeg', 'image/webp',
					'application/msword',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
				];
				$max = 5 * 1024 * 1024; // 5MB

				if (!in_array($mime, $allowed, true)) {
					$errors[] = 'Unsupported file type.';
				} elseif ($f['size'] > $max) {
					$errors[] = 'File exceeds 5MB.';
				} else {
					$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
					if ($ext === '') {
						// map by mime
						$ext = $mime === 'application/pdf' ? 'pdf' :
							($mime === 'image/png' ? 'png' :
							($mime === 'image/webp' ? 'webp' :
							($mime === 'image/jpeg' ? 'jpg' :
							($mime === 'application/msword' ? 'doc' :
							($mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ? 'docx' : 'bin')))));
					}
					$uploadDir = __DIR__ . '/../assets/uploads/docs';
					if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
					$fname = 'doc_u' . $user_id . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
					$dest = $uploadDir . '/' . $fname;

					if (move_uploaded_file($f['tmp_name'], $dest)) {
						$relPath = 'assets/uploads/docs/' . $fname;
						$docName = $title !== '' ? $title : basename($f['name']);
						$type = $mime;
						$size = (int)$f['size'];

						// Insert metadata (ignore error silently if table missing)
						if ($stmt = mysqli_prepare($mysqli, "INSERT INTO documents (user_id, name, path, type, size, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())")) {
							mysqli_stmt_bind_param($stmt, 'isssi', $user_id, $docName, $relPath, $type, $size);
							if (mysqli_stmt_execute($stmt)) $success = 'Document uploaded.';
							else $errors[] = 'Failed to record document.';
							mysqli_stmt_close($stmt);
						} else {
							// fallback raw insert
							$sql = "INSERT INTO documents (user_id, name, path, type, size, uploaded_at) VALUES (" .
								intval($user_id) . ", '" . mysqli_real_escape_string($mysqli, $docName) . "', '" .
								mysqli_real_escape_string($mysqli, $relPath) . "', '" . mysqli_real_escape_string($mysqli, $type) .
								"', " . $size . ", NOW())";
							if (@mysqli_query($mysqli, $sql)) $success = 'Document uploaded.';
						}
					} else {
						$errors[] = 'Failed to save file.';
					}
				}
			}
		}
	}

	if ($action === 'delete' && isset($_POST['doc_id'])) {
		$doc_id = intval($_POST['doc_id']);
		$path = '';
		if ($stmt = mysqli_prepare($mysqli, "SELECT path FROM documents WHERE id = ? AND user_id = ?")) {
			mysqli_stmt_bind_param($stmt, 'ii', $doc_id, $user_id);
			if (mysqli_stmt_execute($stmt)) {
				$res = mysqli_stmt_get_result($stmt);
				if ($res && $row = mysqli_fetch_assoc($res)) $path = $row['path'];
				if ($res) mysqli_free_result($res);
			}
			mysqli_stmt_close($stmt);
		}
		if ($path !== '') {
			$abs = __DIR__ . '/../' . ltrim($path, '/');
			if (is_file($abs)) @unlink($abs);
			if ($stmt = mysqli_prepare($mysqli, "DELETE FROM documents WHERE id = ? AND user_id = ?")) {
				mysqli_stmt_bind_param($stmt, 'ii', $doc_id, $user_id);
				if (mysqli_stmt_execute($stmt)) $success = 'Document deleted.';
				mysqli_stmt_close($stmt);
			} else {
				@mysqli_query($mysqli, "DELETE FROM documents WHERE id = " . $doc_id . " AND user_id = " . $user_id);
				$success = 'Document deleted.';
			}
		} else {
			$errors[] = 'Document not found.';
		}
	}
}

/* Fetch user's documents */
$docs = [];
if (!$not_logged_in && $dbAvailable) {
	$sql = "SELECT id, name, path, type, size, uploaded_at FROM documents WHERE user_id = ? ORDER BY uploaded_at DESC, id DESC";
	if ($stmt = mysqli_prepare($mysqli, $sql)) {
		mysqli_stmt_bind_param($stmt, 'i', $user_id);
		if (mysqli_stmt_execute($stmt)) {
			$res = mysqli_stmt_get_result($stmt);
			if ($res) {
				while ($row = mysqli_fetch_assoc($res)) $docs[] = $row;
				mysqli_free_result($res);
			}
		}
		mysqli_stmt_close($stmt);
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
/* page tweaks using site tokens */
.page-wrap { max-width: 980px; margin: 24px auto; padding: 18px; position: relative; z-index: 1; }
.header-row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.header-row h2 { margin:0; }
.note { color: var(--muted); }

/* upload form */
.form-inline { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.input-sm { padding:8px 10px; border-radius:8px; border:1px solid var(--line); }
.btn-sm { padding:8px 10px; border-radius:8px; border:none; cursor:pointer; }
.btn-primary { background: var(--pal-4); color:#fff; }
.btn-ghost { background: transparent; border: 1px solid var(--line); color: var(--text); }
.btn-danger { background:#ef4444; color:#fff; }

/* table */
.table { width:100%; border-collapse:collapse; margin-top:12px; }
.table th, .table td { padding:10px 12px; border-bottom:1px solid rgba(255,255,255,.2); background:transparent; color: #fff; }
.table th { font-weight: 800; }

/* Make form cards blue */
.form-card.glass-card {
	background: #0078a6 !important;
	color: #fff;
	border-radius: 16px;
	padding: 16px 20px;
	box-shadow: 0 8px 24px rgba(0,120,166,.24);
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
}
.form-card.glass-card h3 { color: #fff; }
.form-card.glass-card strong { color: #fff; }
.form-card.glass-card ul { color: #fff; }
.form-card.glass-card .note { color: rgba(255,255,255,.85); }
.form-card.glass-card small.note { color: rgba(255,255,255,.75); }

/* page override: white background */
body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

/* Blue bottom border on topbar */
.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }

/* Background logo - transparent and behind UI */
.bg-logo {
	position: fixed;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	width: 25%;
	max-width: 350px;
	opacity: 0.15;
	z-index: 0;
	pointer-events: none;
}
.bg-logo img {
	width: 100%;
	height: auto;
	display: block;
}

/* centered floating bottom navigation */
.dash-bottom-nav {
	position: fixed;
	left: 50%;
	right: auto;
	bottom: 16px;
	z-index: 1000;
	width: max-content;
	transform: translateX(-50%) scale(0.92);
	transform-origin: bottom center;
	transition: transform 180ms ease, box-shadow 180ms ease;
	border: 3px solid #0078a6;
	background: transparent;
}
.dash-bottom-nav:hover {
	transform: translateX(-50%) scale(1);
	box-shadow: 0 12px 28px rgba(2,6,23,.12);
}

/* bottom back button */
.bottom-box { position: fixed; right: 20px; bottom: 20px; z-index: 999; background: transparent; border: none; padding: 0; box-shadow: none; }
.back-box { 
	display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:10px; 
	background: #0078a6; color: #fff; text-decoration:none; font-weight:700; 
	border:2px solid color-mix(in srgb, #0078a6 80%, #0000); 
	transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease; 
	box-shadow: 0 6px 18px rgba(0,120,166,.24); 
}
.back-box:hover { 
	transform: translateY(-4px) scale(1.02); 
	box-shadow: 0 12px 28px rgba(0,120,166,.32); 
	background: #006a94; 
	border-color: color-mix(in srgb, #0078a6 60%, #0000); 
}
@media (max-width:520px){ .bottom-box{ left:12px; right:12px; bottom:14px; display:flex; justify-content:center; } .back-box{ width:100%; justify-content:center; } }
</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="ServisyoHub" class="dash-brand-logo" onerror="this.style.display='none'">
		</div>
	</div>

	<div class="page-wrap">
		<div class="header-row">
			<div>
				<h2>Documents</h2>
				<div class="note">
					<?php
					if ($not_logged_in) echo 'You are not logged in. Sign in to manage your documents.';
					elseif (!$dbAvailable) echo 'Database unavailable: ' . e($lastConnError);
					else echo 'Upload and manage your documents securely.';
					?>
				</div>
			</div>
		</div>

		<?php if ($success): ?>
			<div class="form-card glass-card" style="margin-bottom:12px;"><strong><?php echo e($success); ?></strong></div>
		<?php endif; ?>
		<?php if (!empty($errors)): ?>
			<div class="form-card glass-card" style="margin-bottom:12px;">
				<ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $err) echo '<li>'.e($err).'</li>'; ?></ul>
			</div>
		<?php endif; ?>

		<div class="form-card glass-card">
			<h3 style="margin-top:0">Upload Document</h3>
			<?php if ($not_logged_in): ?>
				<div class="note">Log in to upload documents.</div>
			<?php elseif (!$dbAvailable): ?>
				<div class="note">Cannot upload while the database is unavailable.</div>
			<?php else: ?>
			<form method="post" enctype="multipart/form-data" class="form-inline">
				<input type="hidden" name="action" value="upload">
				<input class="input-sm" type="text" name="title" placeholder="Title (optional)" style="min-width:220px">
				<input class="input-sm" type="file" name="doc" required>
				<button class="btn-sm btn-primary" type="submit">Upload</button>
			</form>
			<small class="note">Allowed: PDF, JPG, PNG, WEBP, DOC, DOCX up to 5MB.</small>
			<?php endif; ?>
		</div>

		<div class="form-card glass-card" style="margin-top:14px;">
			<h3 style="margin-top:0">Your Documents</h3>
			<?php if ($not_logged_in || !$dbAvailable): ?>
				<div class="note">No documents to display.</div>
			<?php elseif (empty($docs)): ?>
				<div class="note">You have not uploaded any documents yet.</div>
			<?php else: ?>
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>Title</th>
							<th>Type</th>
							<th>Size</th>
							<th>Uploaded</th>
							<th style="width:200px">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($docs as $d): ?>
							<tr>
								<td><?php echo e($d['id']); ?></td>
								<td title="<?php echo e($d['name']); ?>"><?php echo e($d['name']); ?></td>
								<td><?php echo e($d['type']); ?></td>
								<td><?php echo e(number_format((float)$d['size']/1024, 1)); ?> KB</td>
								<td><?php echo e($d['uploaded_at'] ? date('M d, Y g:i A', strtotime($d['uploaded_at'])) : '—'); ?></td>
								<td>
									<a class="btn-sm btn-ghost" href="<?php echo '../' . ltrim($d['path'],'/'); ?>" target="_blank" rel="noopener">Download</a>
									<form method="post" style="display:inline" onsubmit="return confirm('Delete this document?');">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="doc_id" value="<?php echo e($d['id']); ?>">
										<button class="btn-sm btn-danger" type="submit">Delete</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="./jobs-profile.php" class="back-box" title="Back to profile">← Back to profile</a>
	</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./home-jobs.php" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
		</a>
		<a href="./my-jobs.php" aria-label="My Jobs">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Jobs</span>
		</a>
		<a href="./jobs-profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
</body>
</html>
