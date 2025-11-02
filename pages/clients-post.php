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

/* Fetch recent job posts from users */
$recentSearches = [];
if ($dbAvailable) {
	// Get the 4 most recent job titles from user posts
	$sql = "SELECT title FROM jobs 
	        WHERE title IS NOT NULL AND title != '' 
	        ORDER BY posted_at DESC LIMIT 4";
	$result = mysqli_query($mysqli, $sql);
	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) {
			$recentSearches[] = $row['title'];
		}
		mysqli_free_result($result);
	}
}

// Default suggestions if no job posts found
if (empty($recentSearches)) {
	$recentSearches = [
		'Buy and deliver item',
		'Booth Staff for pop-up',
		'Help me with moving',
		'Helper for an event'
	];
}
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
	background: #ffffff !important; 
	margin: 0; 
	font-family: system-ui, -apple-system, sans-serif; 
}

/* Background logo - transparent and behind UI */
.bg-logo {
	position: fixed;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	z-index: 0;
	pointer-events: none;
	opacity: 0.03;
	width: 80%;
	max-width: 600px;
}
.bg-logo img {
	width: 100%;
	height: auto;
}

/* Top bar */
.top-bar {
	background: #ffffff;
	border-bottom: 1px solid #e5e7eb;
	padding: 16px 0;
	position: sticky;
	top: 0;
	z-index: 100;
	box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.top-bar-content {
	max-width: 960px;
	margin: 0 auto;
	padding: 0 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	position: relative;
	z-index: 10;
}
.top-bar-logo {
	text-decoration: none;
	display: flex;
	align-items: center;
}
.top-bar-logo img {
	height: 48px;
	width: auto;
}

/* Greeting section with avatar */
.jobs-greeting {
	display: flex;
	align-items: center;
	gap: 14px;
	margin: 24px auto 24px;
	max-width: 960px;
	padding: 0 12px;
	position: relative;
	z-index: 10;
}
.jobs-avatar {
	width: 56px;
	height: 56px;
	border-radius: 50%;
	background: #e0f2fe;
	color: #0078a6;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 800;
	font-size: 1.3rem;
	flex-shrink: 0;
	box-shadow: 0 4px 12px rgba(0,120,166,.15);
}
.jobs-greeting-text {
	display: flex;
	flex-direction: column;
	gap: 2px;
}
.jobs-greeting-label {
	margin: 0;
	font-size: 0.95rem;
	color: #64748b;
	font-weight: 500;
}
.jobs-greeting-name {
	margin: 0;
	font-size: 1.4rem;
	font-weight: 800;
	color: #0f172a;
	line-height: 1.2;
}

/* Question section */
.jobs-question {
	max-width: 960px;
	margin: 0 auto 20px;
	padding: 0 12px;
	position: relative;
	z-index: 10;
}
.jobs-question-text {
	margin: 0;
	font-size: 1.6rem;
	font-weight: 800;
	color: #0f172a;
	line-height: 1.3;
}

/* Search section */
:root { --jobs-blue: #0078a6; }
.jobs-search-simple {
	max-width: 960px;
	margin: 0 auto 24px;
	padding: 0 12px;
	position: relative;
	z-index: 10;
}
.jobs-box {
	border: 2px solid color-mix(in srgb, var(--jobs-blue) 70%, #0000);
	border-radius: 16px;
	overflow: hidden;
	background: #fff;
	box-shadow: 0 10px 28px rgba(2,6,23,.08);
}
.jobs-row {
	display: grid;
	grid-template-columns: 28px 1fr 28px;
	align-items: center;
	gap: 10px;
	padding: 10px 12px;
}
.jobs-ico { width: 18px; height: 18px; color: var(--jobs-blue); opacity: .95; }
.jobs-input {
	appearance: none; border: none; outline: none; background: transparent;
	font: inherit; color: #0f172a; padding: 6px 0; width: 100%;
}
.jobs-row:focus-within { box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--jobs-blue) 35%, #0000); border-radius: 12px; }
.jobs-input-wrap { position: relative; }

/* Search suggestions */
.search-suggestions {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	margin-top: 16px;
}
.suggestion-pill {
	appearance: none;
	border: 2px solid #0078a6;
	background: #fff;
	color: #0f172a;
	border-radius: 999px;
	padding: 10px 16px;
	font-weight: 600;
	font-size: 0.9rem;
	cursor: pointer;
	transition: all 0.15s ease;
}
.suggestion-pill:hover {
	background: #f0f9ff;
	border-color: #0078a6;
	transform: translateY(-1px);
}
.suggestion-pill:active {
	transform: translateY(0);
}

/* Job results section */
.jobs-results { max-width: 960px; margin: 0 auto 80px; padding: 0 12px; }
.results-header { display: flex; align-items: center; gap: 8px; margin: 10px 0 12px; font-size: .9rem; color: #64748b; }
.results-dot { width: 12px; height: 12px; border-radius: 50%; background: var(--jobs-blue); }

/* Trending Services */
.trending-services {
	max-width: 960px;
	margin: 24px auto 80px;
	padding: 0 12px;
	position: relative;
	z-index: 10;
}
.trending-title {
	margin: 0 0 16px;
	font-size: 1.3rem;
	font-weight: 800;
	color: #0f172a;
}
.trending-list {
	display: flex;
	flex-direction: column;
	gap: 0;
}
.service-item {
	padding: 16px 0;
	border-bottom: 1px solid #e5e7eb;
	cursor: pointer;
	transition: background 0.15s ease;
}
.service-item:hover {
	background: #f8fafc;
}
.service-item:last-child {
	border-bottom: none;
}
.service-category {
	font-size: 0.85rem;
	color: #64748b;
	margin-bottom: 6px;
	font-weight: 500;
}
.service-main {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
}
.service-title {
	font-size: 1.05rem;
	font-weight: 700;
	color: #0f172a;
	flex: 1;
}
.service-arrow {
	width: 20px;
	height: 20px;
	color: #64748b;
	flex-shrink: 0;
	transition: transform 0.15s ease, color 0.15s ease;
}
.service-item:hover .service-arrow {
	transform: translateX(4px);
	color: #0078a6;
}

.jobs-list { display: grid; gap: 12px; }
.job-card {
	background: #0078a6;
	color: #fff;
	border-radius: 16px;
	padding: 20px 22px;
	box-shadow: 0 8px 24px rgba(0,120,166,.24);
	transition: transform .15s ease, box-shadow .15s ease;
	position: relative;
}
.job-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,120,166,.32); }
.job-title { font-weight: 800; font-size: 1.1rem; margin: 0 0 14px; color: #fff; }
.job-meta { display: flex; flex-wrap: wrap; gap: 14px 18px; font-size: .9rem; opacity: .95; }
.job-meta-item { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
.job-meta-item svg { width: 16px; height: 16px; flex-shrink: 0; }
.job-heart {
	position: absolute;
	top: 20px;
	right: 22px;
	width: 22px;
	height: 22px;
	color: #fff;
	opacity: .9;
	cursor: pointer;
	transition: transform .12s ease, opacity .12s ease;
}
.job-heart:hover { transform: scale(1.1); opacity: 1; }

/* Post Modal Form */
.post-modal {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: #fff;
	z-index: 2000;
	opacity: 0;
	visibility: hidden;
	transition: opacity 0.3s ease, visibility 0.3s ease;
}
.post-modal.active {
	opacity: 1;
	visibility: visible;
}
.post-modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20px;
}
.modal-back {
	background: none;
	border: none;
	cursor: pointer;
	padding: 8px;
	color: #0f172a;
	transition: color 0.15s ease;
	display: none;
}
.modal-back.visible {
	display: block;
}
.modal-back:hover {
	color: #64748b;
}
.modal-back svg {
	width: 24px;
	height: 24px;
}
.modal-close {
	background: none;
	border: none;
	cursor: pointer;
	padding: 8px;
	color: #64748b;
	transition: color 0.15s ease;
}
.modal-close:hover {
	color: #0f172a;
}
.modal-close svg {
	width: 28px;
	height: 28px;
}

/* Step Progress */
.step-progress {
	display: flex;
	justify-content: space-between;
	align-items: center;
	max-width: 600px;
	margin: 0 auto 40px;
	padding: 0 20px;
}
.step-item {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 12px;
	flex: 1;
	position: relative;
}
.step-item:not(:last-child)::after {
	content: '···';
	position: absolute;
	right: -20px;
	top: 20px;
	color: #e5e7eb;
	font-size: 1.2rem;
	letter-spacing: 2px;
}
.step-circle {
	width: 44px;
	height: 44px;
	border-radius: 50%;
	border: 2px solid #e5e7eb;
	background: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.3s ease;
}
.step-item.active .step-circle {
	background: #0f172a;
	border-color: #0f172a;
}
.step-item.completed .step-circle {
	background: #0f172a;
	border-color: #0f172a;
}
.step-item.active .step-circle::after {
	content: '';
	width: 12px;
	height: 12px;
	border-radius: 50%;
	background: #fff;
}
.step-item.completed .step-circle svg {
	width: 20px;
	height: 20px;
	color: #fff;
}
.step-label {
	font-size: 0.9rem;
	color: #94a3b8;
	font-weight: 600;
}
.step-item.active .step-label {
	color: #0f172a;
}
.step-item.completed .step-label {
	color: #0f172a;
}

/* Modal Content */
.modal-content {
	max-width: 600px;
	margin: 0 auto;
	padding: 0 20px 40px;
}
.modal-step {
	display: none;
}
.modal-step.active {
	display: block;
}
.step-title {
	font-size: 0.85rem;
	color: #64748b;
	margin-bottom: 8px;
	font-weight: 500;
}
.step-heading {
	font-size: 1.8rem;
	font-weight: 800;
	color: #0f172a;
	margin-bottom: 24px;
}
.step-subheading {
	font-size: 1.4rem;
	font-weight: 700;
	color: #0f172a;
	margin-top: 32px;
	margin-bottom: 12px;
}
.step-subtitle {
	font-size: 1rem;
	color: #64748b;
	margin-bottom: 16px;
	font-weight: 500;
}
.guidance-text {
	color: #64748b;
	font-size: 0.95rem;
	line-height: 1.6;
	margin-bottom: 12px;
}
.guidance-list {
	list-style: none;
	padding: 0;
	margin: 0 0 20px 0;
}
.guidance-list li {
	color: #64748b;
	font-size: 0.95rem;
	padding-left: 20px;
	position: relative;
	margin-bottom: 8px;
}
.guidance-list li::before {
	content: '•';
	position: absolute;
	left: 0;
	font-weight: bold;
}
.generate-button {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	background: transparent;
	color: #64748b;
	border: none;
	padding: 0;
	font-size: 1rem;
	font-weight: 600;
	cursor: pointer;
	margin: 12px 0 0 0;
	transition: color 0.15s ease;
}
.generate-button:hover {
	color: #0f172a;
}
.generate-button svg {
	width: 20px;
	height: 20px;
}
.helper-section {
	margin: 32px 0 0 0;
	padding: 32px 0 0 0;
	border-top: 1px solid #e5e7eb;
}
.helper-label {
	font-size: 1rem;
	color: #64748b;
	margin-bottom: 16px;
	font-weight: 500;
}
.helper-counter {
	display: flex;
	align-items: center;
	gap: 24px;
}
.counter-btn {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	border: 2px solid #e5e7eb;
	background: #fff;
	color: #0f172a;
	font-size: 1.5rem;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.15s ease;
}
.counter-btn:hover {
	border-color: #cbd5e1;
	background: #f8fafc;
}
.counter-value {
	font-size: 1.5rem;
	font-weight: 700;
	color: #0f172a;
	min-width: 60px;
	text-align: center;
}

/* Sub-steps */
.sub-step {
	animation: fadeIn 0.3s ease;
}
@keyframes fadeIn {
	from { opacity: 0; transform: translateY(10px); }
	to { opacity: 1; transform: translateY(0); }
}

/* Question Items */
.question-item {
	margin-bottom: 16px;
	position: relative;
}
.add-question-btn {
	width: 100%;
	padding: 14px;
	border: 2px dashed #cbd5e1;
	background: transparent;
	border-radius: 12px;
	color: #64748b;
	font-size: 0.95rem;
	font-weight: 600;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	margin: 24px 0;
	transition: all 0.15s ease;
}
.add-question-btn:hover {
	border-color: #94a3b8;
	color: #475569;
	background: #f8fafc;
}
.add-question-btn svg {
	width: 18px;
	height: 18px;
}
.warning-message {
	display: flex;
	align-items: flex-start;
	gap: 12px;
	background: #fef3c7;
	padding: 16px;
	border-radius: 12px;
	margin: 32px 0 120px 0;
}
.warning-message svg {
	width: 20px;
	height: 20px;
	color: #f59e0b;
	flex-shrink: 0;
	margin-top: 2px;
}
.warning-message p {
	margin: 0;
	color: #78716c;
	font-size: 0.9rem;
	line-height: 1.5;
}
.upload-section {
	margin: 24px 0;
}
.upload-button {
	width: 100%;
	background: #fff;
	color: #0f172a;
	border: 2px solid #e5e7eb;
	border-radius: 999px;
	padding: 16px;
	font-size: 1rem;
	font-weight: 600;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	transition: all 0.15s ease;
	margin-bottom: 8px;
}
.upload-button:hover {
	background: #f8fafc;
	border-color: #cbd5e1;
}
.upload-button svg {
	width: 20px;
	height: 20px;
}
.upload-info {
	text-align: right;
	font-size: 0.85rem;
	color: #cbd5e1;
	margin-bottom: 24px;
}
.upload-warning {
	color: #94a3b8;
	font-size: 0.9rem;
	line-height: 1.5;
	margin-bottom: 100px;
}
.generate-screening-button {
	width: 100%;
	background: #f87171;
	color: #fff;
	border: none;
	border-radius: 12px;
	padding: 16px;
	font-size: 1rem;
	font-weight: 700;
	cursor: pointer;
	transition: all 0.15s ease;
	position: fixed;
	bottom: 20px;
	left: 20px;
	right: 20px;
	max-width: 600px;
	margin: 0 auto;
}
.generate-screening-button:hover {
	background: #ef4444;
}
.form-input {
	width: 100%;
	border: none;
	background: #f1f5f9;
	border-radius: 12px;
	padding: 16px;
	font-size: 1rem;
	color: #0f172a;
	font-family: inherit;
	outline: none;
	transition: background 0.15s ease;
}
.form-input:focus {
	background: #e2e8f0;
}
.form-input::placeholder {
	color: #cbd5e1;
}
.char-count {
	text-align: right;
	font-size: 0.85rem;
	color: #cbd5e1;
	margin-top: 8px;
}
.form-textarea {
	min-height: 120px;
	resize: vertical;
}
.modal-button {
	width: 100%;
	background: #cbd5e1;
	color: #64748b;
	border: none;
	border-radius: 12px;
	padding: 16px;
	font-size: 1rem;
	font-weight: 700;
	cursor: pointer;
	margin-top: 24px;
	transition: all 0.15s ease;
}
.modal-button:hover {
	background: #b0bccf;
}
.modal-button.next-button {
	position: fixed;
	bottom: 20px;
	left: 20px;
	right: 20px;
	max-width: 600px;
	margin: 0 auto;
}
.back-button {
	background: transparent;
	color: #64748b;
	border: 2px solid #e5e7eb;
	margin-bottom: 16px;
	margin-top: 0;
}
.back-button:hover {
	background: #f8fafc;
	border-color: #cbd5e1;
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
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<!-- Top Bar -->
	<div class="top-bar">
		<div class="top-bar-content">
			<a href="./home-services.php" class="top-bar-logo">
				<img src="../assets/images/bluefont.png" alt="Servisyo Hub" />
			</a>
		</div>
	</div>

	<!-- Greeting with avatar (left) and text (right) -->
	<div class="jobs-greeting">
		<div class="jobs-avatar"><?php echo htmlspecialchars($avatar); ?></div>
		<div class="jobs-greeting-text">
			<p class="jobs-greeting-label">Good morning!</p>
			<h1 class="jobs-greeting-name"><?php echo htmlspecialchars($display); ?></h1>
		</div>
	</div>

	<!-- Question section -->
	<div class="jobs-question">
		<h2 class="jobs-question-text">What do you need done today?</h2>
	</div>

	<!-- Main search bar -->
	<section class="jobs-search-simple" aria-label="Quick search">
		<div class="jobs-box">
			<div class="jobs-row">
				<svg class="jobs-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
				<div class="jobs-input-wrap">
					<input class="jobs-input" type="search" id="searchInput" placeholder="" aria-label="Search for a Job" autocomplete="off" />
				</div>
				<svg class="jobs-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
			</div>
		</div>
		
		<!-- Search suggestions -->
		<div class="search-suggestions">
			<?php foreach ($recentSearches as $search): ?>
				<button type="button" class="suggestion-pill"><?php echo htmlspecialchars($search); ?></button>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Trending Services -->
	<section class="trending-services" aria-label="Trending Services">
		<h3 class="trending-title">Trending Services</h3>
		
		<div class="trending-list">
			<!-- Service Item 1 -->
			<div class="service-item">
				<div class="service-category">Part-time · F&B</div>
				<div class="service-main">
					<span class="service-title">Part-timer needed for cafe ☕</span>
					<svg class="service-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="m9 18 6-6-6-6"/>
					</svg>
				</div>
			</div>

			<!-- Service Item 2 -->
			<div class="service-item">
				<div class="service-category">Social media · Micro-influencing</div>
				<div class="service-main">
					<span class="service-title">Livestream Host / Assistant ✏️</span>
					<svg class="service-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="m9 18 6-6-6-6"/>
					</svg>
				</div>
			</div>

			<!-- Service Item 3 -->
			<div class="service-item">
				<div class="service-category">Errands · Delivery</div>
				<div class="service-main">
					<span class="service-title">Deliver birthday present 🎁</span>
					<svg class="service-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="m9 18 6-6-6-6"/>
					</svg>
				</div>
			</div>

			<!-- Service Item 4 -->
			<div class="service-item">
				<div class="service-category">Errands · Overseas errands</div>
				<div class="service-main">
					<span class="service-title">Buy shoes from Japan 🇯🇵</span>
					<svg class="service-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="m9 18 6-6-6-6"/>
					</svg>
				</div>
			</div>

			<!-- Service Item 5 -->
			<div class="service-item">
				<div class="service-category">Household · Assembly</div>
				<div class="service-main">
					<span class="service-title">Assemble IKEA furniture for me 🪑</span>
					<svg class="service-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="m9 18 6-6-6-6"/>
					</svg>
				</div>
			</div>
		</div>
	</section>

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

	<!-- Post Modal -->
	<div class="post-modal" id="postModal">
		<div class="post-modal-header">
			<button class="modal-back" id="modalBack" aria-label="Back">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M15 18l-6-6 6-6"/>
				</svg>
			</button>
			<button class="modal-close" id="closeModal" aria-label="Close">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M18 6 6 18M6 6l12 12"/>
				</svg>
			</button>
		</div>

		<!-- Step Progress -->
			<div class="step-progress">
				<div class="step-item active" data-step="1">
					<div class="step-circle"></div>
					<span class="step-label">Title</span>
				</div>
				<div class="step-item" data-step="2">
					<div class="step-circle"></div>
					<span class="step-label">Description</span>
				</div>
				<div class="step-item" data-step="3">
					<div class="step-circle"></div>
					<span class="step-label">Details</span>
				</div>
				<div class="step-item" data-step="4">
					<div class="step-circle"></div>
					<span class="step-label">Budget</span>
				</div>
			</div>		<!-- Modal Form -->
		<form id="postForm" method="POST" action="">
			<div class="modal-content">
				<!-- Step 1: Title -->
				<div class="modal-step active" data-step="1">
					<p class="step-title">Step 1 of 1</p>
					<h2 class="step-heading">What do you need done today?</h2>
					<p class="step-subtitle">Give your task a title</p>
					<input 
						type="text" 
						name="title" 
						class="form-input" 
						placeholder="Need help with..." 
						required
						maxlength="100"
						id="titleInput"
					/>
					<p class="char-count">Minimum 10 characters</p>
					<button type="button" class="modal-button next-button" id="nextStep1">Generate task description</button>
				</div>

				<!-- Step 2: Description -->
				<div class="modal-step" data-step="2">
					<p class="step-title">Step 1 of 4</p>
					
					<!-- Sub-step 1: Describe your task -->
					<div class="sub-step" id="subStep2_1">
						<h2 class="step-heading">Describe your task</h2>
						
						<p class="guidance-text">Summarize the key details! A great description should:</p>
						<ul class="guidance-list">
							<li>Cover essential details</li>
							<li>Clearly outline expected results</li>
							<li>Request reference works when necessary</li>
						</ul>
						
						<textarea 
							name="description" 
							class="form-input form-textarea" 
							placeholder="Include details of your task here"
							required
							id="descriptionInput"
						></textarea>
						<p class="char-count">Minimum 30 characters</p>
						
						<button type="button" class="generate-button" id="generateBtn">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/>
								<path d="M21 3v5h-5"/>
							</svg>
							Generate
						</button>
						
						<div class="helper-section">
							<p class="helper-label">How many helpers do you need?</p>
							<div class="helper-counter">
								<button type="button" class="counter-btn" id="decreaseHelper">−</button>
								<span class="counter-value" id="helperCount">1</span>
								<button type="button" class="counter-btn" id="increaseHelper">+</button>
							</div>
						</div>
						
						<button type="button" class="modal-button next-button" id="nextSubStep2_1">Next</button>
					</div>
					
					<!-- Sub-step 2: Add an image (optional) -->
					<div class="sub-step" id="subStep2_2" style="display: none;">
						<h2 class="step-heading">Add an image</h2>
						<p class="step-subtitle">Add an image to better elaborate your task. (optional)</p>
						
						<div class="upload-section">
							<button type="button" class="upload-button" id="uploadBtn">
								<span>Upload</span>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
									<polyline points="17 8 12 3 7 8"/>
									<line x1="12" y1="3" x2="12" y2="15"/>
								</svg>
							</button>
							<p class="upload-info">Max file size: 5 MB</p>
							<input type="file" id="imageUpload" name="task_image" accept="image/*" style="display: none;" />
						</div>
						
						<div class="warning-message">
							<svg viewBox="0 0 24 24" fill="currentColor">
								<path d="M12 2L2 20h20L12 2zm0 5l6 11H6l6-11z"/>
								<path d="M11 10h2v5h-2zm0 6h2v2h-2z" fill="#fff"/>
							</svg>
							<p>Images with contact details or attempts to take conversations off-platform will be removed, leading to a ban or task removal.</p>
						</div>
						
						<button type="button" class="modal-button back-button" id="backSubStep2_2">Back</button>
						<button type="button" class="modal-button next-button" id="nextSubStep2_2">Next</button>
					</div>
					
					<!-- Sub-step 3: Pre-screen helpers -->
					<div class="sub-step" id="subStep2_3" style="display: none;">
						<h2 class="step-heading">Pre-screen helpers</h2>
						<p class="step-subtitle">Add questions to help find the right helper</p>
						
						<div id="questionsList">
							<div class="question-item">
								<input 
									type="text" 
									name="question1" 
									class="form-input" 
									placeholder="e.g., Do you have experience with this type of work?"
									id="question1Input"
								/>
							</div>
						</div>
						
						<button type="button" class="add-question-btn" id="addQuestionBtn">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<line x1="12" y1="5" x2="12" y2="19"/>
								<line x1="5" y1="12" x2="19" y2="12"/>
							</svg>
							Add another question
						</button>
						
						<button type="button" class="modal-button back-button" id="backSubStep2_3">Back</button>
						<button type="button" class="modal-button next-button" id="nextSubStep2_3">Next</button>
					</div>
					
					<!-- Sub-step 4: Requirements (optional) -->
					<div class="sub-step" id="subStep2_4" style="display: none;">
						<h2 class="step-heading">Requirements</h2>
						<p class="step-subtitle">Add any specific requirements for this task (optional)</p>
						
						<textarea 
							name="requirements" 
							class="form-input form-textarea" 
							placeholder="e.g., Must have own tools, Valid ID required, etc."
							id="requirementsInput"
							rows="6"
						></textarea>
						
						<button type="button" class="modal-button back-button" id="backSubStep2_4">Back</button>
						<button type="button" class="modal-button next-button" id="nextStep2">Continue to Details</button>
					</div>
				</div>

				<!-- Step 3: Details -->
				<div class="modal-step" data-step="4">
					<p class="step-title">Step 4 of 5</p>
					<h2 class="step-heading">Task details</h2>
					<p class="step-subtitle">Location and when you need it done</p>
					<input 
						type="text" 
						name="location" 
						class="form-input" 
						placeholder="Location"
						required
						style="margin-bottom: 16px;"
						id="locationInput"
					/>
					<input 
						type="date" 
						name="date_needed" 
						class="form-input" 
						required
						id="dateInput"
					/>
					<button type="button" class="modal-button back-button" id="backStep4">Back</button>
					<button type="button" class="modal-button next-button" id="nextStep4">Continue</button>
				</div>

				<!-- Step 4: Budget -->
				<div class="modal-step" data-step="4">
					<p class="step-title">Step 4 of 4</p>
					<h2 class="step-heading">What's your budget?</h2>
					<p class="step-subtitle">Suggest a budget for this task</p>
					<input 
						type="text" 
						name="budget" 
						class="form-input" 
						placeholder="₱ 0.00"
						id="budgetInput"
					/>
					<input type="hidden" name="category" value="General" id="categoryInput" />
					<button type="button" class="modal-button back-button" id="backStep4">Back</button>
					<button type="submit" class="modal-button next-button">Post Task</button>
				</div>
			</div>
		</form>
	</div>

	<script>
	// Post Modal functionality
	(function(){
		const modal = document.getElementById('postModal');
		const searchInput = document.getElementById('searchInput');
		const closeModal = document.getElementById('closeModal');
		const modalBack = document.getElementById('modalBack');
		const suggestionPills = document.querySelectorAll('.suggestion-pill');
		const trendingItems = document.querySelectorAll('.service-item');
		const titleInput = document.getElementById('titleInput');
		
		// Open modal when clicking search bar
		searchInput.addEventListener('click', function(e) {
			e.preventDefault();
			modal.classList.add('active');
			document.body.style.overflow = 'hidden';
		});
		
		// Open modal when clicking suggestion pills
		suggestionPills.forEach(pill => {
			pill.addEventListener('click', function(e) {
				e.preventDefault();
				const text = this.textContent;
				modal.classList.add('active');
				document.body.style.overflow = 'hidden';
				// Pre-fill the title with the suggestion
				titleInput.value = text;
			});
		});
		
		// Open modal when clicking trending items
		trendingItems.forEach(item => {
			item.addEventListener('click', function(e) {
				e.preventDefault();
				const title = this.querySelector('.service-title').textContent;
				modal.classList.add('active');
				document.body.style.overflow = 'hidden';
				// Pre-fill the title with the trending service
				titleInput.value = title;
			});
		});
		
		// Close modal
		closeModal.addEventListener('click', function() {
			modal.classList.remove('active');
			document.body.style.overflow = '';
		});
		
		// Close on escape key
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && modal.classList.contains('active')) {
				modal.classList.remove('active');
				document.body.style.overflow = '';
			}
		});
	})();
	
	// Helper counter
	(function(){
		let helperCount = 1;
		const countDisplay = document.getElementById('helperCount');
		const decreaseBtn = document.getElementById('decreaseHelper');
		const increaseBtn = document.getElementById('increaseHelper');
		
		decreaseBtn.addEventListener('click', function() {
			if (helperCount > 1) {
				helperCount--;
				countDisplay.textContent = helperCount;
			}
		});
		
		increaseBtn.addEventListener('click', function() {
			helperCount++;
			countDisplay.textContent = helperCount;
		});
	})();
	
	// Multi-step form navigation
	(function(){
		let currentStep = 1;
		const totalSteps = 4;
		const modalBack = document.getElementById('modalBack');
		
		function updateStepProgress(stepNumber) {
			// Mark previous steps as completed
			document.querySelectorAll('.step-item').forEach((item, index) => {
				const stepNum = index + 1;
				item.classList.remove('active', 'completed');
				
				if (stepNum < stepNumber) {
					item.classList.add('completed');
					// Add checkmark for completed steps
					const circle = item.querySelector('.step-circle');
					if (!circle.querySelector('svg')) {
						circle.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
					}
				} else if (stepNum === stepNumber) {
					item.classList.add('active');
					// Remove checkmark, show dot
					const circle = item.querySelector('.step-circle');
					circle.innerHTML = '';
				} else {
					// Future steps - empty circle
					const circle = item.querySelector('.step-circle');
					circle.innerHTML = '';
				}
			});
			
			// Show/hide back button in header
			if (stepNumber > 1) {
				modalBack.classList.add('visible');
			} else {
				modalBack.classList.remove('visible');
			}
		}
		
		function goToStep(stepNumber) {
			// Hide all steps
			document.querySelectorAll('.modal-step').forEach(step => {
				step.classList.remove('active');
			});
			
			// Show current step
			document.querySelector(`.modal-step[data-step="${stepNumber}"]`).classList.add('active');
			
			// Update progress indicators
			updateStepProgress(stepNumber);
			
			currentStep = stepNumber;
		}
		
		// Header back button
		modalBack.addEventListener('click', function() {
			if (currentStep > 1) {
				goToStep(currentStep - 1);
			}
		});
		
		// Step 1 -> Step 2
		document.getElementById('nextStep1').addEventListener('click', function() {
			const titleInput = document.getElementById('titleInput');
			if (titleInput.value.trim().length >= 10) {
				goToStep(2);
				showSubStep(2, 1); // Show first sub-step of Step 2
			} else {
				alert('Please enter at least 10 characters for the title.');
			}
		});
		
		// Sub-step navigation for Step 2
		let currentSubStep = 1;
		
		function showSubStep(step, subStep) {
			// Hide all sub-steps of Step 2
			document.querySelectorAll('#subStep2_1, #subStep2_2, #subStep2_3, #subStep2_4').forEach(sub => {
				sub.style.display = 'none';
			});
			
			// Show target sub-step
			document.getElementById(`subStep${step}_${subStep}`).style.display = 'block';
			currentSubStep = subStep;
		}
		
		// Sub-step 2.1 -> 2.2 (Describe -> Add Image)
		document.getElementById('nextSubStep2_1').addEventListener('click', function() {
			const descInput = document.getElementById('descriptionInput');
			if (descInput.value.trim().length >= 30) {
				showSubStep(2, 2);
			} else {
				alert('Please enter at least 30 characters for the description.');
			}
		});
		
		// Sub-step 2.2 -> 2.3 (Add Image -> Pre-screen)
		document.getElementById('nextSubStep2_2').addEventListener('click', function() {
			showSubStep(2, 3);
		});
		
		// Back from sub-step 2.2 to 2.1
		document.getElementById('backSubStep2_2').addEventListener('click', function() {
			showSubStep(2, 1);
		});
		
		// Sub-step 2.3 -> 2.4 (Pre-screen -> Requirements)
		document.getElementById('nextSubStep2_3').addEventListener('click', function() {
			showSubStep(2, 4);
		});
		
		// Back from sub-step 2.3 to 2.2
		document.getElementById('backSubStep2_3').addEventListener('click', function() {
			showSubStep(2, 2);
		});
		
		// Sub-step 2.4 -> Step 3 (Requirements -> Details)
		document.getElementById('nextStep2').addEventListener('click', function() {
			goToStep(3);
		});
		
		// Back from sub-step 2.4 to 2.3
		document.getElementById('backSubStep2_4').addEventListener('click', function() {
			showSubStep(2, 3);
		});
		
		// Step 2 -> Step 3
		/*document.getElementById('nextStep2').addEventListener('click', function() {
			const descInput = document.getElementById('descriptionInput');
			if (descInput.value.trim().length >= 30) {
				goToStep(3);
			} else {
				alert('Please enter at least 30 characters for the description.');
			}
		});*/
		
		// Step 3 -> Step 4
		document.getElementById('nextStep3').addEventListener('click', function() {
			const locationInput = document.getElementById('locationInput');
			const dateInput = document.getElementById('dateInput');
			if (locationInput.value.trim().length > 0 && dateInput.value) {
				goToStep(4);
			} else {
				alert('Please fill in location and date.');
			}
		});
		
		// Back buttons
		document.getElementById('backStep3').addEventListener('click', function() {
			goToStep(2);
			showSubStep(2, 4); // Go back to last sub-step of Step 2
		});
		
		document.getElementById('backStep4').addEventListener('click', function() {
			goToStep(3);
		});
		
		// Add question functionality
		let questionCount = 1;
		document.getElementById('addQuestionBtn').addEventListener('click', function() {
			if (questionCount < 5) { // Limit to 5 questions
				questionCount++;
				const questionsList = document.getElementById('questionsList');
				const newQuestion = document.createElement('div');
				newQuestion.className = 'question-item';
				newQuestion.innerHTML = `
					<input 
						type="text" 
						name="question${questionCount}" 
						class="form-input" 
						placeholder="Add another screening question"
						id="question${questionCount}Input"
					/>
				`;
				questionsList.appendChild(newQuestion);
			} else {
				alert('Maximum 5 questions allowed');
			}
		});
		
		// Upload button click handler
		document.getElementById('uploadBtn').addEventListener('click', function() {
			document.getElementById('imageUpload').click();
		});
		
		// Show file name when file is selected
		document.getElementById('imageUpload').addEventListener('change', function(e) {
			if (e.target.files.length > 0) {
				const fileName = e.target.files[0].name;
				const fileSize = (e.target.files[0].size / (1024 * 1024)).toFixed(2);
				if (fileSize > 5) {
					alert('File size must be less than 5 MB');
					e.target.value = '';
					return;
				}
				document.querySelector('.upload-info').textContent = `Selected: ${fileName} (${fileSize} MB)`;
			}
		});
	})();
	
	// Typing effect for placeholder with rotating phrases
	(function(){
		const searchInput = document.getElementById('searchInput');
		const phrases = [
			'Pick up laundry later at 5pm',
			'Need help with moving furniture',
			'Looking for house cleaning service',
			'Buy and deliver groceries',
			'Assemble IKEA furniture for me',
			'Walking my dog every morning'
		];
		let phraseIndex = 0;
		let charIndex = 0;
		let isDeleting = false;
		
		function typeEffect() {
			const currentPhrase = phrases[phraseIndex];
			
			if (!isDeleting) {
				// Typing forward
				searchInput.setAttribute('placeholder', currentPhrase.substring(0, charIndex + 1));
				charIndex++;
				
				if (charIndex === currentPhrase.length) {
					// Pause at end of phrase
					isDeleting = true;
					setTimeout(typeEffect, 2000); // Wait 2 seconds before deleting
					return;
				}
				setTimeout(typeEffect, 80); // Typing speed
			} else {
				// Deleting backward
				searchInput.setAttribute('placeholder', currentPhrase.substring(0, charIndex - 1));
				charIndex--;
				
				if (charIndex === 0) {
					// Move to next phrase
					isDeleting = false;
					phraseIndex = (phraseIndex + 1) % phrases.length;
					setTimeout(typeEffect, 500); // Pause before typing next phrase
					return;
				}
				setTimeout(typeEffect, 40); // Deleting speed (faster)
			}
		}
		
		// Start typing effect after a brief delay
		setTimeout(typeEffect, 500);
	})();


	</script>
</body>
</html>
</body>
</html>
