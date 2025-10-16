<?php
session_start();

// Minimal safe DB connection (tries config then localhost fallback)
$configPath = __DIR__ . '/../includes/config.php';
$mysqli = null;
$dbAvailable = false;
$lastConnError = '';

if (file_exists($configPath)) { require_once $configPath; }

// build attempts list
$attempts = [];
if (isset($db_host, $db_user, $db_pass, $db_name)) $attempts[] = [$db_host, $db_user, $db_pass, $db_name];
$attempts[] = ['localhost', 'root', '', 'servisyohub'];

// --- replaced connection loop: use mysqli_connect with exception-safe handling
$lastConnError = '';
$dbAvailable = false;
$mysqli = null;
foreach ($attempts as $creds) {
		list($h, $u, $p, $n) = $creds;

		// disable mysqli exceptions for the attempt if available
		if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_OFF);
		try {
			// suppress PHP warnings and attempt connection; catch any mysqli_sql_exception
			$conn = @mysqli_connect($h, $u, $p, $n);
			if ($conn && !mysqli_connect_errno()) {
				$mysqli = $conn;
				$dbAvailable = true;
				break;
			} else {
				$lastConnError = mysqli_connect_error() ?: 'Connection failed';
				if ($conn) { @mysqli_close($conn); }
			}
		} catch (mysqli_sql_exception $ex) {
			// record exception message but continue to next attempt
			$lastConnError = $ex->getMessage();
		} catch (Throwable $ex) {
			$lastConnError = $ex->getMessage();
		} finally {
			// restore default reporting behavior if available
			if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);
		}
}

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Basic auth guard note (optional): you may require login here.
// $user_id = $_SESSION['user_id'] ?? null;

$errors = [];
$success = '';

// Handle actions: add, toggle, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dbAvailable) {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $amount = trim($_POST['amount'] ?? '');
        $method = trim($_POST['method'] ?? '');
        $notes  = trim($_POST['notes'] ?? '');
        $date   = trim($_POST['date'] ?? date('Y-m-d'));
        if ($amount === '' || !is_numeric($amount)) $errors[] = 'Valid amount is required.';
        if ($method === '') $errors[] = 'Payment method is required.';
        if (empty($errors)) {
            // normalize values for binding
            $amount_f = (float)$amount;
            $method_s = $method;
            $notes_s  = $notes;
            $date_s   = $date;
            $status_s = 'pending';

            // Try prepared statement with string status (most common schema)
            $stmt = $mysqli->prepare("INSERT INTO payments (amount, method, notes, `date`, status) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('dssss', $amount_f, $method_s, $notes_s, $date_s, $status_s);
                if ($stmt->execute()) {
                    $success = 'Payment added.';
                } else {
                    $errors[] = 'Insert failed: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                // fallback: escaped simple query
                $sql = "INSERT INTO payments (amount, method, notes, `date`, status) VALUES ('".$mysqli->real_escape_string((string)$amount_f)."', '".$mysqli->real_escape_string($method_s)."', '".$mysqli->real_escape_string($notes_s)."', '".$mysqli->real_escape_string($date_s)."', '".$mysqli->real_escape_string($status_s)."')";
                if ($mysqli->query($sql)) $success = 'Payment added.';
                else $errors[] = 'Insert failed: '.$mysqli->error;
            }
        }
    } elseif ($action === 'toggle' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        // Flip status between paid/pending (adjust depending on schema)
        $r = $mysqli->query("SELECT status FROM payments WHERE id = ". $id ." LIMIT 1");
        if ($r && $row = $r->fetch_assoc()) {
            $new = ($row['status'] === 'paid') ? 'pending' : 'paid';
            if ($mysqli->query("UPDATE payments SET status = '".$mysqli->real_escape_string($new)."' WHERE id = ".$id)) $success = 'Payment status updated.';
            else $errors[] = 'Update failed: '.$mysqli->error;
        } else $errors[] = 'Payment not found.';
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        if ($mysqli->query("DELETE FROM payments WHERE id = ".$id)) $success = 'Payment deleted.';
        else $errors[] = 'Delete failed: '.$mysqli->error;
    }
}

