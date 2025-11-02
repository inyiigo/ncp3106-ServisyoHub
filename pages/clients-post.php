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
	overflow-y: auto;
	-webkit-overflow-scrolling: touch;
	contain: layout style paint;
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
	padding: 0 20px 180px;
	contain: layout style;
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
	min-height: 200px;
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

/* Checkbox */
.checkbox-label {
	display: flex;
	align-items: center;
	gap: 12px;
	cursor: pointer;
	margin: 24px 0;
	user-select: none;
}
.checkbox-input {
	width: 24px;
	height: 24px;
	border: 2px solid #cbd5e1;
	border-radius: 6px;
	cursor: pointer;
	appearance: none;
	-webkit-appearance: none;
	background: #fff;
	position: relative;
	flex-shrink: 0;
	transition: all 0.15s ease;
}
.checkbox-input:checked {
	background: #0f172a;
	border-color: #0f172a;
}
.checkbox-input:checked::after {
	content: '';
	position: absolute;
	left: 7px;
	top: 3px;
	width: 6px;
	height: 10px;
	border: solid #fff;
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}
.checkbox-text {
	font-size: 0.95rem;
	color: #475569;
	line-height: 1.5;
}

/* Location Type Buttons */
.location-type-buttons {
	display: flex;
	gap: 12px;
	margin-bottom: 24px;
}
.location-type-btn {
	flex: 1;
	padding: 14px 24px;
	border: 2px solid #e5e7eb;
	background: #fff;
	border-radius: 999px;
	font-size: 1rem;
	font-weight: 600;
	color: #64748b;
	cursor: pointer;
	transition: all 0.15s ease;
}
.location-type-btn:hover {
	border-color: #cbd5e1;
	background: #f8fafc;
}
.location-type-btn.active {
	background: #0f172a;
	border-color: #0f172a;
	color: #fff;
}

/* Location Picker */
.location-picker-input {
	position: relative;
	margin-bottom: 16px;
}
.location-picker-input svg {
	position: absolute;
	left: 16px;
	top: 50%;
	transform: translateY(-50%);
	width: 20px;
	height: 20px;
	color: #94a3b8;
	pointer-events: none;
}
.location-picker-field {
	width: 100%;
	border: none;
	background: #f1f5f9;
	border-radius: 12px;
	padding: 16px 16px 16px 48px;
	font-size: 1rem;
	color: #0f172a;
	font-family: inherit;
	outline: none;
	transition: background 0.15s ease;
}
.location-picker-field:focus {
	background: #e2e8f0;
}
.location-picker-field::placeholder {
	color: #cbd5e1;
}

/* Date Option Buttons */
.date-option-btn {
	width: 100%;
	padding: 16px 20px;
	border: 2px solid #e5e7eb;
	background: #fff;
	border-radius: 16px;
	font-size: 1rem;
	font-weight: 500;
	color: #64748b;
	cursor: pointer;
	transition: all 0.15s ease;
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 12px;
	text-align: left;
}
.date-option-btn:hover {
	border-color: #cbd5e1;
	background: #f8fafc;
}
.date-option-btn.active {
	background: #0f172a;
	border-color: #0f172a;
	color: #fff;
}
.date-option-btn.active svg {
	opacity: 1 !important;
	stroke: #fff;
}
.date-option-btn span {
	flex: 1;
}

/* Time Picker Modal */
.time-picker-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.5);
	z-index: 3000;
	display: none;
	align-items: flex-end;
	justify-content: center;
}
.time-picker-overlay.active {
	display: flex;
}
.time-picker-modal {
	background: #fff;
	border-radius: 24px 24px 0 0;
	width: 100%;
	max-width: 500px;
	padding: 24px;
	animation: slideUp 0.3s ease;
}
@keyframes slideUp {
	from {
		transform: translateY(100%);
	}
	to {
		transform: translateY(0);
	}
}
.time-picker-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 32px;
}
.time-picker-header h3 {
	font-size: 1.25rem;
	font-weight: 700;
	color: #0f172a;
	margin: 0;
}
.time-picker-close {
	background: none;
	border: none;
	padding: 8px;
	cursor: pointer;
	color: #64748b;
}
.time-picker-close:hover {
	color: #0f172a;
}
.time-picker-wheels {
	display: flex;
	justify-content: center;
	align-items: center;
	gap: 8px;
	margin-bottom: 32px;
	height: 200px;
	position: relative;
}
.time-picker-wheels::before {
	content: '';
	position: absolute;
	left: 0;
	right: 0;
	top: 50%;
	transform: translateY(-50%);
	height: 48px;
	background: #f1f5f9;
	border-radius: 12px;
	pointer-events: none;
	z-index: 1;
}
.time-wheel {
	flex: 1;
	height: 200px;
	overflow-y: scroll;
	scroll-snap-type: y mandatory;
	-webkit-overflow-scrolling: touch;
	position: relative;
	z-index: 2;
	scrollbar-width: none;
	-ms-overflow-style: none;
}
.time-wheel::-webkit-scrollbar {
	display: none;
}
.time-wheel-item {
	height: 48px;
	display: flex;
	align-items: center;
	justify-content: center;
	scroll-snap-align: center;
	font-size: 1.5rem;
	font-weight: 600;
	color: #cbd5e1;
	transition: all 0.2s ease;
}
.time-wheel-item.selected {
	color: #0f172a;
	font-size: 2rem;
}
.time-separator {
	font-size: 2rem;
	font-weight: 700;
	color: #0f172a;
	z-index: 2;
	margin: 0 4px;
}
.time-picker-done {
	width: 100%;
	background: #0f172a;
	color: #fff;
	border: none;
	border-radius: 16px;
	padding: 18px;
	font-size: 1.1rem;
	font-weight: 700;
	cursor: pointer;
	transition: all 0.15s ease;
}
.time-picker-done:hover {
	background: #1e293b;
}

/* Calendar Picker Modal */
.calendar-picker-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.5);
	z-index: 3000;
	display: none;
	align-items: center;
	justify-content: center;
	padding: 20px;
}
.calendar-picker-overlay.active {
	display: flex;
}
.calendar-picker-modal {
	background: #fff;
	border-radius: 24px;
	width: 100%;
	max-width: 400px;
	padding: 24px;
	animation: scaleIn 0.3s ease;
}
@keyframes scaleIn {
	from {
		transform: scale(0.9);
		opacity: 0;
	}
	to {
		transform: scale(1);
		opacity: 1;
	}
}
.calendar-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 24px;
}
.calendar-header h3 {
	font-size: 1.1rem;
	font-weight: 700;
	color: #0f172a;
	margin: 0;
}
.calendar-nav {
	display: flex;
	gap: 8px;
}
.calendar-nav button {
	background: #f1f5f9;
	border: none;
	width: 36px;
	height: 36px;
	border-radius: 8px;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #64748b;
	transition: all 0.15s ease;
}
.calendar-nav button:hover {
	background: #e2e8f0;
	color: #0f172a;
}
.calendar-weekdays {
	display: grid;
	grid-template-columns: repeat(7, 1fr);
	gap: 8px;
	margin-bottom: 8px;
}
.calendar-weekday {
	text-align: center;
	font-size: 0.75rem;
	font-weight: 600;
	color: #94a3b8;
	padding: 8px 0;
}
.calendar-days {
	display: grid;
	grid-template-columns: repeat(7, 1fr);
	gap: 8px;
	margin-bottom: 20px;
}
.calendar-day {
	aspect-ratio: 1;
	border: none;
	background: #fff;
	border-radius: 12px;
	font-size: 0.95rem;
	font-weight: 600;
	color: #0f172a;
	cursor: pointer;
	transition: all 0.15s ease;
	display: flex;
	align-items: center;
	justify-content: center;
}
.calendar-day:hover:not(.disabled):not(.selected) {
	background: #f1f5f9;
}
.calendar-day.disabled {
	color: #cbd5e1;
	cursor: not-allowed;
}
.calendar-day.other-month {
	color: #cbd5e1;
}
.calendar-day.selected {
	background: #0f172a;
	color: #fff;
}
.calendar-day.today {
	border: 2px solid #0f172a;
}

