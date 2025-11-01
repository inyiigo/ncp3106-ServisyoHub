<?php
session_start();

/* Safe DB connection */
$configPath = __DIR__ . '/../config/config.php';
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

/* Handle submit: store in jobs table */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_logged_in && $dbAvailable) {
	$category = trim($_POST['category'] ?? '');
	$title = trim($_POST['title'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$location = trim($_POST['location'] ?? '');
	$budget = trim($_POST['budget'] ?? '');
	$date_needed = trim($_POST['date_needed'] ?? '');

	if ($category === '') $errors[] = 'Please select a service category.';
	if ($title === '') $errors[] = 'Title is required.';
	if ($description === '') $errors[] = 'Description is required.';
	if ($location === '') $errors[] = 'Location is required.';

	if (empty($errors)) {
		$sql = "INSERT INTO jobs (user_id, title, category, description, location, budget, date_needed, status, posted_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'open', NOW())";
		if ($stmt = mysqli_prepare($mysqli, $sql)) {
			mysqli_stmt_bind_param($stmt, 'issssss', $user_id, $title, $category, $description, $location, $budget, $date_needed);
			if (mysqli_stmt_execute($stmt)) {
				$success = 'Your service request has been posted successfully!';
				// Clear form
				$_POST = [];
			} else {
				$errors[] = 'Unable to publish service request.';
			}
			mysqli_stmt_close($stmt);
		} else {
			$errors[] = 'Database error.';
		}
	}
}

$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : 'there';
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Post Service Request • Servisyo Hub</title>
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
/* Page theme: white background */
body { 
	background: #f8fafc !important; 
	margin: 0; 
	font-family: system-ui, -apple-system, sans-serif; 
}

/* Topbar */
.dash-topbar { 
	background: #fff;
	padding: 20px 24px;
	border-bottom: 3px solid #0078a6;
}

.dash-brand {
	font-size: 1.5rem;
	font-weight: 800;
	color: #0078a6;
	text-align: center;
}

/* Main container */
.post-container {
	max-width: 800px;
	margin: 0 auto;
	padding: 40px 20px 120px;
}

/* Main container */
.post-container {
	max-width: 800px;
	margin: 40px auto 120px;
	padding: 0 20px;
	position: relative;
	z-index: 1;
}

/* Page title */
.page-title {
	text-align: center;
	margin-bottom: 32px;
}

.page-title h1 {
	font-size: 2rem;
	font-weight: 800;
	color: #0f172a;
	margin: 0 0 8px;
}

.page-title p {
	color: #64748b;
	font-size: 1rem;
	margin: 0;
}

/* Form card */
.form-card {
	background: #0078a6;
	color: #fff;
	border-radius: 16px;
	padding: 32px;
	box-shadow: 0 8px 24px rgba(0,120,166,.24);
}

.form-card h2 {
	margin: 0 0 24px;
	font-size: 1.5rem;
	font-weight: 800;
	color: #fff;
}

/* Form elements */
.form-group {
	margin-bottom: 20px;
}

.form-group label {
	display: block;
	margin-bottom: 8px;
	font-weight: 700;
	color: #fff;
	font-size: 0.95rem;
}

.form-group input,
.form-group textarea,
.form-group select {
	width: 100%;
	padding: 12px 16px;
	border-radius: 10px;
	border: 2px solid rgba(255,255,255,.3);
	background: rgba(255,255,255,.15);
	color: #fff;
	font: inherit;
	font-size: 1rem;
	transition: all 0.15s ease;
	box-sizing: border-box;
}

.form-group select {
	cursor: pointer;
	appearance: none;
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
	background-repeat: no-repeat;
	background-position: right 16px center;
	padding-right: 48px;
}

.form-group textarea {
	resize: vertical;
	min-height: 120px;
	font-family: inherit;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
	color: rgba(255,255,255,.65);
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
	outline: none;
	border-color: rgba(255,255,255,.6);
	background: rgba(255,255,255,.2);
}

/* Submit button */
.btn-submit {
	background: #fff;
	color: #0078a6;
	padding: 14px 32px;
	border: none;
	border-radius: 12px;
	font-weight: 800;
	font-size: 1.1rem;
	cursor: pointer;
	transition: transform 0.15s ease, box-shadow 0.15s ease;
	width: 100%;
	margin-top: 8px;
}

.btn-submit:hover {
	transform: translateY(-2px);
	box-shadow: 0 6px 18px rgba(255,255,255,.3);
}

.btn-submit:active {
	transform: translateY(0);
}

.btn-submit:disabled {
	opacity: 0.6;
	cursor: not-allowed;
}

/* Alerts */
.alert {
	padding: 16px 20px;
	border-radius: 12px;
	margin-bottom: 24px;
	font-weight: 600;
	text-align: center;
}

.alert-success {
	background: rgba(34, 197, 94, 0.2);
	border: 2px solid rgba(34, 197, 94, 0.5);
	color: #fff;
}

.alert-error {
	background: rgba(239, 68, 68, 0.2);
	border: 2px solid rgba(239, 68, 68, 0.5);
	color: #fff;
}

.alert ul {
	margin: 8px 0 0;
	padding-left: 24px;
	text-align: left;
}

/* Bottom navigation */
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
	background: #fff;
	border-radius: 18px;
	box-shadow: 0 18px 46px rgba(2,6,23,.16);
	padding: 10px 12px;
	display: flex;
	gap: 6px;
}

.dash-bottom-nav:hover {
	transform: translateX(-50%) scale(1);
	box-shadow: 0 12px 28px rgba(2,6,23,.12);
}

.dash-bottom-nav a {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 10px 16px;
	border-radius: 12px;
	color: #0f172a;
	text-decoration: none;
	font-weight: 800;
	transition: background 0.15s ease, color 0.15s ease;
}

.dash-bottom-nav a.active {
	background: #0078a6;
	color: #fff;
}

.dash-bottom-nav a:hover:not(.active) {
	background: #f0f9ff;
}

.dash-bottom-nav .dash-icon {
	width: 20px;
	height: 20px;
}
</style>
</head>
<body>
	<!-- Topbar -->
	<div class="dash-topbar">
		<div class="dash-brand">Servisyo Hub</div>
	</div>

	<!-- Main Content -->
	<div class="post-container">
		<div class="page-title">
			<h1>Post a Service Request</h1>
			<p>Tell us what service you need and we'll connect you with providers</p>
		</div>

		<?php if ($success): ?>
			<div class="alert alert-success">
				✓ <?php echo e($success); ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($errors)): ?>
			<div class="alert alert-error">
				<strong>Please fix the following errors:</strong>
				<ul>
					<?php foreach ($errors as $err): ?>
						<li><?php echo e($err); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="form-card">
			<?php if ($not_logged_in): ?>
				<h2>Login Required</h2>
				<p style="text-align: center; margin: 0;">You need to be logged in to post a service request.</p>
			<?php elseif (!$dbAvailable): ?>
				<h2>Service Unavailable</h2>
				<p style="text-align: center; margin: 0;">Database connection error. Please try again later.</p>
			<?php else: ?>
				<h2>Service Request Details</h2>
				<form method="POST" action="">
					<div class="form-group">
						<label for="category">Service Category *</label>
						<select name="category" id="category" required>
							<option value="">-- Select a service --</option>
							<option value="Cleaning" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Cleaning') ? 'selected' : ''; ?>>House Cleaning</option>
							<option value="Plumbing" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Plumbing') ? 'selected' : ''; ?>>Plumbing Services</option>
							<option value="Electrical" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Electrical') ? 'selected' : ''; ?>>Electrical Repair</option>
							<option value="Aircon" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Aircon') ? 'selected' : ''; ?>>Aircon Cleaning</option>
							<option value="Painting" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Painting') ? 'selected' : ''; ?>>Painting</option>
							<option value="Gardening" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Gardening') ? 'selected' : ''; ?>>Gardening</option>
							<option value="Pest Control" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Pest Control') ? 'selected' : ''; ?>>Pest Control</option>
							<option value="Appliance Repair" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Appliance Repair') ? 'selected' : ''; ?>>Appliance Repair</option>
							<option value="Car Spa" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Car Spa') ? 'selected' : ''; ?>>Car Spa</option>
							<option value="Beauty Services" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Beauty Services') ? 'selected' : ''; ?>>Beauty Services</option>
							<option value="Massage" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Massage') ? 'selected' : ''; ?>>Massage</option>
							<option value="Pet Care" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Pet Care') ? 'selected' : ''; ?>>Pet Care</option>
						</select>
					</div>

					<div class="form-group">
						<label for="title">Title *</label>
						<input type="text" name="title" id="title" placeholder="e.g., Need house cleaning service" value="<?php echo e($_POST['title'] ?? ''); ?>" required>
					</div>

					<div class="form-group">
						<label for="description">Description *</label>
						<textarea name="description" id="description" placeholder="Describe what you need in detail..." required><?php echo e($_POST['description'] ?? ''); ?></textarea>
					</div>

					<div class="form-group">
						<label for="location">Location *</label>
						<input type="text" name="location" id="location" placeholder="e.g., Brgy. 442 Zone 44, Manila" value="<?php echo e($_POST['location'] ?? ''); ?>" required>
					</div>

					<div class="form-group">
						<label for="budget">Budget (Optional)</label>
						<input type="text" name="budget" id="budget" placeholder="e.g., ₱500 - ₱1000" value="<?php echo e($_POST['budget'] ?? ''); ?>">
					</div>

					<div class="form-group">
						<label for="date_needed">Date Needed (Optional)</label>
						<input type="date" name="date_needed" id="date_needed" value="<?php echo e($_POST['date_needed'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>">
					</div>

					<button type="submit" class="btn-submit">Post Service Request</button>
				</form>
			<?php endif; ?>
		</div>
	</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./home-services.php" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
		</a>
		<a href="./clients-post.php" class="active" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span>Post</span>
		</a>
		<a href="./my-services.php" aria-label="My Services">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Services</span>
		</a>
		<a href="./clients-profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>

	<script>
	// Search suggestions functionality
	(function(){
		const searchInput = document.getElementById('searchInput');
		const suggestionPills = document.querySelectorAll('.suggestion-pill');

		suggestionPills.forEach(pill => {
			pill.addEventListener('click', function() {
				searchInput.value = this.textContent;
				searchInput.focus();
			});
		});
	})();
	</script>
