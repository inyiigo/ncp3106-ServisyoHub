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

/* Handle actions: withdraw, delete */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_logged_in && $dbAvailable) {
	$action = $_POST['action'] ?? '';
	$app_id = isset($_POST['app_id']) ? intval($_POST['app_id']) : 0;

	if ($app_id > 0 && $action === 'withdraw') {
		if ($stmt = mysqli_prepare($mysqli, "UPDATE job_applications SET status = 'withdrawn', updated_at = NOW() WHERE id = ? AND user_id = ?")) {
			mysqli_stmt_bind_param($stmt, 'ii', $app_id, $user_id);
			if (mysqli_stmt_execute($stmt)) $success = 'Application withdrawn.';
			else $errors[] = 'Could not withdraw application.';
			mysqli_stmt_close($stmt);
		} else { $errors[] = 'Database error.'; }
	}

	if ($app_id > 0 && $action === 'delete') {
		if ($stmt = mysqli_prepare($mysqli, "DELETE FROM job_applications WHERE id = ? AND user_id = ? AND status IN ('withdrawn','rejected')")) {
			mysqli_stmt_bind_param($stmt, 'ii', $app_id, $user_id);
			if (mysqli_stmt_execute($stmt)) $success = 'Application deleted.';
			else $errors[] = 'Could not delete application.';
			mysqli_stmt_close($stmt);
		} else { $errors[] = 'Database error.'; }
	}
}

/* Fetch job applications for this user */
$applications = [];
if (!$not_logged_in && $dbAvailable) {
	$sql = "SELECT id,
	        COALESCE(profession, 'Application') AS profession,
	        COALESCE(status, 'pending') AS status,
	        COALESCE(updated_at, submitted_at) AS dt,
	        COALESCE(notes, '') AS notes
	        FROM job_applications
	        WHERE user_id = ?
	        ORDER BY dt DESC, id DESC";
	if ($stmt = mysqli_prepare($mysqli, $sql)) {
		mysqli_stmt_bind_param($stmt, 'i', $user_id);
		if (mysqli_stmt_execute($stmt)) {
			$res = mysqli_stmt_get_result($stmt);
			if ($res) {
				while ($row = mysqli_fetch_assoc($res)) $applications[] = $row;
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
<title>Job Applications • ServisyoHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* page tweaks using site tokens */
.page-wrap { max-width: 980px; margin: 24px auto; padding: 18px; position: relative; z-index: 1; }
.header-row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.header-row h2 { margin:0; }
.note { color: var(--muted); }

/* cards grid */
.grid { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 960px) { .grid { grid-template-columns: 1fr; } }

/* application card (glass look to match site) */
.app-card { display:grid; gap:8px; padding:14px; border-radius:14px;
	background: #0078a6; color: #fff; border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
	box-shadow: 0 8px 20px rgba(0,120,166,.24); }
.app-top { display:flex; align-items:center; justify-content:space-between; gap:10px; }
.app-title { font-weight:800; color: #fff; }
.app-sub { color: rgba(255,255,255,.85); font-size:.92rem; }
.badge { padding:6px 10px; border-radius:999px; font-weight:800; font-size:.82rem; }
.badge-pending { background:#fef3c7; color:#92400e; }
.badge-review { background:#e0e7ff; color:#3730a3; }
.badge-rejected { background:#fee2e2; color:#991b1b; }
.badge-withdrawn { background:#f1f5f9; color:#334155; }
.badge-hired { background:#dcfce7; color:#166534; }

/* actions */
.app-actions { display:flex; gap:8px; margin-top:6px; flex-wrap:wrap; }
.btn-ghost { background: #fff; border: 1px solid rgba(255,255,255,.3); color: #0078a6; padding:8px 10px; border-radius:10px; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; }
.btn-danger { background:#ef4444; color:#fff; padding:8px 10px; border-radius:10px; border:none; cursor:pointer; }
.btn-primary { background: #fff; color: #0078a6; padding:8px 10px; border-radius:10px; border:none; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; }

/* Make form cards blue */
.form-card.glass-card {
	background: #0078a6 !important;
	color: #fff;
	border-radius: 16px;
	padding: 16px 20px;
	box-shadow: 0 8px 24px rgba(0,120,166,.24);
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
}
.form-card.glass-card strong { color: #fff; }
.form-card.glass-card ul { color: #fff; }
.form-card.glass-card .note { color: rgba(255,255,255,.85); }
.app-card .note { color: rgba(255,255,255,.75) !important; }

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
				<h2>Job Applications</h2>
				<div class="note">
					<?php
					if ($not_logged_in) echo 'You are not logged in. Sign in to view your applications.';
					elseif (!$dbAvailable) echo 'Database unavailable: ' . e($lastConnError);
					else echo 'Track the status of your job applications.';
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

		<div class="grid">
			<?php if ($not_logged_in || !$dbAvailable): ?>
				<div class="form-card glass-card" style="grid-column:1/-1;">
					<p class="note">No applications to display.</p>
				</div>
			<?php else: ?>
				<?php if (empty($applications)): ?>
					<div class="form-card glass-card" style="grid-column:1/-1;">
						<p class="note">You haven’t submitted any applications yet.</p>
					</div>
				<?php else: ?>
					<?php foreach ($applications as $a): ?>
						<?php
							$status = strtolower($a['status']);
							$badgeClass = 'badge-pending';
							if (in_array($status, ['pending','submitted'], true)) $badgeClass = 'badge-pending';
							elseif (in_array($status, ['in review','review','under_review'], true)) $badgeClass = 'badge-review';
							elseif ($status === 'rejected') $badgeClass = 'badge-rejected';
							elseif ($status === 'withdrawn') $badgeClass = 'badge-withdrawn';
							elseif (in_array($status, ['hired','accepted'], true)) $badgeClass = 'badge-hired';
							$when = $a['dt'] ? date('M d, Y g:i A', strtotime($a['dt'])) : '—';
						?>
						<div class="app-card">
							<div class="app-top">
								<div class="app-title"><?php echo e($a['profession']); ?></div>
								<div class="badge <?php echo $badgeClass; ?>"><?php echo e(ucwords(str_replace('_',' ', $status))); ?></div>
							</div>
							<div class="app-sub"><?php echo e($when); ?></div>
							<?php if (!empty($a['notes'])): ?>
								<div class="note"><?php echo e($a['notes']); ?></div>
							<?php endif; ?>

							<div class="app-actions">
								<a class="btn-ghost" href="javascript:void(0)" title="View details">View</a>

								<?php if (!in_array($status, ['withdrawn','hired','accepted'], true)): ?>
									<form method="post" style="display:inline" onsubmit="return confirm('Withdraw this application?');">
										<input type="hidden" name="action" value="withdraw">
										<input type="hidden" name="app_id" value="<?php echo e($a['id']); ?>">
										<button class="btn-primary" type="submit">Withdraw</button>
									</form>
								<?php endif; ?>

								<?php if (in_array($status, ['withdrawn','rejected'], true)): ?>
									<form method="post" style="display:inline" onsubmit="return confirm('Delete this application? This cannot be undone.');">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="app_id" value="<?php echo e($a['id']); ?>">
										<button class="btn-danger" type="submit">Delete</button>
									</form>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
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
