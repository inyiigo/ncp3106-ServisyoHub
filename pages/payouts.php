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

/* Handle actions: request, mark_received, cancel */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_logged_in && $dbAvailable) {
	$action = $_POST['action'] ?? '';

	if ($action === 'request') {
		$amount = trim($_POST['amount'] ?? '');
		$method = trim($_POST['method'] ?? '');
		$notes  = trim($_POST['notes'] ?? '');
		if ($amount === '' || !is_numeric($amount) || (float)$amount <= 0) $errors[] = 'Enter a valid payout amount.';
		if ($method === '') $errors[] = 'Select a payout method.';
		if (empty($errors)) {
			$sql = "INSERT INTO payouts (user_id, amount, method, notes, status, requested_at) VALUES (?, ?, ?, ?, 'pending', NOW())";
			if ($stmt = mysqli_prepare($mysqli, $sql)) {
				$amt = (float)$amount;
				mysqli_stmt_bind_param($stmt, 'idss', $user_id, $amt, $method, $notes);
				if (mysqli_stmt_execute($stmt)) $success = 'Payout request submitted.';
				else $errors[] = 'Unable to submit request.';
				mysqli_stmt_close($stmt);
			} else {
				$errors[] = 'Database error.';
			}
		}
	}

	if ($action === 'mark_received' && isset($_POST['payout_id'])) {
		$pid = intval($_POST['payout_id']);
		$sql = "UPDATE payouts SET status = 'received', processed_at = IFNULL(processed_at, NOW()) WHERE id = ? AND user_id = ? AND status = 'processed'";
		if ($stmt = mysqli_prepare($mysqli, $sql)) {
			mysqli_stmt_bind_param($stmt, 'ii', $pid, $user_id);
			if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) $success = 'Payout marked as received.';
			else $errors[] = 'Cannot mark as received.';
			mysqli_stmt_close($stmt);
		} else $errors[] = 'Database error.';
	}

	if ($action === 'cancel' && isset($_POST['payout_id'])) {
		$pid = intval($_POST['payout_id']);
		$sql = "UPDATE payouts SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'";
		if ($stmt = mysqli_prepare($mysqli, $sql)) {
			mysqli_stmt_bind_param($stmt, 'ii', $pid, $user_id);
			if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) $success = 'Payout cancelled.';
			else $errors[] = 'Cannot cancel this payout.';
			mysqli_stmt_close($stmt);
		} else $errors[] = 'Database error.';
	}
}

/* Fetch payouts for this user (graceful if table missing) */
$payouts = [];
if (!$not_logged_in && $dbAvailable) {
	$sql = "SELECT id, amount, method, COALESCE(status,'pending') AS status,
	               requested_at, processed_at, COALESCE(reference,'') AS reference,
	               COALESCE(notes,'') AS notes
	        FROM payouts
	        WHERE user_id = ?
	        ORDER BY COALESCE(processed_at, requested_at) DESC, id DESC";
	if ($stmt = mysqli_prepare($mysqli, $sql)) {
		mysqli_stmt_bind_param($stmt, 'i', $user_id);
		if (mysqli_stmt_execute($stmt)) {
			$res = mysqli_stmt_get_result($stmt);
			if ($res) {
				while ($row = mysqli_fetch_assoc($res)) $payouts[] = $row;
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
<title>Payouts • ServisyoHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* page tweaks using site tokens */
.page-wrap { max-width: 980px; margin: 24px auto; padding: 18px; position: relative; z-index: 1; }
.header-row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.header-row h2 { margin:0; }
.note { color: var(--muted); }

/* form and table styling */
.form-inline { display:flex; gap:8px; align-items:center; flex-wrap: wrap; }
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
				<h2>Payouts</h2>
				<div class="note">
					<?php
					if ($not_logged_in) echo 'You are not logged in. Sign in to manage payouts.';
					elseif (!$dbAvailable) echo 'Database unavailable: ' . e($lastConnError);
					else echo 'Request and track your withdrawals.';
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
			<h3 style="margin-top:0">Request Payout</h3>
			<?php if ($not_logged_in): ?>
				<div class="note">Log in to request a payout.</div>
			<?php elseif (!$dbAvailable): ?>
				<div class="note">Cannot submit while the database is unavailable.</div>
			<?php else: ?>
			<form method="post" class="form-inline">
				<input type="hidden" name="action" value="request">
				<input class="input-sm" name="amount" placeholder="Amount" required inputmode="decimal" style="width:120px">
				<select class="input-sm" name="method" required>
					<option value="" disabled selected>Select method</option>
					<option value="Bank Transfer">Bank Transfer</option>
					<option value="GCash">GCash</option>
					<option value="PayMaya">PayMaya</option>
				</select>
				<input class="input-sm" name="notes" placeholder="Notes (optional)" style="min-width:220px">
				<button class="btn-sm btn-primary" type="submit">Request</button>
			</form>
			<?php endif; ?>
		</div>

		<div class="form-card glass-card" style="margin-top:14px;">
			<h3 style="margin-top:0">Payout History</h3>
			<?php if ($not_logged_in || !$dbAvailable): ?>
				<div class="note">No payouts to display.</div>
			<?php elseif (empty($payouts)): ?>
				<div class="note">You have no payout records yet.</div>
			<?php else: ?>
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>Requested</th>
							<th>Amount</th>
							<th>Method</th>
							<th>Status</th>
							<th>Processed</th>
							<th>Ref</th>
							<th style="width:210px">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($payouts as $p): ?>
							<?php
								$badge = 'badge-pending';
								$st = strtolower($p['status']);
								if ($st === 'processed') $badge = 'badge-processed';
								elseif ($st === 'received') $badge = 'badge-received';
								elseif (in_array($st, ['cancelled','rejected'], true)) $badge = 'badge-cancelled';
							?>
							<tr>
								<td><?php echo e($p['id']); ?></td>
								<td><?php echo e($p['requested_at'] ? date('M d, Y g:i A', strtotime($p['requested_at'])) : '—'); ?></td>
								<td>₱<?php echo e(number_format((float)$p['amount'],2)); ?></td>
								<td><?php echo e($p['method']); ?></td>
								<td><span class="badge <?php echo $badge; ?>"><?php echo e(ucwords($p['status'])); ?></span></td>
								<td><?php echo e($p['processed_at'] ? date('M d, Y g:i A', strtotime($p['processed_at'])) : '—'); ?></td>
								<td title="<?php echo e($p['reference']); ?>"><?php echo e($p['reference'] ?: '—'); ?></td>
								<td>
									<?php if ($st === 'pending'): ?>
										<form method="post" style="display:inline" onsubmit="return confirm('Cancel this payout request?');">
											<input type="hidden" name="action" value="cancel">
											<input type="hidden" name="payout_id" value="<?php echo e($p['id']); ?>">
											<button class="btn-sm btn-ghost" type="submit">Cancel</button>
										</form>
									<?php endif; ?>

									<?php if ($st === 'processed'): ?>
										<form method="post" style="display:inline">
											<input type="hidden" name="action" value="mark_received">
											<input type="hidden" name="payout_id" value="<?php echo e($p['id']); ?>">
											<button class="btn-sm btn-primary" type="submit">Mark Received</button>
										</form>
									<?php endif; ?>
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