// Fetch payments for listing
$payments = [];
if ($dbAvailable) {
    $res = $mysqli->query("SELECT id, amount, method, notes, `date`, status FROM payments ORDER BY `date` DESC, id DESC LIMIT 200");
    if ($res) {
        while ($row = $res->fetch_assoc()) $payments[] = $row;
        $res->free();
    } else {
        // table may not exist; show empty
        // $errors[] = 'Could not load payments: '.$mysqli->error;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manage Payments • ServisyoHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* small page overrides */
.page-wrap { max-width:980px; margin:24px auto; padding:18px; }
.header-row { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px; }
.col { display:flex; gap:8px; align-items:center; }
.table { width:100%; border-collapse:collapse; margin-top:12px; }
.table th, .table td { padding:10px 12px; border-bottom:1px solid var(--line); background:transparent; }
.status-paid { color: #065f46; font-weight:700; }
.status-pending { color: #b45309; font-weight:700; }
.form-inline { display:flex; gap:8px; align-items:center; }
.input-sm { padding:8px 10px; border-radius:8px; border:1px solid var(--line); }
.btn-sm { padding:8px 10px; border-radius:8px; border:none; cursor:pointer; }
.btn-danger { background:#ef4444; color:#fff; border:none; }
.btn-ghost { background:transparent; border:1px solid var(--line); color:var(--text); }
.note { color:var(--muted); font-size:0.95rem; margin-top:6px; }
/* bottom boxed action - make container invisible so only the back-box is visible */
.bottom-box {
	position: fixed;
	right: 20px;
	bottom: 20px;
	z-index: 999;
	/* remove visual box behind the button */
	background: transparent;
	border: none;
	padding: 0;
	border-radius: 0;
	box-shadow: none;
}

/* keep the back-box styling (visible button) */
.back-box {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 8px 12px;
	border-radius: 10px;
	background: var(--card);
	color: var(--text);
	text-decoration: none;
	font-weight: 700;
	border: 1px solid var(--line);
	animation: floatUp 3.5s ease-in-out infinite;
	transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease, color 200ms ease;
	box-shadow: 0 6px 18px rgba(2,6,23,0.06);
}
.back-box:hover {
	transform: translateY(-4px) scale(1.02);
	box-shadow: 0 12px 28px rgba(2,6,23,0.12);
	background: var(--pal-4);
	color: #fff;
	border-color: color-mix(in srgb, var(--pal-4) 60%, #0000);
}

/* responsive tweaks */
@media (max-width:520px) {
	.bottom-box { left: 12px; right: 12px; bottom: 14px; display:flex; justify-content:center; }
	.back-box { width:100%; justify-content:center; }
}
</style>
</head>
<body class="theme-profile-bg">

	<!-- added shared topbar / logo (uses styles.css .dash-brand-logo) -->
	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="ServisyoHub" class="dash-brand-logo" onerror="this.style.display='none'">
		</div>
	</div>

	<div class="page-wrap">
		<div class="header-row">
			<div>
				<h2 style="margin:0">Manage Payments</h2>
				<div class="note">Add, view and update payment records. Uses site styles.</div>
			</div>
			<div class="col">
				<?php if (!$dbAvailable): ?>
					<div class="note">Database unavailable: <?php echo e($lastConnError); ?></div>
				<?php endif; ?>
				<!-- removed top Back button per change (moved to bottom) -->
			</div>
		</div>

		<?php if ($success): ?><div class="form-card glass-card" style="margin-bottom:12px;"><strong><?php echo e($success); ?></strong></div><?php endif; ?>
		<?php if (!empty($errors)): ?>
			<div class="form-card glass-card" style="margin-bottom:12px;">
				<ul style="margin:0;padding-left:18px;">
					<?php foreach ($errors as $err) echo '<li>'.e($err).'</li>'; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="form-card glass-card">
			<h3 style="margin-top:0">Add Payment</h3>
			<?php if (!$dbAvailable): ?>
				<div class="note">Cannot add payments while the database is unavailable.</div>
			<?php else: ?>
			<form method="post" class="form-inline">
				<input type="hidden" name="action" value="add">
				<input class="input-sm" name="amount" placeholder="Amount" required inputmode="decimal" style="width:110px">
				<input class="input-sm" name="method" placeholder="Method (e.g. Card, Cash)" style="width:160px">
				<input class="input-sm" type="date" name="date" value="<?php echo date('Y-m-d'); ?>">
				<input class="input-sm" name="notes" placeholder="Notes" style="width:260px">
				<button class="btn-sm save" type="submit">Add</button>
			</form>
			<?php endif; ?>
		</div>

		<div class="form-card glass-card" style="margin-top:14px;">
			<h3 style="margin-top:0">Payments</h3>
			<?php if (empty($payments)): ?>
				<div class="note">No payments found.</div>
			<?php else: ?>
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>Date</th>
							<th>Amount</th>
							<th>Method</th>
							<th>Notes</th>
							<th>Status</th>
							<th style="width:170px">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($payments as $p): ?>
							<tr>
								<td><?php echo e($p['id']); ?></td>
								<td><?php echo e($p['date']); ?></td>
								<td><?php echo e(number_format((float)$p['amount'],2)); ?></td>
								<td><?php echo e($p['method']); ?></td>
								<td><?php echo e($p['notes']); ?></td>
								<td>
									<span class="<?php echo $p['status']==='paid' ? 'status-paid' : 'status-pending'; ?>">
										<?php echo e($p['status']); ?>
									</span>
								</td>
								<td>
									<?php if ($dbAvailable): ?>
										<form method="post" style="display:inline">
											<input type="hidden" name="action" value="toggle">
											<input type="hidden" name="id" value="<?php echo e($p['id']); ?>">
											<button class="btn-sm btn-ghost" type="submit"><?php echo $p['status']==='paid' ? 'Mark Pending' : 'Mark Paid'; ?></button>
										</form>
										<form method="post" style="display:inline" onsubmit="return confirm('Delete this payment?');">
											<input type="hidden" name="action" value="delete">
											<input type="hidden" name="id" value="<?php echo e($p['id']); ?>">
											<button class="btn-sm btn-danger" type="submit">Delete</button>
										</form>
									<?php else: ?>
										<span class="note">DB unavailable</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div> <!-- .page-wrap -->

	<!-- added fixed bottom box with boxed Back button -->
	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="profile.php" class="back-box" title="Back to profile">← Back to profile</a>
	</div>

	<!-- ...existing code... -->
</body>
</html>