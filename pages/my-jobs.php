<?php
session_start();

// Safe DB connection (tries config then localhost fallback, no fatal errors)
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
	if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_OFF);
	try {
		$conn = @mysqli_connect($h,$u,$p,$n);
		if ($conn && !mysqli_connect_errno()) { $mysqli = $conn; $dbAvailable = true; break; }
		else { $lastConnError = mysqli_connect_error() ?: 'Connection failed'; if ($conn) { @mysqli_close($conn); } }
	} catch (Throwable $ex) {
		$lastConnError = $ex->getMessage();
	} finally {
		if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);
	}
}

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$errors = [];
$success = '';
$not_logged_in = empty($_SESSION['user_id']);
$user_id = $not_logged_in ? 0 : intval($_SESSION['user_id']);

// Handle actions (complete, cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_logged_in && $dbAvailable) {
	$action = $_POST['action'] ?? '';
	$job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;

	if ($job_id > 0 && in_array($action, ['done','cancel'], true)) {
		$status = $action === 'done' ? 'completed' : 'cancelled';
		$stmt = mysqli_prepare($mysqli, "UPDATE jobs SET status = ? WHERE id = ? AND user_id = ?");
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, 'sii', $status, $job_id, $user_id);
			if (mysqli_stmt_execute($stmt)) $success = $status === 'completed' ? 'Job marked as completed.' : 'Job cancelled.';
			else $errors[] = 'Update failed.';
			mysqli_stmt_close($stmt);
		} else {
			$errors[] = 'Database error.';
		}
	}
}

