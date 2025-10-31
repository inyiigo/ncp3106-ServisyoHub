<?php
session_start();

/* Safe DB connection (tries includes/config.php then localhost fallback) */
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

/* Handle submit: store in jobs table (category as "Parent • Service") */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_logged_in && $dbAvailable) {
	$parent = trim($_POST['parent_category'] ?? '');
	$service = trim($_POST['service_type'] ?? '');
	$title = trim($_POST['title'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$location = trim($_POST['location'] ?? '');
	$budget = trim($_POST['budget'] ?? '');
	$date_needed = trim($_POST['date_needed'] ?? '');

	if ($parent === '') $errors[] = 'Please select a category.';
	if ($service === '') $errors[] = 'Please select a service.';
	if ($title === '') $errors[] = 'Title is required.';
	if ($description === '') $errors[] = 'Description is required.';
	if ($location === '') $errors[] = 'Location is required.';

	if (empty($errors)) {
		$category = $parent . ' • ' . $service;
		$sql = "INSERT INTO jobs (user_id, title, category, description, location, budget, date_needed, status, posted_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'open', NOW())";
		if ($stmt = mysqli_prepare($mysqli, $sql)) {
			mysqli_stmt_bind_param($stmt, 'issssss', $user_id, $title, $category, $description, $location, $budget, $date_needed);
			if (mysqli_stmt_execute($stmt)) {
				$success = 'Your service post has been published successfully!';
				unset($_POST); // clear form
			} else {
				$errors[] = 'Unable to publish service post.';
			}
			mysqli_stmt_close($stmt);
		} else {
			$errors[] = 'Database error.';
		}
	}
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Post a Service • ServisyoHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* layout */
.page-wrap { max-width: 880px; margin: 24px auto 90px; padding: 18px; position: relative; z-index:1; }

/* glass card theme */
.form-card.glass-card, .opt-card.glass-card {
	background: #0078a6 !important; color: #fff;
	border-radius: 16px; padding: 18px 20px;
	box-shadow: 0 8px 24px rgba(0,120,166,.24);
	border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
}
.form-card.glass-card h3, .opt-card.glass-card h3 { color:#fff; margin:0 0 10px; }
.note { color: rgba(255,255,255,.85); }

/* pickers */
.grid { display:grid; gap:10px; }
.grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
.grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
@media (max-width:820px){ .grid.cols-4 { grid-template-columns: repeat(3, 1fr); } }
@media (max-width:640px){ .grid.cols-3, .grid.cols-4 { grid-template-columns: repeat(2, 1fr); } }
@media (max-width:420px){ .grid.cols-3, .grid.cols-4 { grid-template-columns: 1fr; } }

.opt { display:flex; align-items:center; justify-content:center; text-align:center; gap:8px; padding:12px; border-radius:12px;
	background: rgba(255,255,255,.12); color:#fff; border:2px solid rgba(255,255,255,.5); cursor:pointer;
	font-weight:800; transition: transform .15s ease, box-shadow .15s ease, background .15s ease; }
.opt:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.2); background:#0078a6; }
.opt[aria-selected="true"] { background:#fff; color:#0078a6; border-color:#fff; }

.opt-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:0 0 10px; }
.opt-back { background:#fff; color:#0078a6; border:none; padding:6px 10px; border-radius:10px; font-weight:800; cursor:pointer; display:none; }
.opt-back.show { display:inline-flex; }

/* form fields */
.form-group { margin-bottom: 14px; }
.form-group label { display:block; margin-bottom:6px; font-weight:800; color:#fff; }
.form-group input, .form-group textarea {
	width:100%; padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.35);
	background: rgba(255,255,255,.1); color:#fff; font:inherit;
}
.form-group textarea { resize: vertical; min-height: 120px; }
.form-group input::placeholder, .form-group textarea::placeholder { color: rgba(255,255,255,.7); }
.form-group input:focus, .form-group textarea:focus { outline:2px solid rgba(255,255,255,.55); background: rgba(255,255,255,.15); }

.btn-submit {
	background:#fff; color:#0078a6; padding:12px 18px; border:none; border-radius:10px; font-weight:800; cursor:pointer;
	transition: transform .15s ease, box-shadow .15s ease;
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(255,255,255,.3); }

/* topbar + bg */
body.theme-profile-bg { background:#ffffff !important; background-attachment: initial !important; }
.dash-topbar { border-bottom:3px solid #0078a6; position:relative; z-index:1; }
.bg-logo { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:25%; max-width:350px; opacity:.15; z-index:0; pointer-events:none; }
.bg-logo img { width:100%; height:auto; display:block; }
</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo"><img src="../assets/images/job_logo.png" alt=""></div>

	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="ServisyoHub" class="dash-brand-logo" onerror="this.style.display='none'">
		</div>
	</div>

	<div class="page-wrap">
		<div class="opt-card glass-card" id="stepPick">
			<div class="opt-head">
				<h3>Post a Service</h3>
				<button type="button" id="btnBack" class="opt-back">← Back</button>
			</div>
			<p class="note" id="pickNote">Choose a category to start.</p>

			<!-- Step 1: Parent categories -->
			<div class="grid cols-3" id="cats">
				<button class="opt" data-parent="Home Service">Home Service</button>
				<button class="opt" data-parent="Personal Care">Personal Care</button>
				<button class="opt" data-parent="Events">Events</button>
			</div>

			<!-- Step 2: Services (hidden by default) -->
			<div class="grid cols-4" id="services" style="display:none;"></div>
		</div>

		<!-- Posting form -->
		<div class="form-card glass-card" style="margin-top:12px;">
			<h3>Details</h3>
			<div class="note">
				<?php
				if ($not_logged_in) echo 'You are not logged in. Sign in to post a service.';
				elseif (!$dbAvailable) echo 'Database unavailable: ' . e($lastConnError);
				else echo 'Tell us more about the service you need.';
				?>
			</div>

			<?php if ($success): ?>
				<div class="form-card glass-card" style="margin:12px 0 0;"><strong><?php echo e($success); ?></strong></div>
			<?php endif; ?>
			<?php if (!empty($errors)): ?>
				<div class="form-card glass-card" style="margin:12px 0 0;">
					<ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $err) echo '<li>'.e($err).'</li>'; ?></ul>
				</div>
			<?php endif; ?>

			<?php if ($not_logged_in || !$dbAvailable): ?>
				<p class="note" style="margin-top:10px;">Cannot submit at this time.</p>
			<?php else: ?>
			<form method="post" id="postForm" style="margin-top:12px;">
				<input type="hidden" name="parent_category" id="parent_category" value="<?php echo e($_POST['parent_category'] ?? ''); ?>">
				<input type="hidden" name="service_type" id="service_type" value="<?php echo e($_POST['service_type'] ?? ''); ?>">

				<div class="form-group">
					<label for="title">Title *</label>
					<input type="text" id="title" name="title" placeholder="e.g., Need general home cleaning" value="<?php echo e($_POST['title'] ?? ''); ?>" required>
				</div>

				<div class="form-group">
					<label for="description">Description *</label>
					<textarea id="description" name="description" placeholder="Describe what you need..." required><?php echo e($_POST['description'] ?? ''); ?></textarea>
				</div>

				<div class="form-group">
					<label for="location">Location *</label>
					<input type="text" id="location" name="location" placeholder="e.g., Brgy. 442 Zone 44, Manila" value="<?php echo e($_POST['location'] ?? ''); ?>" required>
				</div>

				<div class="form-group">
					<label for="budget">Budget (Optional)</label>
					<input type="text" id="budget" name="budget" placeholder="e.g., ₱800 - ₱1200" value="<?php echo e($_POST['budget'] ?? ''); ?>">
				</div>

				<div class="form-group">
					<label for="date_needed">Date Needed (Optional)</label>
					<input type="date" id="date_needed" name="date_needed" value="<?php echo e($_POST['date_needed'] ?? ''); ?>">
				</div>

				<button type="submit" class="btn-submit">Publish Service Post</button>
			</form>
			<?php endif; ?>
		</div>
	</div>

<script>
(function(){
	const cats = document.getElementById('cats');
	const srvs = document.getElementById('services');
	const back = document.getElementById('btnBack');
	const pickNote = document.getElementById('pickNote');
	const parentInp = document.getElementById('parent_category');
	const serviceInp = document.getElementById('service_type');

	// service map aligned with home-services.php tiles
	const MAP = {
		'Home Service': ['Cleaning','Aircon','Upholstery','Electrical & Appliance','Plumbing & Handyman','Pest Control','Ironing'],
		'Personal Care': ['Beauty','Massage','Spa','Medi-Spa'],
		'Events': ['Birthday','Wedding','Corporate','Anniversary']
	};

	function clearSelected(container){ container.querySelectorAll('.opt[aria-selected="true"]').forEach(el=>el.removeAttribute('aria-selected')); }

	function renderServices(parent){
		const list = MAP[parent] || [];
		srvs.innerHTML = list.map(s => '<button class="opt" data-s="'+s.replace(/"/g,'&quot;')+'">'+s+'</button>').join('');
	}

	// restore from POST (after server validation)
	const initialParent = parentInp.value;
	const initialService = serviceInp.value;
	if (initialParent) {
		clearSelected(cats);
		cats.querySelectorAll('.opt').forEach(o => { if (o.dataset.parent === initialParent) o.setAttribute('aria-selected','true'); });
		renderServices(initialParent);
		srvs.style.display = 'grid'; back.classList.add('show'); pickNote.textContent = 'Pick a service.';
		if (initialService) {
			srvs.querySelectorAll('.opt').forEach(o => { if (o.textContent.trim() === initialService) o.setAttribute('aria-selected','true'); });
		}
	}

	cats.addEventListener('click', (e) => {
		const btn = e.target.closest('.opt[data-parent]');
		if (!btn) return;
		clearSelected(cats); clearSelected(srvs);
		btn.setAttribute('aria-selected','true');
		const parent = btn.dataset.parent;
		parentInp.value = parent;
		renderServices(parent);
		serviceInp.value = '';
		srvs.style.display = 'grid';
		back.classList.add('show');
		pickNote.textContent = 'Pick a service.';
	});

	srvs.addEventListener('click', (e) => {
		const btn = e.target.closest('.opt[data-s]');
		if (!btn) return;
		clearSelected(srvs);
		btn.setAttribute('aria-selected','true');
		serviceInp.value = btn.dataset.s;
		// Prefill a sensible default title if empty
		const t = document.getElementById('title');
		if (t && !t.value.trim()) t.value = 'Need ' + btn.dataset.s.toLowerCase();
	});

	back.addEventListener('click', () => {
		parentInp.value = ''; serviceInp.value = '';
		clearSelected(cats); clearSelected(srvs);
		srvs.style.display = 'none';
		back.classList.remove('show');
		pickNote.textContent = 'Choose a category to start.';
	});
})();
</script>
</body>
</html>
