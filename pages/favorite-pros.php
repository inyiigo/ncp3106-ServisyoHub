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
    list($h, $u, $p, $n) = $creds;
    mysqli_report(MYSQLI_REPORT_OFF);
    try {
        $conn = @mysqli_connect($h, $u, $p, $n);
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

// Handle remove favorite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_logged_in && $dbAvailable) {
    $action = $_POST['action'] ?? '';
    if ($action === 'remove' && isset($_POST['pro_id'])) {
        $pro_id = intval($_POST['pro_id']);
        // delete quietly; ignore if table missing
        if ($stmt = mysqli_prepare($mysqli, "DELETE FROM favorite_pros WHERE user_id = ? AND pro_id = ?")) {
            mysqli_stmt_bind_param($stmt, 'ii', $user_id, $pro_id);
            if (mysqli_stmt_execute($stmt)) $success = 'Removed from favorites.';
            else $errors[] = 'Could not remove favorite.';
            mysqli_stmt_close($stmt);
        }
    }
}

// Load favorite pros (graceful if tables missing)
$favorites = [];
if (!$not_logged_in && $dbAvailable) {
    $sql = "SELECT p.id, p.name, p.profession, COALESCE(p.rating, 0) AS rating, COALESCE(p.city, '') AS city, COALESCE(p.avatar,'') AS avatar
            FROM favorite_pros f
            JOIN pros p ON p.id = f.pro_id
            WHERE f.user_id = ?
            ORDER BY p.rating DESC, p.name ASC";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $res = mysqli_stmt_get_result($stmt);
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) $favorites[] = $row;
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
<title>Favorite Pros • ServisyoHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* Page tweaks using site tokens */
.page-wrap { max-width: 980px; margin: 24px auto; padding: 18px; position: relative; z-index: 1; }
.header-row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.header-row h2 { margin:0; }
.note { color: var(--muted); }

.grid { display:grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
@media (max-width: 960px) { .grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }

.pro-card { display:flex; gap:14px; align-items:center; padding:14px; border-radius:14px; 
	background: #0078a6; color: #fff; border: 2px solid color-mix(in srgb, #0078a6 80%, #0000); 
	box-shadow: 0 8px 20px rgba(0,120,166,.24); }
.pro-avatar { width:60px; height:60px; border-radius:50%; overflow:hidden; background:#fff; flex: 0 0 60px; display:grid; place-items:center; border: 2px solid rgba(255,255,255,.8); }
.pro-avatar img { width:100%; height:100%; object-fit:cover; }
.pro-info { display:grid; gap:4px; flex:1; min-width:0; }
.pro-name { font-weight:800; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color: #fff; }
.pro-sub { color: rgba(255,255,255,.85); font-size:.92rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.pro-meta { display:flex; gap:8px; align-items:center; color: rgba(255,255,255,.75); font-size:.9rem; }

.pro-act { display:flex; gap:8px; }
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

/* removed: centered floating bottom navigation styles (.dash-bottom-nav) */
/*
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
*/
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
				<h2>Your Favorite Pros</h2>
				<div class="note">
					<?php
					if ($not_logged_in) { echo 'You are not logged in. Sign in to manage your favorites.'; }
					elseif (!$dbAvailable) { echo 'Database unavailable: ' . e($lastConnError); }
					else { echo 'Quick access to the professionals you trust most.'; }
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
					<p class="note">No favorites to display.</p>
				</div>
			<?php else: ?>
				<?php if (empty($favorites)): ?>
					<div class="form-card glass-card" style="grid-column:1/-1;">
						<p class="note">You haven’t added any favorites yet.</p>
					</div>
				<?php else: ?>
					<?php foreach ($favorites as $pro): ?>
						<div class="pro-card">
							<div class="pro-avatar">
								<?php
									$av = trim($pro['avatar'] ?? '');
									$src = $av ? '../'.ltrim($av,'/') : '../assets/images/avatar-placeholder.png';
								?>
								<img src="<?php echo e($src); ?>" alt="Avatar of <?php echo e($pro['name']); ?>" onerror="this.src='../assets/images/avatar-placeholder.png'">
							</div>
							<div class="pro-info">
								<div class="pro-name" title="<?php echo e($pro['name']); ?>"><?php echo e($pro['name']); ?></div>
								<div class="pro-sub"><?php echo e($pro['profession']); ?><?php echo $pro['city'] ? ' • ' . e($pro['city']) : ''; ?></div>
								<div class="pro-meta">Rating: <?php echo e(number_format((float)$pro['rating'],1)); ?> / 5</div>
								<div class="pro-act">
									<a class="btn-primary" href="javascript:void(0)" title="View profile">View</a>
									<form method="post" onsubmit="return confirm('Remove from favorites?');" style="display:inline">
										<input type="hidden" name="action" value="remove">
										<input type="hidden" name="pro_id" value="<?php echo e($pro['id']); ?>">
										<button type="submit" class="btn-danger">Remove</button>
									</form>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="./settings.php" class="back-box" title="Back to settings">← Back to settings</a>
	</div>
</body>
</html>