/* Booking fee info bottom sheet */
.info-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.5);
	z-index: 3200;
	display: none;
	align-items: flex-end;
	justify-content: center;
}
.info-overlay.active { display: flex; }
.info-modal {
	background: #fff;
	border-radius: 24px 24px 0 0;
	width: 100%;
	max-width: 500px;
	padding: 24px;
	animation: slideUp 0.25s ease;
}
.info-modal h3 {
	margin: 0 0 16px 0;
	font-size: 1.25rem;
	font-weight: 800;
	color: #0f172a;
}
.info-row { display: flex; justify-content: space-between; align-items: baseline; margin: 12px 0; }
.info-subtitle { color: #0f172a; font-weight: 700; }
.info-note { color: #64748b; font-size: 0.95rem; line-height: 1.6; margin: 12px 0 16px 0; }
.info-close-btn { width: 100%; background: #0f172a; color: #fff; border: none; border-radius: 12px; padding: 16px; font-size: 1rem; font-weight: 700; cursor: pointer; }
.info-close-btn:hover { background: #1e293b; }
.info-close-x { background: none; border: none; padding: 6px; cursor: pointer; color: #64748b; }

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
.modal-button:disabled,
.modal-button[disabled] {
	opacity: 0.6;
	cursor: not-allowed;
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
.button-group {
	position: fixed !important;
	left: 50% !important;
	bottom: 80px !important;
	width: calc(100% - 40px);
	max-width: 560px;
	transform: translateX(-50%) translateZ(0);
	display: flex;
	flex-direction: column-reverse;
	gap: 12px;
	z-index: 2100;
	background: #fff;
	padding-top: 12px;
	will-change: transform;
	backface-visibility: hidden;
	contain: layout style paint;
	pointer-events: auto;
}
.button-group .modal-button {
	margin-top: 0 !important;
	margin-bottom: 0 !important;
	transform: translateZ(0);
}
.button-group .modal-button.next-button {
	position: static !important;
	margin: 0;
	background: #0f172a;
	color: #fff;
}
.button-group .modal-button.next-button:hover {
	background: #1e293b;
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

/* Summary layout tweaks */
.summary-container {
	max-width: 720px; /* a bit wider for readability */
	margin: 0 auto;   /* center on the page */
	padding: 0 20px 180px; /* keep consistent bottom space for fixed buttons */
}
@media (max-width: 640px) {
	.summary-container { max-width: 100%; padding: 0 16px 160px; }
}

.link-button {
	background: none;
	border: none;
	color: #0f172a;
	font-weight: 700;
	text-decoration: underline;
	cursor: pointer;
}

/* Posted confirmation */
.posted-center {
	text-align: center;
	padding-top: 12px;
}
.posted-illustration {
	font-size: 56px;
	line-height: 1;
	margin: 12px 0 20px 0;
}
.posted-subhead { color:#0f172a; font-weight:700; margin: 18px 0; }
.next-steps { list-style: none; padding: 0; margin: 0 0 32px 0; max-width: 640px; margin-left: auto; margin-right: auto; text-align: left; }
.next-steps li { display:flex; align-items:flex-start; gap:12px; margin: 18px 0; }
.step-badge { width:32px; height:32px; border-radius:50%; background:#0f172a; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:800; flex-shrink:0; }
.pro-tip { text-align:center; color:#64748b; }
@media (max-width: 480px) {
	.step-badge { width:28px; height:28px; font-size: 0.9rem; }
	.next-steps li { gap:10px; }
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
					<p class="step-title">Step 1 of 4</p>
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
					<p class="step-title" id="step2Title">Step 1 of 4</p>
					
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
						
						<div class="button-group">
							<button type="button" class="modal-button back-button" id="backSubStep2_2">Back</button>
							<button type="button" class="modal-button next-button" id="nextSubStep2_2">Next</button>
						</div>
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
						
						<div class="button-group">
							<button type="button" class="modal-button back-button" id="backSubStep2_3">Back</button>
							<button type="button" class="modal-button next-button" id="nextSubStep2_3">Next</button>
						</div>
					</div>
					
					<!-- Sub-step 4: Requirements (optional) -->
					<div class="sub-step" id="subStep2_4" style="display: none;">
						<h2 class="step-heading">Requirements <span style="font-weight: 400; color: #94a3b8;">(optional)</span></h2>
						<p class="step-subtitle">Are there any specific requirements for your task that the helper must meet?</p>
						
						<input 
							type="text" 
							name="requirements" 
							class="form-input" 
							placeholder="Do you have your own car?"
							id="requirementsInput"
						/>
						
						<label class="checkbox-label">
							<input type="checkbox" name="make_mandatory" id="makeMandatory" class="checkbox-input" />
							<span class="checkbox-text">Make resumes/portfolios/socials mandatory with offers</span>
						</label>
						
						<div class="button-group">
							<button type="button" class="modal-button back-button" id="backSubStep2_4">Back</button>
							<button type="button" class="modal-button next-button" id="nextStep2">Next</button>
						</div>
					</div>
				</div>

				<!-- Step 3: Details -->
				<div class="modal-step" data-step="3">
					<p class="step-title" id="step3Title">Step 1 of 2</p>
					
					<!-- Sub-step 1: Tell us where -->
					<div class="sub-step" id="subStep3_1">
						<h2 class="step-heading">Tell us where</h2>
						<p class="step-subtitle">Where do you need it done?</p>
						
						<div class="location-type-buttons">
							<button type="button" class="location-type-btn active" id="inPersonBtn">In-Person</button>
							<button type="button" class="location-type-btn" id="onlineBtn">Online</button>
						</div>
						
						<div id="locationFieldsContainer">
							<label class="form-label">Starting location</label>
							<input 
								type="text" 
								name="starting_location" 
								class="form-input" 
								value="Philippines"
								readonly
								style="margin-bottom: 16px;"
								id="startingLocationInput"
							/>
							
							<label class="form-label">Where in Philippines?</label>
							<div class="location-picker-input">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
									<circle cx="12" cy="10" r="3"/>
								</svg>
								<input 
									type="text" 
									name="location" 
									class="location-picker-field" 
									placeholder="Pick a location"
									required
									id="locationInput"
								/>
							</div>
							
							<label class="form-label">Ending location <span style="font-weight: 400; color: #94a3b8;">(optional)</span></label>
							<select name="ending_location" class="form-input" id="endingLocationInput">
								<option value="">Select a country</option>
								<option value="Philippines">Philippines</option>
								<option value="USA">USA</option>
								<option value="Japan">Japan</option>
							</select>
						</div>
						
						<button type="button" class="modal-button next-button" id="nextSubStep3_1">Next</button>
					</div>
					
					<!-- Sub-step 2: Date needed -->
					<div class="sub-step" id="subStep3_2" style="display: none;">
						<h2 class="step-heading">When do you want it done?</h2>
						
						<!-- Date Section -->
						<div style="margin-top: 24px; padding-bottom: 24px; border-bottom: 1px solid #e5e7eb;">
							<label class="form-label">Date</label>
							
							<button type="button" class="date-option-btn active" id="todayBtn" data-date="today">
								<span>Today, <?php echo date('j M'); ?></span>
							</button>
							
							<button type="button" class="date-option-btn" id="specificDateBtn" data-date="specific">
								<span>On a specific date</span>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; opacity: 0.5;">
									<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
									<line x1="16" y1="2" x2="16" y2="6"/>
									<line x1="8" y1="2" x2="8" y2="6"/>
									<line x1="3" y1="10" x2="21" y2="10"/>
								</svg>
							</button>
							
							<button type="button" class="date-option-btn" id="beforeDateBtn" data-date="before">
								<span>Before a specific date</span>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; opacity: 0.5;">
									<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
									<line x1="16" y1="2" x2="16" y2="6"/>
									<line x1="8" y1="2" x2="8" y2="6"/>
									<line x1="3" y1="10" x2="21" y2="10"/>
								</svg>
							</button>
							
							<button type="button" class="date-option-btn" id="dateRangeBtn" data-date="range">
								<span>Select a date range</span>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; opacity: 0.5;">
									<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
									<line x1="16" y1="2" x2="16" y2="6"/>
									<line x1="8" y1="2" x2="8" y2="6"/>
									<line x1="3" y1="10" x2="21" y2="10"/>
								</svg>
							</button>
							
							<input 
								type="hidden" 
								name="date_needed" 
								id="dateInput"
								value="<?php echo date('Y-m-d'); ?>"
							/>
						</div>
						
						<!-- Urgency Section -->
						<div style="margin-top: 24px; padding-bottom: 24px; border-bottom: 1px solid #e5e7eb;">
							<label class="form-label">Urgency</label>
							<p class="step-subtitle" style="margin-top: 4px; margin-bottom: 12px;">Is this time sensitive?</p>
							
							<button type="button" class="date-option-btn" id="urgentBtn" data-urgency="urgent">
								<span>Yes, it's urgent</span>
							</button>
							
							<button type="button" class="date-option-btn active" id="flexibleBtn" data-urgency="flexible">
								<span>No, I'm flexible</span>
							</button>
							
							<input type="hidden" name="urgency" id="urgencyInput" value="flexible" />
						</div>
						
						<!-- Time Section -->
						<div style="margin-top: 24px; margin-bottom: 100px;">
							<label class="form-label">Time</label>
							<p class="step-subtitle" style="margin-top: 4px; margin-bottom: 12px;">What time in the day would you like this done?</p>
							
							<button type="button" class="date-option-btn active" id="noPreferenceBtn" data-time="no-preference">
								<span>No preference</span>
							</button>
							
							<button type="button" class="date-option-btn" id="specificTimeBtn" data-time="specific">
								<span>Pick a specific time</span>
							</button>
							
							<button type="button" class="date-option-btn" id="timeRangeBtn" data-time="range">
								<span>Pick a time range</span>
							</button>
							
							<input type="hidden" name="time_preference" id="timePreferenceInput" value="no-preference" />
							
							<div id="specificTimeContainer" style="display: none; margin-top: 16px;">
								<input 
									type="text" 
									name="specific_time" 
									class="form-input" 
									placeholder="Select a specific time"
									id="specificTimeInput"
									readonly
									style="background: #f1f5f9; color: #0f172a; cursor: pointer;"
								/>
							</div>
							
							<div id="timeRangeContainer" style="display: none; margin-top: 16px;">
								<input 
									type="text" 
									name="time_range_start" 
									class="form-input" 
									placeholder="Start time"
									id="timeRangeStartInput"
									readonly
									style="background: #f1f5f9; color: #0f172a; cursor: pointer; margin-bottom: 12px;"
								/>
								<input 
									type="text" 
									name="time_range_end" 
									class="form-input" 
									placeholder="End time"
									id="timeRangeEndInput"
									readonly
									style="background: #f1f5f9; color: #0f172a; cursor: pointer;"
								/>
							</div>
						</div>
						
						<div class="button-group">
							<button type="button" class="modal-button back-button" id="backSubStep3_2">Back</button>
							<button type="button" class="modal-button next-button" id="nextStep3">Next</button>
						</div>
					</div>
				</div>

				<!-- Step 4: Budget -->
				<div class="modal-step" data-step="4">
					<p class="step-title" id="step4Title">Step 1 of 4</p>
					<h2 class="step-heading" id="step4Heading">Generate guest budget</h2>
					
					<!-- Sub-step 1: Payment Type & Estimated Hours -->
					<div class="sub-step" id="subStep4_1">
						<div style="margin-top: 24px;">
							<label class="form-label">Type of payment</label>
							<p class="step-subtitle" style="margin-top: 4px; margin-bottom: 12px;">Is your budget based on a one-time payment or on an hourly rate?</p>
							
							<div style="display: flex; gap: 12px; margin-bottom: 24px;">
								<button type="button" class="date-option-btn active" id="oneTimePaymentBtn" data-payment="one-time" style="flex: 1; text-align: center;">
									<span>One-time payment</span>
								</button>
								
								<button type="button" class="date-option-btn" id="perHourBtn" data-payment="per-hour" style="flex: 1; text-align: center;">
									<span>Per hour</span>
								</button>
							</div>
							
							<input type="hidden" name="payment_type" id="paymentTypeInput" value="one-time" />
						</div>
						
						<div style="margin-top: 24px;">
							<label class="form-label">Estimated hours</label>
							<p class="step-subtitle" style="margin-top: 4px; margin-bottom: 12px;">How many hours will this quest take?</p>
							<p class="step-subtitle" style="margin-top: 0; margin-bottom: 16px; font-size: 0.9rem;">E.g. If spread over 2 days but only needs 8 hours of actual work, enter 8 not 48.</p>
							
							<div style="background: #f1f5f9; padding: 16px; border-radius: 12px; margin-bottom: 16px;">
								<div style="display: flex; align-items: flex-start; gap: 8px;">
									<span style="font-size: 1.2rem;">💡</span>
									<div>
										<p style="margin: 0 0 8px 0; font-weight: 600; color: #0f172a; font-size: 0.95rem;">Need help estimating?</p>
										<ul style="margin: 0; padding-left: 20px; color: #64748b; font-size: 0.9rem; line-height: 1.6;">
											<li>Delivery: Include time for pickup, travel & drop-off</li>
											<li>Shift work: Enter hours per shift or total weekly hours</li>
											<li>Project-based: Enter total hours needed to finish the task</li>
										</ul>
									</div>
								</div>
							</div>
							
							<div style="position: relative; margin-bottom: 16px;">
								<input 
									type="number" 
									name="estimated_hours" 
									class="form-input" 
									placeholder="3"
									id="estimatedHoursInput"
									min="1"
									style="padding-right: 80px;"
								/>
								<span style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-weight: 500;">Hour(s)</span>
							</div>
							
							<div style="background: #fef3c7; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px;">
								<p style="margin: 0; color: #92400e; font-size: 0.9rem; line-height: 1.5;">
									<span style="font-size: 1rem;">✨</span> Our estimate, based on the title and description, suggests this quest will take approximately <strong>3 hours</strong>.
								</p>
							</div>
                            
							<div class="button-group">
								<button type="button" class="modal-button next-button" id="generateBudgetBtn" style="background: #f87171;">Generate guest budget</button>
							</div>
						</div>
					</div>
					
					<!-- Sub-step 2: Set a budget -->
					<div class="sub-step" id="subStep4_2" style="display: none;">

						<label class="form-label">Hero’s fee</label>
						<p class="step-subtitle" style="margin-top: 4px; margin-bottom: 12px;">This is the amount you’ll pay for the Hero’s time and services.</p>

						<div style="position: relative; margin-bottom: 16px;">
							<span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-weight: 700;">PHP</span>
							<input 
								type="number" 
								name="budget" 
								class="form-input" 
								placeholder="1134"
								id="budgetHeroFeeInput"
								min="80"
								step="1"
								style="padding-left: 64px;"
							/>
						</div>

						<!-- Pricing insights -->
						<div id="pricingInsights" style="background: #f8fafc; border: 2px solid #e5e7eb; border-radius: 16px; padding: 16px; margin: 16px 0;">
							<p style="margin: 0 0 8px 0; color: #0f172a; font-weight: 700; display: flex; align-items: center; gap: 8px;">
								<span>📈</span>
								<span>Pricing insights:</span>
							</p>
							<p id="insightMessage" style="margin: 6px 0 12px 0; color: #0f172a; font-weight: 600;">Hero fee is within the recommended range</p>
							<div style="height: 8px; background: #e5e7eb; border-radius: 999px; overflow: hidden; margin-bottom: 8px;">
								<div id="insightBar" style="height: 100%; width: 60%; background: #10b981;"></div>
							</div>
							<div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #64748b;">
								<span>Minimum: PHP<span id="minFeeText">80</span></span>
								<span>Recommended: PHP<span id="recommendedFeeText">0</span></span>
							</div>
							<button type="button" id="whyPriceToggle" class="generate-button" style="margin-top: 12px;">
								<span>Why price within this range?</span>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
							</button>
							<div id="whyPriceContent" style="display: none; margin-top: 8px; color: #64748b; font-size: 0.92rem;">
								<ul style="margin: 0; padding-left: 18px; line-height: 1.6;">
									<li>Based on your estimated hours and typical rates on Servisyo Hub.</li>
									<li>Urgency and in-person jobs can justify slightly higher fees.</li>
								</ul>
							</div>
						</div>

						<label class="checkbox-label" style="margin-top: 12px;">
							<input type="checkbox" class="checkbox-input" id="negotiableCheckbox" />
							<span class="checkbox-text">Let Heroes know the fee is negotiable</span>
						</label>
						<p style="color: #78716c; font-size: 0.9rem; margin: 8px 0 20px 0;">Even without this, Heroes can still offer other amounts, but being flexible upfront may get you more responses.</p>

						<!-- Totals -->
						<div style="margin: 20px 0 8px 0; display: flex; justify-content: space-between; align-items: baseline;">
							<span style="color: #0f172a; font-weight: 700;">Total you'll pay:</span>
							<span style="color: #0f172a; font-weight: 800; font-size: 1.2rem;">PHP<span id="totalPayText">0.00</span></span>
						</div>
						<p id="approxHourlyText" style="margin: 0 0 12px 0; color: #64748b; font-size: 0.9rem; text-align: right;">(approx. PHP0.00/hr)</p>

						<button type="button" id="priceBreakdownToggle" class="generate-button" aria-expanded="true" aria-controls="priceBreakdownContent" style="display: inline-flex; align-items: center; gap: 8px; margin: 8px 0 8px 0;">
							<span>Price breakdown</span>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 15 12 9 18 15"/></svg>
						</button>
						<div id="priceBreakdownContent" style="display: none; border-top: 1px solid #e5e7eb; padding-top: 12px; margin-bottom: 80px;">
							<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
								<span>Hero's fee</span>
								<span>PHP<span id="breakdownHeroFee">0.00</span></span>
							</div>
							<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
								<span style="display: inline-flex; align-items: center; gap: 8px;">Estimated booking fee 
									<button type="button" id="bookingFeeInfoBtn" aria-label="Booking fee details" style="background: none; border: none; cursor: pointer; padding: 0; color: #94a3b8;">
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="1"/></svg>
									</button>
								</span>
								<span>PHP<span id="breakdownBookingFee">0.00</span></span>
							</div>
						</div>

							<!-- Persistent spacer to keep toggle visible above fixed buttons -->
							<div aria-hidden="true" style="height: 100px;"></div>

						<input type="hidden" name="category" value="General" id="categoryInput" />

						<div class="button-group">
							<button type="button" class="modal-button back-button" id="backStep4Sub2">Back</button>
							<button type="button" class="modal-button next-button" id="submitBudgetBtn">Next</button>
						</div>
					</div>

					<!-- Sub-step 3: Additional cost -->
					<div class="sub-step" id="subStep4_3" style="display: none;">

						<label class="form-label">Cost of purchases (optional)</label>
						<p class="step-subtitle" style="margin-top: 4px; margin-bottom: 12px;">Will the Hero need to buy anything to complete the quest, like materials or items? Add an estimated cost below.</p>

						<div style="position: relative; margin-bottom: 16px; max-width: 360px;">
							<span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-weight: 700;">PHP</span>
							<input 
								type="number" 
								name="additional_cost" 
								class="form-input" 
								placeholder="0"
								id="additionalCostInput"
								min="0"
								step="1"
								style="padding-left: 64px;"
							/>
						</div>

						<!-- Totals (with additional cost) -->
						<div style="margin: 24px 0 8px 0; display: flex; justify-content: space-between; align-items: baseline; border-top: 1px solid #e5e7eb; padding-top: 16px;">
							<span style="color: #0f172a; font-weight: 700;">Total you'll pay:</span>
							<span style="color: #0f172a; font-weight: 800; font-size: 1.2rem;">PHP<span id="totalPayText_ac">0.00</span></span>
						</div>
						<p id="approxHourlyText_ac" style="margin: 0 0 12px 0; color: #64748b; font-size: 0.9rem; text-align: right;">(approx. PHP0.00/hr)</p>

						<button type="button" id="priceBreakdownToggle_ac" class="generate-button" aria-expanded="true" aria-controls="priceBreakdownContent_ac" style="display: inline-flex; align-items: center; gap: 8px; margin: 8px 0 8px 0;">
							<span>Price breakdown</span>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 15 12 9 18 15"/></svg>
						</button>
						<div id="priceBreakdownContent_ac" style="display: none; border-top: 1px solid #e5e7eb; padding-top: 12px; margin-bottom: 80px;">
							<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
								<span>Hero's fee</span>
								<span>PHP<span id="breakdownHeroFee_ac">0.00</span></span>
							</div>
							<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px;">
								<span style="display: inline-flex; align-items: center; gap: 8px;">Estimated booking fee 
									<button type="button" id="bookingFeeInfoBtn_ac" aria-label="Booking fee details" style="background: none; border: none; cursor: pointer; padding: 0; color: #94a3b8;">
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="1"/></svg>
									</button>
								</span>
								<span>PHP<span id="breakdownBookingFee_ac">0.00</span></span>
							</div>
							<div style="display: flex; justify-content: space-between;">
								<span>Cost of purchases</span>
								<span>PHP<span id="breakdownAdditionalCost_ac">0.00</span></span>
							</div>
						</div>

						<!-- Persistent spacer to keep toggle visible above fixed buttons -->
						<div aria-hidden="true" style="height: 100px;"></div>

						<div class="button-group">
							<button type="button" class="modal-button back-button" id="backStep4Sub3">Back</button>
							<button type="button" class="modal-button next-button" id="nextStep4_3">Next</button>
						</div>
					</div>

						<!-- Sub-step 4: Review budget -->
						<div class="sub-step" id="subStep4_4" style="display: none;">
							<p class="step-subtitle">Please review your budget details before posting.</p>

							<!-- Totals (review) -->
							<div style="margin: 20px 0 8px 0; display: flex; justify-content: space-between; align-items: baseline;">
								<span style="color: #0f172a; font-weight: 700;">Total you'll pay:</span>
								<span style="color: #0f172a; font-weight: 800; font-size: 1.2rem;">PHP<span id="totalPayText_rv">0.00</span></span>
							</div>
							<p id="approxHourlyText_rv" style="margin: 0 0 12px 0; color: #64748b; font-size: 0.9rem; text-align: right;">(approx. PHP0.00/hr)</p>

							<button type="button" id="priceBreakdownToggle_rv" class="generate-button" aria-expanded="true" aria-controls="priceBreakdownContent_rv" style="display: inline-flex; align-items: center; gap: 8px; margin: 8px 0 8px 0;">
								<span>Price breakdown</span>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 15 12 9 18 15"/></svg>
							</button>
							<div id="priceBreakdownContent_rv" style="display: none; border-top: 1px solid #e5e7eb; padding-top: 12px; margin-bottom: 80px;">
								<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
									<span>Hero's fee</span>
									<span>PHP<span id="breakdownHeroFee_rv">0.00</span></span>
								</div>
								<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px;">
									<span style="display: inline-flex; align-items: center; gap: 8px;">Estimated booking fee 
										<button type="button" id="bookingFeeInfoBtn_rv" aria-label="Booking fee details" style="background: none; border: none; cursor: pointer; padding: 0; color: #94a3b8;">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="1"/></svg>
										</button>
									</span>
									<span>PHP<span id="breakdownBookingFee_rv">0.00</span></span>
								</div>
								<div style="display: flex; justify-content: space-between;">
									<span>Cost of purchases</span>
									<span>PHP<span id="breakdownAdditionalCost_rv">0.00</span></span>
								</div>
							</div>

							<div class="button-group">
								<button type="button" class="modal-button back-button" id="backStep4Sub4">Back</button>
								<button type="button" class="modal-button next-button" id="postRequestBtn">Next</button>
							</div>
						</div>

					<!-- Step 5: Summary -->
					<div class="modal-step" data-step="5">
						<div class="summary-container">
							<h2 class="step-heading" id="step5Heading">Summary</h2>

							<!-- Title and total -->
							<h3 id="summaryTitleText" style="margin: 0 0 8px 0; color:#0f172a; font-size:1.3rem; font-weight:800;"></h3>
							<p id="summaryTotalText" style="margin: 0 0 24px 0; color:#0f172a; font-weight:800;"></p>

							<!-- Key details -->
							<div style="display:grid; gap:16px; margin-bottom:20px;">
								<div>
									<p style="margin:0; color:#94a3b8; font-weight:600; font-size:0.9rem;">Location</p>
									<p id="summaryLocationText" style="margin:4px 0 0 0; color:#0f172a; font-weight:700;"></p>
								</div>
								<div>
									<p style="margin:0; color:#94a3b8; font-weight:600; font-size:0.9rem;">Completion Date</p>
									<p id="summaryCompletionText" style="margin:4px 0 0 0; color:#0f172a; font-weight:700;"></p>
								</div>
								<div>
									<p style="margin:0; color:#94a3b8; font-weight:600; font-size:0.9rem;">Duration</p>
									<p id="summaryDurationText" style="margin:4px 0 0 0; color:#0f172a; font-weight:700;"></p>
								</div>
							</div>

							<!-- Description -->
							<div style="margin-bottom:20px;">
								<p style="margin:0 0 6px 0; color:#94a3b8; font-weight:600; font-size:0.9rem;">Description</p>
								<p id="summaryDescriptionText" style="margin:0; color:#0f172a; line-height:1.6;"></p>
							</div>

							<!-- Skills/Requirements (optional) -->
							<div id="summaryRequirementsBlock" style="margin-bottom:20px; display:none;">
								<p style="margin:0 0 6px 0; color:#94a3b8; font-weight:600; font-size:0.9rem;">Skills and Experience Required</p>
								<p id="summaryRequirementsText" style="margin:0; color:#0f172a; line-height:1.6;"></p>
							</div>

							<!-- Heroes required -->
							<div style="margin-bottom:20px;">
								<p style="margin:0 0 6px 0; color:#94a3b8; font-weight:600; font-size:0.9rem;">Heroes Required</p>
								<p id="summaryHeroesText" style="margin:0; color:#0f172a; font-weight:700;"></p>
							</div>

							<!-- Screening Questions -->
							<div id="summaryQuestionsBlock" style="margin-bottom:20px; display:none;">
								<p style="margin:0 0 6px 0; color:#94a3b8; font-weight:600; font-size:0.9rem;">Screening Questions</p>
								<ol id="summaryQuestionsList" style="margin:0; padding-left:18px; color:#0f172a; line-height:1.6;"></ol>
							</div>

							<div class="button-group">
								<button type="button" class="modal-button back-button" id="summaryEditBtn">Go back and edit</button>
								<button type="submit" class="modal-button next-button" id="finalPostBtn" style="background:#f87171;">Post request</button>
							</div>
						</div>
					</div>

					<!-- Step 6: Code of Conduct -->
					<div class="modal-step" data-step="6">
						<div class="summary-container">
							<h2 class="step-heading">Code of Conduct</h2>
							<p class="guidance-text" style="margin-bottom: 12px;">Before you post this quest,</p>
							<p class="step-subtitle" style="margin-top: 0;">You agree to:</p>
							<ul class="guidance-list" style="margin-bottom: 16px;">
								<li>Price quest fairly</li>
								<li>Remain contactable and keep communications within the Quest app</li>
								<li>Pay the Hero when the quest is completed</li>
							</ul>

							<p class="step-subtitle">You confirm that this quest is not:</p>
							<ul class="guidance-list" style="margin-bottom: 20px;">
								<li>Advertising</li>
								<li>Contain inaccurate/false information</li>
								<li>Illegal and inappropriate acts</li>
								<li>Financial loans</li>
								<li>Sale of items</li>
								<li>Listing of services</li>
								<li>Academic deceit</li>
								<li>Referral posts</li>
							</ul>

							<p class="guidance-text" style="font-weight:700; color:#0f172a;">Quests violating the above will be deleted, and your account may be banned. No refunds for paid features if guidelines are violated.</p>

							<label class="checkbox-label" style="margin-top: 20px;">
								<input type="checkbox" id="agreeCoc" class="checkbox-input" />
								<span class="checkbox-text">I have read and agree to this code of conduct and Quest's <a href="#" style="color:#0f172a; font-weight:700; text-decoration: underline;">Terms of service</a>.</span>
							</label>

							<label class="checkbox-label" style="margin-top: 0;">
								<input type="checkbox" id="agreeCancellation" class="checkbox-input" />
								<span class="checkbox-text">I agree to being charged a <a href="#" style="color:#0f172a; font-weight:700; text-decoration: underline;">cancellation fee</a> if I cause the quest to be cancelled after confirming an offer.</span>
							</label>

							<div class="button-group">
								<button type="button" class="modal-button next-button" id="agreeTermsBtn" disabled>I agree to the terms</button>
							</div>

							<p style="text-align:center; margin-top: 18px;">
								<button type="button" class="link-button" id="editQuestLink">Edit my quest</button>
							</p>
						</div>
					</div>

					<!-- Step 7: Posted confirmation -->
					<div class="modal-step" data-step="7">
						<div class="summary-container posted-center">
							<h2 class="step-heading" style="text-align:center;">Quest posted!</h2>
							<div class="posted-illustration">🎉</div>
							<p class="posted-subhead">Here's what's next:</p>
							<ul class="next-steps">
								<li>
									<span class="step-badge">1</span>
									<span>Heroes will make offers to your quest</span>
								</li>
								<li>
									<span class="step-badge">2</span>
									<span>Compare and accept offers from <strong>My quests</strong></span>
								</li>
								<li>
									<span class="step-badge">3</span>
									<span>Release the securely held payment to your Hero after quest completion & review them</span>
								</li>
							</ul>

							<p class="pro-tip">✨ <strong>Pro tip</strong> ✨<br/>Upload a profile picture to stand out! 🪄</p>

							<div class="button-group">
								<a href="./my-jobs.php" class="modal-button next-button" style="display:block; text-align:center; background:#0f172a; color:#fff;">Go to my quest</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>

	<!-- Time Picker Modal -->
	<div class="time-picker-overlay" id="timePickerOverlay">
		<div class="time-picker-modal">
			<div class="time-picker-header">
				<h3>Select time</h3>
				<button type="button" class="time-picker-close" id="closeTimePicker">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>
			</div>
			
			<div class="time-picker-wheels">
				<div class="time-wheel" id="hourWheel"></div>
				<span class="time-separator">:</span>
				<div class="time-wheel" id="minuteWheel"></div>
				<div class="time-wheel" id="periodWheel"></div>
			</div>
			
			<button type="button" class="time-picker-done" id="timePickerDone">Done</button>
		</div>
	</div>

	<!-- Calendar Picker Modal -->
	<div class="calendar-picker-overlay" id="calendarPickerOverlay">
		<div class="calendar-picker-modal">
			<div class="calendar-header">
				<h3 id="calendarMonthYear">November 2025</h3>
				<div class="calendar-nav">
					<button type="button" id="prevMonth">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="15 18 9 12 15 6"></polyline>
						</svg>
					</button>
					<button type="button" id="nextMonth">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="9 18 15 12 9 6"></polyline>
						</svg>
					</button>
				</div>
			</div>
			
			<div class="calendar-weekdays">
				<div class="calendar-weekday">Su</div>
				<div class="calendar-weekday">Mo</div>
				<div class="calendar-weekday">Tu</div>
				<div class="calendar-weekday">We</div>
				<div class="calendar-weekday">Th</div>
				<div class="calendar-weekday">Fr</div>
				<div class="calendar-weekday">Sa</div>
			</div>
			
			<div class="calendar-days" id="calendarDays"></div>
		</div>
	</div>

	<!-- Booking Fee Info Bottom Sheet -->
	<div class="info-overlay" id="bookingFeeInfoOverlay">
		<div class="info-modal">
			<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
				<h3>Estimated booking fee</h3>
				<button type="button" class="info-close-x" id="bookingFeeInfoCloseX" aria-label="Close">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>
			</div>

			<div class="info-row">
				<span class="info-subtitle">Processing fee <span style="font-weight: 400; color:#64748b;">(Non-refundable)</span></span>
				<span>PHP<span id="infoProcessingFee">0.00</span></span>
			</div>
			<p class="info-note">This fee goes towards our payment provider, <a href="https://stripe.com/" target="_blank" rel="noopener" style="color:#0f172a; font-weight:700; text-decoration: underline;">Stripe</a>, to hold payments securely and process payments efficiently.</p>
			<p class="info-note" style="margin-top:-6px;"><a href="#" style="color:#ef4444; font-weight:700; text-decoration: none;">Learn more about how payments are secured →</a></p>

			<div class="info-row" style="margin-top: 18px;">
				<span class="info-subtitle">Connection fee</span>
				<span>PHP<span id="infoConnectionFee">0.00</span></span>
			</div>
			<p class="info-note">This fee covers the costs of keeping the platform running, support, and bringing more Heroes onto the platform.</p>

			<p class="info-note" style="color:#78716c; font-size: 0.85rem;">Note: If the quest is cancelled after the Hero confirms availability, your booking fees will not be refunded.</p>

			<button type="button" class="info-close-btn" id="bookingFeeInfoGotIt">Got it</button>
		</div>
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
			// If steps >=5 are nested under Step 4, keep Step 4 visible as a container
			if (stepNumber >= 5) {
				const step4 = document.querySelector('.modal-step[data-step="4"]');
				if (step4) {
					step4.classList.add('active');
					// Hide all Step 4 sub-steps while on Summary
					step4.querySelectorAll('.sub-step').forEach(s => s.style.display = 'none');
				}
				// Hide Step 4 header/title so only Summary is visible
				const s4Title = document.getElementById('step4Title');
				const s4Heading = document.getElementById('step4Heading');
				if (s4Title) s4Title.style.display = 'none';
				if (s4Heading) s4Heading.style.display = 'none';
			} else {
				// Restore Step 4 header/title when not on Summary
				const s4Title = document.getElementById('step4Title');
				const s4Heading = document.getElementById('step4Heading');
				if (s4Title) s4Title.style.display = '';
				if (s4Heading) s4Heading.style.display = '';
			}

			// Hide step-progress on final flows (steps >= 5)
			try {
				const progress = document.querySelector('.step-progress');
				if (progress) progress.style.display = (stepNumber >= 5 ? 'none' : 'flex');
			} catch(_){}
			
			// Update progress indicators
			updateStepProgress(stepNumber);
			
			currentStep = stepNumber;
		}
		
		// Header back button
		// Enhanced: when in Step 4, go back within sub-steps first
		let currentSubStep4 = 1; // 1: generate, 2: budget, 3: additional cost
		modalBack.addEventListener('click', function() {
			if (currentStep === 7) {
				// After posting, back goes to My quests
				window.location.href = './my-jobs.php';
				return;
			}
			if (currentStep === 6) {
				goToStep(5);
				return;
			}
			if (currentStep === 5) {
				// Back from Summary to Budget - Additional cost
				goToStep(4);
				document.getElementById('subStep4_1').style.display = 'none';
				document.getElementById('subStep4_2').style.display = 'none';
				document.getElementById('subStep4_3').style.display = 'none';
				document.getElementById('subStep4_4').style.display = 'block';
				document.getElementById('step4Title').textContent = 'Step 4 of 4';
				document.getElementById('step4Heading').textContent = 'Review budget';
				currentSubStep4 = 4;
				return;
			}
			if (currentStep === 4) {
				if (currentSubStep4 === 4) {
					// Back to Step 4 - Sub-step 3
					document.getElementById('subStep4_4').style.display = 'none';
					document.getElementById('subStep4_3').style.display = 'block';
					document.getElementById('step4Title').textContent = 'Step 3 of 4';
					document.getElementById('step4Heading').textContent = 'Additional cost';
					currentSubStep4 = 3;
					return;
				}
				if (currentSubStep4 === 3) {
					// Back to Step 4 - Sub-step 2
					document.getElementById('subStep4_3').style.display = 'none';
					document.getElementById('subStep4_2').style.display = 'block';
					document.getElementById('step4Title').textContent = 'Step 2 of 4';
					document.getElementById('step4Heading').textContent = 'Set a budget';
					currentSubStep4 = 2;
					return;
				}
				if (currentSubStep4 === 2) {
					// Back to Step 4 - Sub-step 1
					document.getElementById('subStep4_2').style.display = 'none';
					document.getElementById('subStep4_1').style.display = 'block';
					document.getElementById('step4Title').textContent = 'Step 1 of 4';
					document.getElementById('step4Heading').textContent = 'Generate guest budget';
					currentSubStep4 = 1;
					return;
				}
			}
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
			if (step === 2) {
				// Hide all sub-steps of Step 2
				document.querySelectorAll('#subStep2_1, #subStep2_2, #subStep2_3, #subStep2_4').forEach(sub => {
					sub.style.display = 'none';
				});
				// Show target sub-step
				document.getElementById(`subStep${step}_${subStep}`).style.display = 'block';
				currentSubStep = subStep;
				// Update step title
				const stepTitle = document.getElementById('step2Title');
				stepTitle.textContent = `Step ${subStep} of 4`;
			} else if (step === 3) {
				// Hide all sub-steps of Step 3
				document.querySelectorAll('#subStep3_1, #subStep3_2').forEach(sub => {
					sub.style.display = 'none';
				});
				// Show target sub-step
				document.getElementById(`subStep${step}_${subStep}`).style.display = 'block';
				currentSubStep = subStep;
				// Update step title
				const stepTitle = document.getElementById('step3Title');
				stepTitle.textContent = `Step ${subStep} of 2`;
			}
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
		document.getElementById('nextStep2').addEventListener('click', function() {
			goToStep(3);
			showSubStep(3, 1); // Show first sub-step of Step 3
		});
		
		// Step 3 sub-step 1 -> sub-step 2
		document.getElementById('nextSubStep3_1').addEventListener('click', function() {
			const locationInput = document.getElementById('locationInput');
			const onlineBtn = document.getElementById('onlineBtn');
			const isOnline = onlineBtn.classList.contains('active');
			
			// Only validate location if In-Person mode is active
			if (!isOnline && locationInput.value.trim().length === 0) {
				alert('Please select a location.');
				return;
			}
			
			showSubStep(3, 2);
		});
		
		// Back from Step 3 sub-step 2 to sub-step 1
		document.getElementById('backSubStep3_2').addEventListener('click', function() {
			showSubStep(3, 1);
		});
		
		// Step 3 -> Step 4
		document.getElementById('nextStep3').addEventListener('click', function() {
			// Close any open overlays that could block clicks
			try {
				const calendarPickerOverlay = document.getElementById('calendarPickerOverlay');
				if (calendarPickerOverlay) calendarPickerOverlay.classList.remove('active');
				const timePickerOverlay = document.getElementById('timePickerOverlay');
				if (timePickerOverlay) timePickerOverlay.classList.remove('active');
			} catch (e) { /* noop */ }
			
			const dateInput = document.getElementById('dateInput');
			const timePreferenceInput = document.getElementById('timePreferenceInput');
			
			// Fallback defaults to avoid being blocked by empty values
			if (!dateInput || !dateInput.value) {
				const today = new Date();
				const y = today.getFullYear();
				const m = String(today.getMonth() + 1).padStart(2, '0');
				const d = String(today.getDate()).padStart(2, '0');
				if (dateInput) dateInput.value = `${y}-${m}-${d}`;
			}
			if (!timePreferenceInput || !timePreferenceInput.value) {
				if (timePreferenceInput) timePreferenceInput.value = 'no-preference';
			}
			
			// Proceed to Budget step
			goToStep(4);
			// Ensure user sees the top of the next step
			try { window.scrollTo({ top: 0, behavior: 'instant' }); } catch (_) { window.scrollTo(0,0); }
		});
		
		// Location type toggle
		document.getElementById('inPersonBtn').addEventListener('click', function() {
			this.classList.add('active');
			document.getElementById('onlineBtn').classList.remove('active');
			// Show location fields
			document.getElementById('locationFieldsContainer').style.display = 'block';
			document.getElementById('locationInput').required = true;
		});
		
		document.getElementById('onlineBtn').addEventListener('click', function() {
			this.classList.add('active');
			document.getElementById('inPersonBtn').classList.remove('active');
			// Hide location fields
			document.getElementById('locationFieldsContainer').style.display = 'none';
			document.getElementById('locationInput').required = false;
		});
		
		// Date option buttons toggle (only date section buttons)
		const todayBtn = document.getElementById('todayBtn');
		const specificDateBtn = document.getElementById('specificDateBtn');
		const beforeDateBtn = document.getElementById('beforeDateBtn');
		const dateRangeBtnOption = document.getElementById('dateRangeBtn');
		const dateInput = document.getElementById('dateInput');
		
		const dateButtons = [todayBtn, specificDateBtn, beforeDateBtn, dateRangeBtnOption];
		
		// Calendar Picker functionality
		const calendarPickerOverlay = document.getElementById('calendarPickerOverlay');
		const calendarDays = document.getElementById('calendarDays');
		const calendarMonthYear = document.getElementById('calendarMonthYear');
		const prevMonth = document.getElementById('prevMonth');
		const nextMonth = document.getElementById('nextMonth');
		
		let currentCalendarDate = new Date();
		let selectedDate = null;
		let currentDateType = 'specific'; // 'specific', 'before', or 'range'
		let rangeStartDate = null;
		let rangeEndDate = null;
		
		function openCalendar(dateType) {
			currentDateType = dateType;
			currentCalendarDate = new Date();
			
			// Reset range dates when opening for range selection
			if (dateType === 'range') {
				rangeStartDate = null;
				rangeEndDate = null;
			}
			
			calendarPickerOverlay.classList.add('active');
			renderCalendar();
		}
		
		function closeCalendar() {
			calendarPickerOverlay.classList.remove('active');
		}
		
		function renderCalendar() {
			const year = currentCalendarDate.getFullYear();
			const month = currentCalendarDate.getMonth();
			
			// Update header
			const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
				'July', 'August', 'September', 'October', 'November', 'December'];
			calendarMonthYear.textContent = `${monthNames[month]} ${year}`;
			
			// Get first day of month and number of days
			const firstDay = new Date(year, month, 1).getDay();
			const daysInMonth = new Date(year, month + 1, 0).getDate();
			const daysInPrevMonth = new Date(year, month, 0).getDate();
			
			// Clear calendar
			calendarDays.innerHTML = '';
			
			// Add previous month's days
			for (let i = firstDay - 1; i >= 0; i--) {
				const day = daysInPrevMonth - i;
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'calendar-day other-month';
				btn.textContent = day;
				calendarDays.appendChild(btn);
			}
			
			// Add current month's days
			const today = new Date();
			for (let day = 1; day <= daysInMonth; day++) {
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'calendar-day';
				btn.textContent = day;
				
				const currentDate = new Date(year, month, day);
				
				// Mark today
				if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
					btn.classList.add('today');
				}
				
				// Mark selected date (for specific and before)
				if (selectedDate && 
					year === selectedDate.getFullYear() && 
					month === selectedDate.getMonth() && 
					day === selectedDate.getDate()) {
					btn.classList.add('selected');
				}
				
				// Mark range dates
				if (currentDateType === 'range') {
					if (rangeStartDate && rangeEndDate) {
						if (currentDate >= rangeStartDate && currentDate <= rangeEndDate) {
							btn.classList.add('selected');
						}
					} else if (rangeStartDate) {
						if (currentDate.getTime() === rangeStartDate.getTime()) {
							btn.classList.add('selected');
						}
					}
				}
				
				// Add click handler
				btn.addEventListener('click', function() {
					if (currentDateType === 'range') {
						// Handle range selection
						if (!rangeStartDate || (rangeStartDate && rangeEndDate)) {
							// Start new range
							rangeStartDate = new Date(year, month, day);
							rangeEndDate = null;
							renderCalendar(); // Re-render to show selection
						} else {
							// Set end date
							const clickedDate = new Date(year, month, day);
							if (clickedDate < rangeStartDate) {
								// If clicked date is before start, swap them
								rangeEndDate = rangeStartDate;
								rangeStartDate = clickedDate;
							} else {
								rangeEndDate = clickedDate;
							}
							
							// Update date input with range
							const startYear = rangeStartDate.getFullYear();
							const startMonth = String(rangeStartDate.getMonth() + 1).padStart(2, '0');
							const startDay = String(rangeStartDate.getDate()).padStart(2, '0');
							dateInput.value = `${startYear}-${startMonth}-${startDay}`;
							
							// Update button text
							const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
								'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
							const startText = `${rangeStartDate.getDate()} ${monthNames[rangeStartDate.getMonth()]} ${rangeStartDate.getFullYear()}`;
							const endText = `${rangeEndDate.getDate()} ${monthNames[rangeEndDate.getMonth()]} ${rangeEndDate.getFullYear()}`;
							dateRangeBtnOption.querySelector('span').textContent = `${startText} - ${endText}`;
							
							closeCalendar();
						}
					} else {
						// Handle single date selection
						selectedDate = new Date(year, month, day);
						
						// Update date input
						const selectedYear = selectedDate.getFullYear();
						const selectedMonth = String(selectedDate.getMonth() + 1).padStart(2, '0');
						const selectedDay = String(selectedDate.getDate()).padStart(2, '0');
						dateInput.value = `${selectedYear}-${selectedMonth}-${selectedDay}`;
						
						// Update button text based on date type
						const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
							'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
						const dateText = `${selectedDay} ${monthNames[selectedDate.getMonth()]}`;
						
						if (currentDateType === 'specific') {
							specificDateBtn.querySelector('span').textContent = `On ${dateText}`;
						} else if (currentDateType === 'before') {
							beforeDateBtn.querySelector('span').textContent = `Before ${dateText}`;
						}
						
						closeCalendar();
					}
				});
				
				calendarDays.appendChild(btn);
			}
			
			// Add next month's days to fill grid
			const totalCells = calendarDays.children.length;
			const remainingCells = 42 - totalCells; // 6 rows * 7 days
			for (let day = 1; day <= remainingCells; day++) {
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'calendar-day other-month';
				btn.textContent = day;
				calendarDays.appendChild(btn);
			}
		}
		
		prevMonth.addEventListener('click', function() {
			currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
			renderCalendar();
		});
		
		nextMonth.addEventListener('click', function() {
			currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
			renderCalendar();
		});
		
		calendarPickerOverlay.addEventListener('click', function(e) {
			if (e.target === calendarPickerOverlay) {
				closeCalendar();
			}
		});
		
		// Date button handlers
		dateButtons.forEach(button => {
			button.addEventListener('click', function() {
				// Remove active class from date buttons only
				dateButtons.forEach(btn => btn.classList.remove('active'));
				// Add active class to clicked button
				this.classList.add('active');
				
				// Reset all button texts to default when switching
				specificDateBtn.querySelector('span').textContent = 'On a specific date';
				beforeDateBtn.querySelector('span').textContent = 'Before a specific date';
				dateRangeBtnOption.querySelector('span').textContent = 'Select a date range';
				
				// Set date value based on selection
				const dateType = this.getAttribute('data-date');
				const today = new Date();
				
				if (dateType === 'today') {
					const year = today.getFullYear();
					const month = String(today.getMonth() + 1).padStart(2, '0');
					const day = String(today.getDate()).padStart(2, '0');
					dateInput.value = `${year}-${month}-${day}`;
				} else {
					// Open calendar for other options
					openCalendar(dateType);
				}
			});
		});
		
		// Urgency buttons toggle
		const urgentBtn = document.getElementById('urgentBtn');
		const flexibleBtn = document.getElementById('flexibleBtn');
		const urgencyInput = document.getElementById('urgencyInput');
		
		urgentBtn.addEventListener('click', function() {
			this.classList.add('active');
			flexibleBtn.classList.remove('active');
			urgencyInput.value = 'urgent';
		});
		
		flexibleBtn.addEventListener('click', function() {
			this.classList.add('active');
			urgentBtn.classList.remove('active');
			urgencyInput.value = 'flexible';
		});
		
		// Time preference buttons toggle
		const noPreferenceBtn = document.getElementById('noPreferenceBtn');
		const specificTimeBtn = document.getElementById('specificTimeBtn');
		const timeRangeBtnOption = document.getElementById('timeRangeBtn');
		const timePreferenceInput = document.getElementById('timePreferenceInput');
		const specificTimeContainer = document.getElementById('specificTimeContainer');
		const timeRangeContainer = document.getElementById('timeRangeContainer');
		
		noPreferenceBtn.addEventListener('click', function() {
			// Remove active from all time buttons
			noPreferenceBtn.classList.add('active');
			specificTimeBtn.classList.remove('active');
			timeRangeBtnOption.classList.remove('active');
			
			// Hide time input containers
			specificTimeContainer.style.display = 'none';
			timeRangeContainer.style.display = 'none';
			
			// Update hidden input
			timePreferenceInput.value = 'no-preference';
		});
		
		specificTimeBtn.addEventListener('click', function() {
			// Remove active from all time buttons
			noPreferenceBtn.classList.remove('active');
			specificTimeBtn.classList.add('active');
			timeRangeBtnOption.classList.remove('active');
			
			// Show specific time container
			specificTimeContainer.style.display = 'block';
			timeRangeContainer.style.display = 'none';
			
			// Update hidden input
			timePreferenceInput.value = 'specific-time';
			
			// Open time picker if no time selected
			if (!specificTimeInput.value) {
				currentTimeInputType = 'specific';
				openTimePicker();
			}
		});
		
		timeRangeBtnOption.addEventListener('click', function() {
			// Remove active from all time buttons
			noPreferenceBtn.classList.remove('active');
			specificTimeBtn.classList.remove('active');
			timeRangeBtnOption.classList.add('active');
			
			// Show time range container
			specificTimeContainer.style.display = 'none';
			timeRangeContainer.style.display = 'block';
			
			// Update hidden input
			timePreferenceInput.value = 'time-range';
		});
		
		// Payment type toggle (Step 4)
		const oneTimePaymentBtn = document.getElementById('oneTimePaymentBtn');
		const perHourBtn = document.getElementById('perHourBtn');
		const paymentTypeInput = document.getElementById('paymentTypeInput');
		
		oneTimePaymentBtn.addEventListener('click', function() {
			this.classList.add('active');
			perHourBtn.classList.remove('active');
			paymentTypeInput.value = 'one-time';
		});
		
		perHourBtn.addEventListener('click', function() {
			this.classList.add('active');
			oneTimePaymentBtn.classList.remove('active');
			paymentTypeInput.value = 'per-hour';
		});
		
		// Generate budget button -> compute and go to Budget sub-step 2
		document.getElementById('generateBudgetBtn').addEventListener('click', function() {
			const hoursVal = parseFloat(document.getElementById('estimatedHoursInput').value || '0');
			const paymentType = (document.getElementById('paymentTypeInput')?.value || 'one-time');
			if (!hoursVal || hoursVal <= 0) {
				alert('Please enter estimated hours.');
				return;
			}

			// Heuristic pricing: base hourly; adjustables could be added later
			const BASE_HOURLY = 378; // PHP per hour baseline
			const MIN_FEE = 80;      // Minimum allowed fee
			let recommended = Math.max(MIN_FEE, Math.round(hoursVal * BASE_HOURLY));
			if (paymentType === 'one-time') {
				// Keep same recommendation for now; future: category/complexity modifiers
				recommended = Math.max(MIN_FEE, recommended);
			}

			// Prefill Sub-step 2 values
			const budgetInput = document.getElementById('budgetHeroFeeInput');
			budgetInput.value = recommended;
			document.getElementById('recommendedFeeText').textContent = recommended.toString();
			document.getElementById('minFeeText').textContent = MIN_FEE.toString();

			// Compute totals and breakdown
			const BOOKING_FEE_RATE = 0.1107; // ~11.07%
			const bookingFee = Math.round((recommended * BOOKING_FEE_RATE) * 100) / 100;
			// Split booking fee for info sheet
			const processingFee = Math.round((bookingFee * 0.65) * 100) / 100;
			const connectionFee = Math.round((bookingFee - processingFee) * 100) / 100;
			const totalPay = Math.round((recommended + bookingFee) * 100) / 100;
			document.getElementById('breakdownHeroFee').textContent = recommended.toFixed(2);
			document.getElementById('breakdownBookingFee').textContent = bookingFee.toFixed(2);
			document.getElementById('totalPayText').textContent = totalPay.toFixed(2);
			document.getElementById('approxHourlyText').textContent = `(approx. PHP${(recommended / hoursVal).toFixed(2)}/hr)`;
			// Update info modal values
			document.getElementById('infoProcessingFee').textContent = processingFee.toFixed(2);
			document.getElementById('infoConnectionFee').textContent = connectionFee.toFixed(2);

			// Simple insight bar position within 0-2x recommended
			const insightBar = document.getElementById('insightBar');
			insightBar.style.width = '60%';
			document.getElementById('insightMessage').textContent = 'Hero fee is within the recommended range';

				// Show sub-step 2, update titles
			document.getElementById('subStep4_1').style.display = 'none';
			document.getElementById('subStep4_2').style.display = 'block';
				document.getElementById('step4Title').textContent = 'Step 2 of 4';
			document.getElementById('step4Heading').textContent = 'Set a budget';
			currentSubStep4 = 2;
			// Expand price breakdown by default so it's visible immediately
			const defaultBreakdown = document.getElementById('priceBreakdownContent');
			if (defaultBreakdown) defaultBreakdown.style.display = 'block';
			try { window.scrollTo({ top: 0, behavior: 'instant' }); } catch (_) { window.scrollTo(0,0); }
		});

		// Budget Sub-step 2 interactions
		(function(){
			const budgetInput = document.getElementById('budgetHeroFeeInput');
			const recommendedText = document.getElementById('recommendedFeeText');
			const minFeeText = document.getElementById('minFeeText');
			const breakdownHero = document.getElementById('breakdownHeroFee');
			const breakdownBooking = document.getElementById('breakdownBookingFee');
			const totalPayText = document.getElementById('totalPayText');
			const approxHourlyText = document.getElementById('approxHourlyText');
			const whyToggle = document.getElementById('whyPriceToggle');
			const whyContent = document.getElementById('whyPriceContent');
			const breakdownToggle = document.getElementById('priceBreakdownToggle');
			const breakdownContent = document.getElementById('priceBreakdownContent');
			const insightMessage = document.getElementById('insightMessage');
			const insightBar = document.getElementById('insightBar');
			const hoursEl = document.getElementById('estimatedHoursInput');
			const infoOverlay = document.getElementById('bookingFeeInfoOverlay');
			const infoOpenBtn = document.getElementById('bookingFeeInfoBtn');
			const infoCloseX = document.getElementById('bookingFeeInfoCloseX');
			const infoGotIt = document.getElementById('bookingFeeInfoGotIt');

			const BOOKING_FEE_RATE = 0.1107;
			const MIN_FEE = 80;

			function recalc() {
				const heroFee = Math.max(MIN_FEE, parseFloat(budgetInput.value || '0'));
				const hours = Math.max(1, parseFloat(hoursEl.value || '1'));
				const recommended = parseFloat(recommendedText.textContent || '0') || heroFee;
				const booking = Math.round((heroFee * BOOKING_FEE_RATE) * 100) / 100;
				const processing = Math.round((booking * 0.65) * 100) / 100;
				const connection = Math.round((booking - processing) * 100) / 100;
				const total = Math.round((heroFee + booking) * 100) / 100;
				breakdownHero.textContent = heroFee.toFixed(2);
				breakdownBooking.textContent = booking.toFixed(2);
				totalPayText.textContent = total.toFixed(2);
				approxHourlyText.textContent = `(approx. PHP${(heroFee / hours).toFixed(2)}/hr)`;
				// Update info modal numbers too
				document.getElementById('infoProcessingFee').textContent = processing.toFixed(2);
				document.getElementById('infoConnectionFee').textContent = connection.toFixed(2);
				// insights
				if (heroFee < MIN_FEE) {
					insightMessage.textContent = 'Below minimum fee';
					insightBar.style.width = '10%';
					insightBar.style.background = '#f59e0b';
				} else if (heroFee < recommended * 0.8) {
					insightMessage.textContent = 'Below the recommended range';
					insightBar.style.width = '40%';
					insightBar.style.background = '#f59e0b';
				} else if (heroFee <= recommended * 1.2) {
					insightMessage.textContent = 'Hero fee is within the recommended range';
					insightBar.style.width = '60%';
					insightBar.style.background = '#10b981';
				} else {
					insightMessage.textContent = 'Above the recommended range';
					insightBar.style.width = '85%';
					insightBar.style.background = '#3b82f6';
				}
			}

			budgetInput?.addEventListener('input', recalc);
			whyToggle?.addEventListener('click', () => {
				whyContent.style.display = whyContent.style.display === 'none' ? 'block' : 'none';
			});
			breakdownToggle?.addEventListener('click', () => {
				const expanded = breakdownToggle.getAttribute('aria-expanded') === 'true';
				breakdownToggle.setAttribute('aria-expanded', (!expanded).toString());
				breakdownContent.style.display = expanded ? 'none' : 'block';
			});
			infoOpenBtn?.addEventListener('click', () => { infoOverlay.classList.add('active'); });
			infoCloseX?.addEventListener('click', () => { infoOverlay.classList.remove('active'); });
			infoGotIt?.addEventListener('click', () => { infoOverlay.classList.remove('active'); });
			infoOverlay?.addEventListener('click', (e) => { if (e.target === infoOverlay) infoOverlay.classList.remove('active'); });
			document.getElementById('backStep4Sub2')?.addEventListener('click', () => {
				document.getElementById('subStep4_2').style.display = 'none';
				document.getElementById('subStep4_1').style.display = 'block';
				document.getElementById('step4Title').textContent = 'Step 1 of 4';
				document.getElementById('step4Heading').textContent = 'Generate guest budget';
				currentSubStep4 = 1;
			});

			// Initial calc when entering sub-step 2 will be triggered from generator
		})();

		// Additional cost (Step 4 - Sub-step 3) interactions
		(function(){
			const addCostEl = document.getElementById('additionalCostInput');
			const totalPayTextAC = document.getElementById('totalPayText_ac');
			const approxHourlyTextAC = document.getElementById('approxHourlyText_ac');
			const breakdownHeroAC = document.getElementById('breakdownHeroFee_ac');
			const breakdownBookingAC = document.getElementById('breakdownBookingFee_ac');
			const breakdownAddCostAC = document.getElementById('breakdownAdditionalCost_ac');
			const breakdownToggleAC = document.getElementById('priceBreakdownToggle_ac');
			const breakdownContentAC = document.getElementById('priceBreakdownContent_ac');
			const infoOverlay = document.getElementById('bookingFeeInfoOverlay');
			const infoOpenBtnAC = document.getElementById('bookingFeeInfoBtn_ac');

			const BOOKING_FEE_RATE = 0.1107;
			const MIN_FEE = 80;

			function recalcAC() {
				const heroFee = Math.max(MIN_FEE, parseFloat(document.getElementById('budgetHeroFeeInput')?.value || '0'));
				const hours = Math.max(1, parseFloat(document.getElementById('estimatedHoursInput')?.value || '1'));
				const booking = Math.round((heroFee * BOOKING_FEE_RATE) * 100) / 100;
				const addCost = Math.max(0, parseFloat(addCostEl?.value || '0'));
				const total = Math.round((heroFee + booking + addCost) * 100) / 100;
				if (breakdownHeroAC) breakdownHeroAC.textContent = heroFee.toFixed(2);
				if (breakdownBookingAC) breakdownBookingAC.textContent = booking.toFixed(2);
				if (breakdownAddCostAC) breakdownAddCostAC.textContent = addCost.toFixed(2);
				if (totalPayTextAC) totalPayTextAC.textContent = total.toFixed(2);
				if (approxHourlyTextAC) approxHourlyTextAC.textContent = `(approx. PHP${(heroFee / hours).toFixed(2)}/hr)`;
			}

			breakdownToggleAC?.addEventListener('click', () => {
				const expanded = breakdownToggleAC.getAttribute('aria-expanded') === 'true';
				breakdownToggleAC.setAttribute('aria-expanded', (!expanded).toString());
				breakdownContentAC.style.display = expanded ? 'none' : 'block';
			});
			infoOpenBtnAC?.addEventListener('click', () => { infoOverlay.classList.add('active'); });
			addCostEl?.addEventListener('input', recalcAC);

			// (Review step removed)

			// Summary step actions
			document.getElementById('summaryEditBtn')?.addEventListener('click', () => {
				// Return to Step 4 - Additional cost
				goToStep(4);
				document.getElementById('subStep4_1').style.display = 'none';
				document.getElementById('subStep4_2').style.display = 'none';
				document.getElementById('subStep4_3').style.display = 'block';
				document.getElementById('step4Title').textContent = 'Step 3 of 3';
				document.getElementById('step4Heading').textContent = 'Additional cost';
				currentSubStep4 = 3;
			});

			// Intercept final submit to show Code of Conduct step
			document.getElementById('finalPostBtn')?.addEventListener('click', (e) => {
				e.preventDefault();
				goToStep(6);
			});

			// Move from Sub-step 2 to Sub-step 3
			document.getElementById('submitBudgetBtn')?.addEventListener('click', () => {
				document.getElementById('subStep4_2').style.display = 'none';
				document.getElementById('subStep4_3').style.display = 'block';
				document.getElementById('step4Title').textContent = 'Step 3 of 4';
				document.getElementById('step4Heading').textContent = 'Additional cost';
				currentSubStep4 = 3;
				recalcAC();
				// Auto-expand breakdown on entering Sub-step 3 to match Sub-step 2 behavior
				const bdAC = document.getElementById('priceBreakdownContent_ac');
				if (bdAC) bdAC.style.display = 'block';
				try { window.scrollTo({ top: 0, behavior: 'instant' }); } catch (_) { window.scrollTo(0,0); }
			});

			// Back from Sub-step 3 to 2
			document.getElementById('backStep4Sub3')?.addEventListener('click', () => {
				document.getElementById('subStep4_3').style.display = 'none';
				document.getElementById('subStep4_2').style.display = 'block';
				document.getElementById('step4Title').textContent = 'Step 2 of 4';
				document.getElementById('step4Heading').textContent = 'Set a budget';
				currentSubStep4 = 2;
			});

			// From Review -> Summary (populate just before navigating)
			function populateSummary() {
				const title = (document.getElementById('titleInput')?.value || '').trim();
				const desc = (document.getElementById('descriptionInput')?.value || '').trim();
				const req = (document.getElementById('requirementsInput')?.value || '').trim();
				const hours = Math.max(1, parseFloat(document.getElementById('estimatedHoursInput')?.value || '1'));
				const MIN_FEE = 80, BOOKING_FEE_RATE = 0.1107;
				const heroFee = Math.max(MIN_FEE, parseFloat(document.getElementById('budgetHeroFeeInput')?.value || '0'));
				const addCost = Math.max(0, parseFloat(document.getElementById('additionalCostInput')?.value || '0'));
				const booking = Math.round((heroFee * BOOKING_FEE_RATE) * 100) / 100;
				const total = Math.round((heroFee + booking + addCost) * 100) / 100;

				// Title and total
				const titleEl = document.getElementById('summaryTitleText'); if (titleEl) titleEl.textContent = title || 'Untitled quest';
				const totalEl = document.getElementById('summaryTotalText'); if (totalEl) totalEl.textContent = `PHP${total.toFixed(2)}`;

				// Location
				const onlineActive = document.getElementById('onlineBtn')?.classList.contains('active');
				const locValue = onlineActive ? 'Online' : (document.getElementById('locationInput')?.value || '');
				const locEl = document.getElementById('summaryLocationText'); if (locEl) locEl.textContent = locValue || '—';

				// Completion Date + Time
				const dateVal = document.getElementById('dateInput')?.value || '';
				const timePref = document.getElementById('timePreferenceInput')?.value || 'no-preference';
				const specificTime = document.getElementById('specificTimeInput')?.value || '';
				const rangeStart = document.getElementById('timeRangeStartInput')?.value || '';
				const rangeEnd = document.getElementById('timeRangeEndInput')?.value || '';
				function formatDate(iso){ try{ const d = new Date(iso); const day = d.toLocaleString(undefined,{weekday:'short'}); const dd = String(d.getDate()).padStart(2,'0'); const mon = d.toLocaleString(undefined,{month:'short'}); return `On ${day}, ${dd} ${mon}`; } catch(_){ return iso; } }
				let timeText = '(Anytime)';
				if (timePref === 'specific-time' && specificTime) timeText = `(${specificTime})`;
				if (timePref === 'time-range' && rangeStart && rangeEnd) timeText = `(${rangeStart} - ${rangeEnd})`;
				const compEl = document.getElementById('summaryCompletionText'); if (compEl) compEl.textContent = `${formatDate(dateVal)} ${timeText}`.trim();

				// Duration
				const durEl = document.getElementById('summaryDurationText'); if (durEl) durEl.textContent = `${hours} Hour(s)`;

				// Description
				const descEl = document.getElementById('summaryDescriptionText'); if (descEl) descEl.textContent = desc || '—';

				// Requirements (optional)
				const reqBlock = document.getElementById('summaryRequirementsBlock');
				const reqText = document.getElementById('summaryRequirementsText');
				if (req && reqBlock && reqText) { reqBlock.style.display = 'block'; reqText.textContent = req; } else if (reqBlock) { reqBlock.style.display = 'none'; }

				// Heroes required
				const heroes = document.getElementById('helperCount')?.textContent || '1';
				const heroesEl = document.getElementById('summaryHeroesText'); if (heroesEl) heroesEl.textContent = heroes;

				// Screening questions
				const qBlock = document.getElementById('summaryQuestionsBlock');
				const qList = document.getElementById('summaryQuestionsList');
				if (qList) qList.innerHTML = '';
				const inputs = Array.from(document.querySelectorAll('#questionsList input[type="text"]'));
				const questions = inputs.map(i => (i.value||'').trim()).filter(Boolean);
				if (questions.length && qBlock && qList) {
					qBlock.style.display = 'block';
					questions.forEach(q => { const li = document.createElement('li'); li.textContent = q; qList.appendChild(li); });
				} else if (qBlock) { qBlock.style.display = 'none'; }
			}

			// Sub-step 3 -> 4 (Additional cost -> Review)
			document.getElementById('nextStep4_3')?.addEventListener('click', () => {
				document.getElementById('subStep4_3').style.display = 'none';
				document.getElementById('subStep4_4').style.display = 'block';
				document.getElementById('step4Title').textContent = 'Step 4 of 4';
				document.getElementById('step4Heading').textContent = 'Review budget';
				currentSubStep4 = 4;
				// Calculate review values
				(function recalcRV(){
					const BOOKING_FEE_RATE = 0.1107;
					const MIN_FEE = 80;
					const heroFee = Math.max(MIN_FEE, parseFloat(document.getElementById('budgetHeroFeeInput')?.value || '0'));
					const addCost = Math.max(0, parseFloat(document.getElementById('additionalCostInput')?.value || '0'));
					const hours = Math.max(1, parseFloat(document.getElementById('estimatedHoursInput')?.value || '1'));
					const booking = Math.round((heroFee * BOOKING_FEE_RATE) * 100) / 100;
					const total = Math.round((heroFee + booking + addCost) * 100) / 100;
					const bh = document.getElementById('breakdownHeroFee_rv'); if (bh) bh.textContent = heroFee.toFixed(2);
					const bb = document.getElementById('breakdownBookingFee_rv'); if (bb) bb.textContent = booking.toFixed(2);
					const ba = document.getElementById('breakdownAdditionalCost_rv'); if (ba) ba.textContent = addCost.toFixed(2);
					const tp = document.getElementById('totalPayText_rv'); if (tp) tp.textContent = total.toFixed(2);
					const ah = document.getElementById('approxHourlyText_rv'); if (ah) ah.textContent = `(approx. PHP${(heroFee / hours).toFixed(2)}/hr)`;
				})();
				const bdRV = document.getElementById('priceBreakdownContent_rv');
				if (bdRV) bdRV.style.display = 'block';
				try { window.scrollTo({ top: 0, behavior: 'instant' }); } catch (_) { window.scrollTo(0,0); }
			});

			// Review actions
			const breakdownToggleRV = document.getElementById('priceBreakdownToggle_rv');
			const breakdownContentRV = document.getElementById('priceBreakdownContent_rv');
			const infoOpenBtnRV = document.getElementById('bookingFeeInfoBtn_rv');
			const backStep4Sub4 = document.getElementById('backStep4Sub4');
			const postRequestBtn = document.getElementById('postRequestBtn');

			breakdownToggleRV?.addEventListener('click', () => {
				const expanded = breakdownToggleRV.getAttribute('aria-expanded') === 'true';
				breakdownToggleRV.setAttribute('aria-expanded', (!expanded).toString());
				breakdownContentRV.style.display = expanded ? 'none' : 'block';
			});
			infoOpenBtnRV?.addEventListener('click', () => { infoOverlay.classList.add('active'); });
			backStep4Sub4?.addEventListener('click', () => {
				document.getElementById('subStep4_4').style.display = 'none';
				document.getElementById('subStep4_3').style.display = 'block';
				document.getElementById('step4Title').textContent = 'Step 3 of 4';
				document.getElementById('step4Heading').textContent = 'Additional cost';
				currentSubStep4 = 3;
			});
			postRequestBtn?.addEventListener('click', () => {
				populateSummary();
				goToStep(5);
			});
		})();

		// Code of Conduct interactions (Step 6)
		(function(){
			const coc = document.getElementById('agreeCoc');
			const cancelFee = document.getElementById('agreeCancellation');
			const agreeBtn = document.getElementById('agreeTermsBtn');
			const editLink = document.getElementById('editQuestLink');
			const form = document.getElementById('postForm');

			function updateAgreeState(){
				const ok = !!(coc?.checked && cancelFee?.checked);
				if (agreeBtn){ agreeBtn.disabled = !ok; }
			}

			coc?.addEventListener('change', updateAgreeState);
			cancelFee?.addEventListener('change', updateAgreeState);
			agreeBtn?.addEventListener('click', function(e){
				if (this.disabled) return;
				// Show posted confirmation immediately
				goToStep(7);
				// Try to submit in the background so data still saves; ignore result
				try {
					if (form) {
						const fd = new FormData(form);
						fd.append('ajax', '1');
						fetch(form.getAttribute('action') || window.location.href, { method: 'POST', body: fd, credentials: 'same-origin' }).catch(()=>{});
					}
				} catch(_) { /* ignore */ }
			});
			editLink?.addEventListener('click', function(){ goToStep(5); });
		})();

		// Auto-open Posted confirmation if the server insert succeeded
		<?php if (!empty($success)) { ?>
		(function(){
			try {
				document.getElementById('postModal').classList.add('active');
				document.body.style.overflow = 'hidden';
				goToStep(7);
			} catch(_) {}
		})();
		<?php } ?>
		
		// Time Picker functionality
		const timePickerOverlay = document.getElementById('timePickerOverlay');
		const closeTimePicker = document.getElementById('closeTimePicker');
		const timePickerDone = document.getElementById('timePickerDone');
		const hourWheel = document.getElementById('hourWheel');
		const minuteWheel = document.getElementById('minuteWheel');
		const periodWheel = document.getElementById('periodWheel');
		const specificTimeInput = document.getElementById('specificTimeInput');
		const timeRangeStartInput = document.getElementById('timeRangeStartInput');
		const timeRangeEndInput = document.getElementById('timeRangeEndInput');
		
		let selectedHour = 12;
		let selectedMinute = 0;
		let selectedPeriod = 'AM';
		let currentTimeInputType = 'specific'; // 'specific', 'range-start', or 'range-end'
		
		// Allow clicking on the input to reopen time picker
		specificTimeInput.addEventListener('click', function() {
			currentTimeInputType = 'specific';
			openTimePicker();
		});
		
		timeRangeStartInput.addEventListener('click', function() {
			currentTimeInputType = 'range-start';
			openTimePicker();
		});
		
		timeRangeEndInput.addEventListener('click', function() {
			currentTimeInputType = 'range-end';
			openTimePicker();
		});
		
		function openTimePicker() {
			// Parse existing value based on current input type
			let currentInput = null;
			if (currentTimeInputType === 'specific') {
				currentInput = specificTimeInput;
			} else if (currentTimeInputType === 'range-start') {
				currentInput = timeRangeStartInput;
			} else if (currentTimeInputType === 'range-end') {
				currentInput = timeRangeEndInput;
			}
			
			if (currentInput && currentInput.value) {
				const timeMatch = currentInput.value.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
				if (timeMatch) {
					selectedHour = parseInt(timeMatch[1]);
					selectedMinute = parseInt(timeMatch[2]);
					selectedPeriod = timeMatch[3].toUpperCase();
				}
			}
			
			timePickerOverlay.classList.add('active');
			initializeWheels();
		}
		
		function closeTimePickerModal() {
			timePickerOverlay.classList.remove('active');
		}
		
		function initializeWheels() {
			// Create hours (1-12)
			hourWheel.innerHTML = '';
			for (let i = 0; i < 4; i++) hourWheel.innerHTML += '<div class="time-wheel-item"></div>'; // Padding top
			for (let i = 1; i <= 12; i++) {
				const item = document.createElement('div');
				item.className = 'time-wheel-item';
				item.textContent = String(i).padStart(2, '0');
				item.dataset.value = i;
				hourWheel.appendChild(item);
			}
			for (let i = 0; i < 4; i++) hourWheel.innerHTML += '<div class="time-wheel-item"></div>'; // Padding bottom
			
			// Create minutes (00-59)
			minuteWheel.innerHTML = '';
			for (let i = 0; i < 4; i++) minuteWheel.innerHTML += '<div class="time-wheel-item"></div>';
			for (let i = 0; i < 60; i++) {
				const item = document.createElement('div');
				item.className = 'time-wheel-item';
				item.textContent = String(i).padStart(2, '0');
				item.dataset.value = i;
				minuteWheel.appendChild(item);
			}
			for (let i = 0; i < 4; i++) minuteWheel.innerHTML += '<div class="time-wheel-item"></div>';
			
			// Create periods (AM/PM)
			periodWheel.innerHTML = '';
			for (let i = 0; i < 4; i++) periodWheel.innerHTML += '<div class="time-wheel-item"></div>';
			['AM', 'PM'].forEach(period => {
				const item = document.createElement('div');
				item.className = 'time-wheel-item';
				item.textContent = period;
				item.dataset.value = period;
				periodWheel.appendChild(item);
			});
			for (let i = 0; i < 4; i++) periodWheel.innerHTML += '<div class="time-wheel-item"></div>';
			
			// Set initial scroll positions
			scrollToValue(hourWheel, 12);
			scrollToValue(minuteWheel, 0);
			scrollToValue(periodWheel, 'AM');
			
			// Add scroll listeners
			hourWheel.addEventListener('scroll', () => updateSelection(hourWheel));
			minuteWheel.addEventListener('scroll', () => updateSelection(minuteWheel));
			periodWheel.addEventListener('scroll', () => updateSelection(periodWheel));
		}
		
		function scrollToValue(wheel, value) {
			const items = wheel.querySelectorAll('.time-wheel-item[data-value]');
			items.forEach(item => {
				if (item.dataset.value == value) {
					const itemTop = item.offsetTop;
					const wheelHeight = wheel.clientHeight;
					const itemHeight = item.clientHeight;
					wheel.scrollTop = itemTop - (wheelHeight / 2) + (itemHeight / 2);
				}
			});
		}
		
		function updateSelection(wheel) {
			const items = wheel.querySelectorAll('.time-wheel-item[data-value]');
			const wheelRect = wheel.getBoundingClientRect();
			const centerY = wheelRect.top + wheelRect.height / 2;
			
			let closestItem = null;
			let closestDistance = Infinity;
			
			items.forEach(item => {
				item.classList.remove('selected');
				const itemRect = item.getBoundingClientRect();
				const itemCenterY = itemRect.top + itemRect.height / 2;
				const distance = Math.abs(centerY - itemCenterY);
				
				if (distance < closestDistance) {
					closestDistance = distance;
					closestItem = item;
				}
			});
			
			if (closestItem) {
				closestItem.classList.add('selected');
				
				// Update selected values
				if (wheel === hourWheel) {
					selectedHour = parseInt(closestItem.dataset.value);
				} else if (wheel === minuteWheel) {
					selectedMinute = parseInt(closestItem.dataset.value);
				} else if (wheel === periodWheel) {
					selectedPeriod = closestItem.dataset.value;
				}
			}
		}
		
		closeTimePicker.addEventListener('click', closeTimePickerModal);
		timePickerOverlay.addEventListener('click', function(e) {
			if (e.target === timePickerOverlay) {
				closeTimePickerModal();
			}
		});
		
		timePickerDone.addEventListener('click', function() {
			// Display in 12-hour format with AM/PM
			const displayTime = `${String(selectedHour).padStart(2, '0')}:${String(selectedMinute).padStart(2, '0')} ${selectedPeriod}`;
			
			// Set value to correct input based on currentTimeInputType
			if (currentTimeInputType === 'specific') {
				specificTimeInput.value = displayTime;
			} else if (currentTimeInputType === 'range-start') {
				timeRangeStartInput.value = displayTime;
			} else if (currentTimeInputType === 'range-end') {
				timeRangeEndInput.value = displayTime;
			}
			
			closeTimePickerModal();
		});
		
		// Back buttons - update backStep3 handler
		/*document.getElementById('backStep3').addEventListener('click', function() {
			goToStep(2);
			showSubStep(2, 4); // Go back to last sub-step of Step 2
		});*/
		
		const backStep4Btn = document.getElementById('backStep4');
		backStep4Btn?.addEventListener('click', function() {
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