</body>
</html>
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

<!-- Floating bottom navigation -->
<nav class="dash-bottom-nav">
	<a href="./home-services.php" aria-label="Home">
		<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
		<span>Home</span>
	</a>
	<a href="./clients-post.php" class="active" aria-label="Post">
		<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
		<span>Post</span>
	</a>
	<a href="./my-services.php" aria-label="My Services">
		<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
		<span>My Services</span>
	</a>
	<a href="./clients-profile.php" aria-label="Profile">
		<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
		<span>Profile</span>
	</a>
</nav>

<style>
/* Bottom navigation styling */
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
	border: 1px solid #e5e7eb;
	background: #fff;
	border-radius: 18px;
	box-shadow: 0 18px 46px rgba(2,6,23,.16);
	padding: 10px 12px;
	display: flex;
	gap: 6px;
}
.dash-bottom-nav:hover {
	transform: translateX(-50%) scale(1);
	box-shadow: 0 12px 28px rgba(2,6,23,.12);
}
.dash-bottom-nav a {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 10px 12px;
	border-radius: 12px;
	color: #0f172a;
	text-decoration: none;
	font-weight: 800;
	transition: background 0.15s ease, color 0.15s ease;
}
.dash-bottom-nav a.active, .dash-bottom-nav a:hover {
	background: #0ea5e9;
	color: #fff;
}
.dash-bottom-nav .dash-icon {
	width: 18px;
	height: 18px;
}
</style>
</body>
</html>