// Fetch jobs for this user
$jobs = [];
if (!$not_logged_in && $dbAvailable) {
	$sql = "SELECT j.id, COALESCE(j.title,'Service Job') AS title, COALESCE(j.status,'pending') AS status,
	        COALESCE(j.scheduled_at, j.created_at) AS dt, COALESCE(j.price,0) AS price,
	        COALESCE(p.name,'Assigned Pro') AS provider
	        FROM jobs j
	        LEFT JOIN pros p ON p.id = j.pro_id
	        WHERE j.user_id = ?
	        ORDER BY dt DESC, j.id DESC";
	if ($stmt = mysqli_prepare($mysqli, $sql)) {
		mysqli_stmt_bind_param($stmt, 'i', $user_id);
		if (mysqli_stmt_execute($stmt)) {
			$res = mysqli_stmt_get_result($stmt);
			if ($res) {
				while ($row = mysqli_fetch_assoc($res)) $jobs[] = $row;
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
<title>My Jobs • ServisyoHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* page tweaks using site tokens */
.page-wrap { max-width: 980px; margin: 24px auto; padding: 18px; }

.header-row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }

.header-row h2 { margin:0; color: #0f172a; }
.note { color: #64748b; }

/* job cards with blue background like home-jobs.php */
.grid { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 960px) { .grid { grid-template-columns: 1fr; } }

.job-card {
	display:grid; grid-template-columns: auto 1fr; gap:14px; align-items:center;
	padding: 20px 22px; border-radius: 16px;
	background: #0078a6; color: #fff;
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
	box-shadow: 0 8px 24px rgba(0,120,166,.24);
	transition: transform .15s ease, box-shadow .15s ease;
}
.job-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,120,166,.32); }

.job-badge { padding:6px 10px; border-radius:999px; font-weight:800; font-size:.82rem; width:max-content; }
.badge-pending { background: rgba(254,243,199,.95); color:#92400e; }
.badge-inprogress { background: rgba(219,234,254,.95); color:#1e40af; }
.badge-completed { background: rgba(220,252,231,.95); color:#166534; }
.badge-cancelled { background: rgba(254,226,226,.95); color:#991b1b; }

.job-title { font-weight: 800; color: #fff; font-size: 1.1rem; }
.job-sub { color: rgba(255,255,255,.85); font-size: .92rem; }
.job-meta { color: rgba(255,255,255,.75); font-size: .9rem; }

.job-actions { display:flex; gap:8px; margin-top:8px; flex-wrap: wrap; }
.btn-ghost {
	background: #fff; border: 1px solid rgba(255,255,255,.3); color: #0078a6;
	padding:8px 12px; border-radius:12px; cursor:pointer; text-decoration:none;
	display:inline-flex; align-items:center; font-weight:700;
	transition: transform .12s ease, box-shadow .12s ease;
}
.btn-ghost:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(255,255,255,.2); }

.btn-danger {
	background:#ef4444; color:#fff; padding:8px 12px; border-radius:12px; border:none;
	cursor:pointer; font-weight:700; transition: filter .12s ease, transform .12s ease;
}
.btn-danger:hover { filter: brightness(1.05); transform: translateY(-1px); }

.btn-primary {
	background: #fff; color: #0078a6; padding:8px 12px; border-radius:12px;
	border:none; cursor:pointer; text-decoration:none; display:inline-flex;
	align-items:center; font-weight:700; transition: transform .12s ease, box-shadow .12s ease;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(255,255,255,.2); }

/* page override: white background */
body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

/* Blue bottom border on topbar */
.dash-topbar { border-bottom: 3px solid #0078a6; }

/* centered floating bottom navigation (match home-services.php) */
.dash-bottom-nav {
	position: fixed;
	left: 50%;
	bottom: 16px;
	z-index: 1000;
	width: max-content;
	transform: translateX(-50%) scale(0.92);
	transform-origin: bottom center;
	transition: transform 180ms ease, box-shadow 180ms ease;
}
.dash-bottom-nav:hover {
	transform: translateX(-50%) scale(1);
	box-shadow: 0 12px 28px rgba(2,6,23,.12);
}

/* Entrance animation */
@media (prefers-reduced-motion: no-preference) {
	.grid { animation: fadeUp .4s ease both .15s; }
	@keyframes fadeUp { from { opacity:.0; transform: translateY(6px);} to { opacity:1; transform:none; } }
}

/* Make notification boxes blue with white text */
.form-card.glass-card {
	background: #0078a6;
	color: #fff;
	border-radius: 16px;
	padding: 16px 20px;
	box-shadow: 0 8px 24px rgba(0,120,166,.24);
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
}
.form-card.glass-card strong { color: #fff; }
.form-card.glass-card ul { color: #fff; }
/* Make note text white inside job cards */
.job-card .note { color: #fff !important; }
</style>
</head>
<body class="theme-profile-bg">
	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="ServisyoHub" class="dash-brand-logo" onerror="this.style.display='none'">
		</div>
	</div>

	<div class="page-wrap">
		<div class="header-row">
			<div>
				<h2>My Jobs</h2>
				<div class="note">
					<?php
					if ($not_logged_in) echo 'You are not logged in. Sign in to view your jobs.';
					elseif (!$dbAvailable) echo 'Database unavailable: ' . e($lastConnError);
					else echo 'Track your service requests and manage their status.';
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
				<div class="job-card" style="grid-column:1/-1; display:block;">
					<p class="note">No jobs to display.</p>
				</div>
			<?php else: ?>
				<?php if (empty($jobs)): ?>
					<div class="job-card" style="grid-column:1/-1; display:block;">
						<p class="note">You don't have any jobs yet.</p>
					</div>
				<?php else: ?>
					<?php foreach ($jobs as $j): ?>
						<?php
							$status = strtolower($j['status']);
							$badgeClass = $status === 'completed' ? 'badge-completed' :
								($status === 'cancelled' ? 'badge-cancelled' :
								(($status === 'in progress' || $status === 'in_progress') ? 'badge-inprogress' : 'badge-pending'));
							$when = $j['dt'] ? date('M d, Y g:i A', strtotime($j['dt'])) : '—';
						?>
						<div class="job-card">
							<div class="job-badge <?php echo $badgeClass; ?>"><?php echo e(ucwords(str_replace('_',' ', $status))); ?></div>
							<div>
								<div class="job-title"><?php echo e($j['title']); ?></div>
								<div class="job-sub">With <?php echo e($j['provider']); ?></div>
								<div class="job-meta"><?php echo e($when); ?> • ₱<?php echo e(number_format((float)$j['price'],2)); ?></div>

								<div class="job-actions">
									<a class="btn-ghost" href="javascript:void(0)" title="View details">View</a>

									<?php if (!in_array($status, ['completed','cancelled'], true)): ?>
										<form method="post" style="display:inline">
											<input type="hidden" name="action" value="done">
											<input type="hidden" name="job_id" value="<?php echo e($j['id']); ?>">
											<button class="btn-primary" type="submit">Mark Completed</button>
										</form>
										<form method="post" style="display:inline" onsubmit="return confirm('Cancel this job?');">
											<input type="hidden" name="action" value="cancel">
											<input type="hidden" name="job_id" value="<?php echo e($j['id']); ?>">
											<button class="btn-danger" type="submit">Cancel</button>
										</form>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./home-jobs.php" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
		</a>
		<a href="./my-jobs.php" class="active" aria-label="My Jobs">
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
