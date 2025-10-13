<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Home • Jobs • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body>
	<?php
		session_start();
		// Capture first_name from POST if present and keep in session for future requests
		if (!empty($_POST['first_name'])) {
			$_SESSION['display_name'] = trim($_POST['first_name']);
		}
		$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : 'there';
		$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
	?>
	<div class="dash-topbar">
		<div class="dash-brand">Servisyo Hub</div>
		<div class="dash-top-spacer"></div>
		<div class="dash-avatar" title="<?php echo htmlspecialchars($display); ?>"><?php echo htmlspecialchars($avatar); ?></div>
		<button class="dash-icon-btn" title="Settings" aria-label="Settings">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.33 1.82l.02.06a2 2 0 1 1-3.38 0l.02-.06A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82-.33l-.06.02a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15c0-.32-.1-.63-.27-.9a1.65 1.65 0 0 0-1.55-.78H2.7a2 2 0 1 1 0-4h.08c.64 0 1.22-.3 1.55-.78.17-.27.27-.58.27-.9s-.1-.63-.27-.9A1.65 1.65 0 0 0 3 4.6l-.06-.06A2 2 0 0 1 5.77 1.7l.06.06c.46.46 1.1.66 1.73.52.31-.06.6-.19.85-.38.25-.19.45-.44.6-.72l.02-.06a2 2 0 1 1 3.38 0l.02.06c.15.28.35.53.6.72.25.19.54.32.85.38.63.14 1.27-.06 1.73-.52l.06-.06A2 2 0 1 1 22.3 4.6l-.06.06c-.46.46-.66 1.1-.52 1.73.06.31.19.6.38.85.19.25.44.45.72.6l.06.02a2 2 0 1 1 0 3.38l-.06.02c-.28.15-.53.35-.72.6-.19.25-.32.54-.38.85-.14.63.06 1.27.52 1.73l.06.06Z"/></svg>
		</button>
	</div>

	<div class="dash-shell">
		<main class="dash-content">
			<h1 class="dash-greet">Hi <?php echo htmlspecialchars($display); ?>!</h1>
			<p class="dash-muted">Welcome to your job application dashboard.</p>

			<section class="dash-cards">
				<div class="dash-card green">
					<div>
						<div class="dash-pill">Ready to work?</div>
						<h3>Browse Job Posts</h3>
						<p>See opportunities that match your profession.</p>
					</div>
					<a href="./my-jobs.php" class="dash-pill">Explore</a>
				</div>
				<div class="dash-card blue">
					<div>
						<div class="dash-pill">Complete your details</div>
						<h3>Update Your Profile</h3>
						<p>Stand out with a complete profile.</p>
					</div>
					<a href="./profile.php" class="dash-pill">Update</a>
				</div>
			</section>

			<section class="dash-explore">
				<h4>Explore</h4>
				<div class="dash-list-item">Application Tips: How to get approved faster</div>
			</section>
		</main>

		<aside class="dash-aside">
			<nav class="dash-nav">
				<h3>Navigation</h3>
				<a href="./home-jobs.php" class="active">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
					Home
				</a>
				<a href="./my-jobs.php">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
					My Jobs <span class="dash-badge">0</span>
				</a>
				<a href="./profile.php">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
					Profile
				</a>
			</nav>
		</aside>
	</div>
</body>
</html>

