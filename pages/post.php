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

/* Right-side full-height sidebar nav (from profile.php) */
.dash-float-nav {
	position: fixed; top: 0; right: 0; bottom: 0;
	z-index: 1000;
	display: flex !important; flex-direction: column; justify-content: flex-start;
	gap: 8px;
	padding: 12px 8px 8px 8px;
	border: 2px solid color-mix(in srgb, #0078a6 75%, #0000);
	border-right: 0;
	background: rgba(255,255,255,.95);
	backdrop-filter: saturate(1.15) blur(12px);
	border-top-left-radius: 16px; border-bottom-left-radius: 16px;
	border-top-right-radius: 0; border-bottom-right-radius: 0;
	box-shadow: 0 8px 24px rgba(0,120,166,.28), 0 0 0 1px rgba(255,255,255,.4) inset;
	transition: width .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s ease;
	width: 56px; overflow: hidden;
}
.dash-float-nav:hover { width: 200px; box-shadow: 0 12px 32px rgba(0,120,166,.35), 0 0 0 1px rgba(255,255,255,.5) inset; }

/* Brand at top: job_logo by default, bluefont on hover */
.dash-float-nav .nav-brand { display: grid; place-items: center; position: relative; height: 56px; padding: 6px 0; }
.dash-float-nav .nav-brand a { display:block; width:100%; height:100%; position:relative; text-decoration:none; }
.dash-float-nav .nav-brand img {
	position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
	display:block; object-fit:contain; pointer-events:none;
	transition: opacity .25s ease, transform .25s ease, width .3s ease;
}
.dash-float-nav .nav-brand .logo-small { width:26px; height:auto; opacity:1; }
.dash-float-nav .nav-brand .logo-wide { width:160px; height:auto; opacity:0; }
.dash-float-nav:hover .nav-brand .logo-small { opacity:0; transform:translate(-50%,-50%) scale(.96); }
.dash-float-nav:hover .nav-brand .logo-wide { opacity:1; transform:translate(-50%,-50%) scale(1); }

/* Groups */
.dash-float-nav > .nav-main { display:grid; gap:8px; align-content:start; }
.dash-float-nav > .nav-settings { margin-top:auto; display:grid; gap:8px; }

/* Links and icons */
.dash-float-nav a {
	position: relative;
	width: 40px; height: 40px;
	display: grid; grid-template-columns: 40px 1fr; place-items: center; align-items: center;
	border-radius: 12px; color: #0f172a; text-decoration: none; outline: none; white-space: nowrap;
	transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4,0,0.2,1);
}
.dash-float-nav:hover a { width: 184px; }
.dash-float-nav a:hover:not(.active) { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); transform: scale(1.05); }
.dash-float-nav a:focus-visible { box-shadow: 0 0 0 3px rgba(0,120,166,.3); }
.dash-float-nav a.active { background: linear-gradient(135deg, #0078a6 0%, #006a94 100%); color:#fff; box-shadow: 0 6px 18px rgba(0,120,166,.4); }
.dash-float-nav a.active::after {
	content: ""; position: absolute; left: -5px; width: 3px; height: 18px;
	background: linear-gradient(180deg, #0078a6 0%, #00a8e8 100%); border-radius: 2px;
	box-shadow: 0 0 0 2px rgba(255,255,255,.9), 0 0 12px rgba(0,120,166,.6);
}
.dash-float-nav .dash-icon { width:18px; height:18px; justify-self:center; object-fit:contain; transition: transform .2s ease; }
.dash-float-nav a:hover .dash-icon { transform: scale(1.1); }
.dash-float-nav a .dash-text {
	opacity:0; transform:translateX(-10px);
	transition: opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s;
	font-weight:800; font-size:.85rem; color:inherit; justify-self:start; padding-left:8px;
}
.dash-float-nav:hover a .dash-text { opacity:1; transform:translateX(0); }

/* Remove top bar on this page */
.top-bar { display: none !important; }

/* Hide bottom nav on this page */
.dash-bottom-nav { display: none !important; }
</style>
</head>
<body>
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<!-- Top Bar (removed) -->
	<!--
	<div class="top-bar">
		<div class="top-bar-content">
			<a href="./home-gawain.php" class="top-bar-logo">
				<img src="../assets/images/bluefont.png" alt="Servisyo Hub" />
			</a>
		</div>
	</div>
	-->

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

	<!-- Trending Gawain -->
	<section class="trending-services" aria-label="Trending Gawain">
		<h3 class="trending-title">Trending Gawain</h3>
		
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

	<!-- Floating bottom navigation (removed) -->
	<!--
	<nav class="dash-bottom-nav">
		<a href="./home-gawain.php" aria-label="Browse">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
			<span>Browse</span>
		</a>
		<a href="./post.php" class="active" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span>Post</span>
		</a>
		<a href="./my-gawain.php" aria-label="My Gawain">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Gawain</span>
		</a>
		<a href="./chats.php" aria-label="Chats">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			<span>Chats</span>
		</a>
		<a href="./profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
	-->

	<!-- Right-side full-height sidebar navigation (copied from profile.php) -->
	<nav class="dash-float-nav" id="dashNav">
		<div class="nav-brand">
			<a href="./home-gawain.php" title="">
				<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub">
				<img class="logo-wide" src="../assets/images/bluefont.png" alt="ServisyoHub">
			</a>
		</div>

		<div class="nav-main">
			<a href="./profile.php" aria-label="Profile">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
				<span class="dash-text">Profile</span>
			</a>
			<a href="./post.php" class="active" aria-current="page" aria-label="Post">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/>
				</svg>
				<span class="dash-text">Post</span>
			</a>
			<a href="./my-gawain.php" aria-label="My Gawain">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
				<span class="dash-text">My Gawain</span>
			</a>
			<a href="./chats.php" aria-label="Chats">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
				<span class="dash-text">Chats</span>
			</a>
		</div>

		<div class="nav-settings">