<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Home • Jobs • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
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
		<div class="dash-brand">
			<img class="dash-brand-logo" src="../assets/images/job_logo.png" alt="Servisyo Hub" />
			<span>Servisyo Hub</span>
		</div>
		<div class="dash-top-spacer"></div>
	</div>

	<div class="dash-overlay"></div>

	<div class="dash-shell">
		<main class="dash-content">
			<h1 class="dash-greet">Hi <?php echo htmlspecialchars($display); ?>!</h1>
			<p class="dash-muted">Welcome to your job application dashboard.</p>

			<p class="dash-muted">Where skilled hands meet local demand.</p>

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

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./home-jobs.php" class="active" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
		</a>
		<a href="./my-jobs.php" aria-label="My Jobs">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Jobs</span>
		</a>
		<a href="./profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>
</body>
</html>

