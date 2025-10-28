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
function quick_count($mysqli, $sql){
	$cnt = 0;
	if (!$mysqli) return $cnt;
	$res = @mysqli_query($mysqli, $sql);
	if ($res) {
		$row = mysqli_fetch_row($res);
		if ($row) $cnt = (int)$row[0];
		mysqli_free_result($res);
	}
	return $cnt;
}

// Admin KPIs (graceful if tables missing)
$k_users = $dbAvailable ? quick_count($mysqli, "SELECT COUNT(*) FROM users") : 0;
$k_jobs  = $dbAvailable ? quick_count($mysqli, "SELECT COUNT(*) FROM jobs") : 0;
$k_apps  = $dbAvailable ? quick_count($mysqli, "SELECT COUNT(*) FROM job_applications") : 0;
$k_pay   = $dbAvailable ? quick_count($mysqli, "SELECT COUNT(*) FROM payments") : 0;

// Recent lists (graceful if tables missing)
$recentUsers = [];
if ($dbAvailable) {
	$res = @mysqli_query($mysqli, "SELECT id, COALESCE(username,'') AS username, COALESCE(email,'') AS email FROM users ORDER BY id DESC LIMIT 8");
	if ($res) { while ($row = mysqli_fetch_assoc($res)) $recentUsers[] = $row; mysqli_free_result($res); }
}
$recentJobs = [];
if ($dbAvailable) {
	$res = @mysqli_query($mysqli, "SELECT id, COALESCE(title,'Job') AS title, COALESCE(status,'pending') AS status FROM jobs ORDER BY id DESC LIMIT 8");
	if ($res) { while ($row = mysqli_fetch_assoc($res)) $recentJobs[] = $row; mysqli_free_result($res); }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin • ServisyoHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* page tweaks using site tokens */
.page-wrap { max-width: 1100px; margin: 24px auto; padding: 18px; }
.header-row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.header-row h2 { margin:0; }
.note { color: var(--muted); }

/* metrics grid */
.kpi { display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; }
@media (max-width: 960px){ .kpi { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 520px){ .kpi { grid-template-columns: 1fr; } }
.kpi-card { padding:14px; border-radius:14px; background: rgba(255,255,255,.35); border: 2px solid rgba(255,255,255,.6); box-shadow: 0 8px 20px rgba(2,6,23,.15); backdrop-filter: blur(4px); }
.kpi-label { color: var(--muted); font-weight:700; font-size:.9rem; }
.kpi-value { font-weight: 900; font-size: 1.6rem; }

/* lists */
.grid { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top:14px; }
@media (max-width: 960px){ .grid { grid-template-columns: 1fr; } }
.table { width:100%; border-collapse:collapse; }
.table th, .table td { padding:10px 12px; border-bottom:1px solid var(--line); background:transparent; }
.badge { padding:4px 8px; border-radius:999px; font-weight:800; font-size:.78rem; }
.badge-pending { background:#fef3c7; color:#92400e; }
.badge-inprogress { background:#dbeafe; color:#1e40af; }
.badge-completed { background:#dcfce7; color:#166534; }
.badge-cancelled { background:#fee2e2; color:#991b1b; }
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
				<h2>Admin Dashboard</h2>
				<div class="note">
					<?php
					if (!$dbAvailable) echo 'Database unavailable: ' . e($lastConnError);
					else echo 'Overview of users, jobs, and activity.';
					?>
				</div>
			</div>
		</div>

		<!-- KPIs -->
		<section class="kpi">
			<div class="kpi-card">
				<div class="kpi-label">Users</div>
				<div class="kpi-value"><?php echo number_format($k_users); ?></div>
			</div>
			<div class="kpi-card">
				<div class="kpi-label">Jobs</div>
				<div class="kpi-value"><?php echo number_format($k_jobs); ?></div>
			</div>
			<div class="kpi-card">
				<div class="kpi-label">Applications</div>
				<div class="kpi-value"><?php echo number_format($k_apps); ?></div>
			</div>
			<div class="kpi-card">
				<div class="kpi-label">Payments</div>
				<div class="kpi-value"><?php echo number_format($k_pay); ?></div>
			</div>
		</section>

		<!-- Recent lists -->
		<section class="grid">
			<div class="form-card glass-card">
				<h3 style="margin:0 0 8px;">Recent Users</h3>
				<?php if (!$dbAvailable): ?>
					<div class="note">Cannot load users.</div>
				<?php elseif (empty($recentUsers)): ?>
					<div class="note">No users to display.</div>
				<?php else: ?>
					<table class="table">
						<thead>
							<tr><th>#</th><th>Username</th><th>Email</th></tr>
						</thead>
						<tbody>
							<?php foreach ($recentUsers as $u): ?>
								<tr>
									<td><?php echo e($u['id']); ?></td>
									<td><?php echo e($u['username']); ?></td>
									<td><?php echo e($u['email']); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<div class="form-card glass-card">
				<h3 style="margin:0 0 8px;">Recent Jobs</h3>
				<?php if (!$dbAvailable): ?>
					<div class="note">Cannot load jobs.</div>
				<?php elseif (empty($recentJobs)): ?>
					<div class="note">No jobs to display.</div>
				<?php else: ?>
					<table class="table">
						<thead>
							<tr><th>#</th><th>Title</th><th>Status</th></tr>
						</thead>
						<tbody>
							<?php foreach ($recentJobs as $j): ?>
								<?php
									$st = strtolower($j['status']);
									$badge = $st==='completed' ? 'badge-completed' : ($st==='cancelled' ? 'badge-cancelled' : (($st==='in progress'||$st==='in_progress')?'badge-inprogress':'badge-pending'));
								?>
								<tr>
									<td><?php echo e($j['id']); ?></td>
									<td><?php echo e($j['title']); ?></td>
									<td><span class="badge <?php echo $badge; ?>"><?php echo e(ucwords(str_replace('_',' ', $st))); ?></span></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</section>
	</div>
</body>
</html>
