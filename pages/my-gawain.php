<?php
session_start();
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'there');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>My Gawain • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-topbar center">
		<div class="dash-brand"><img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" /></div>
	</div>
	<div class="dash-shell">
		<main class="dash-content">
			<header class="mq-header">
				<h1 class="mq-title">My Gawain</h1>
				<nav class="mq-tabs" role="tablist" aria-label="Gawain tabs">
					<?php $tab = isset($_GET['tab']) ? $_GET['tab'] : 'offered'; ?>
					<a class="mq-tab <?php echo $tab==='offered'?'active':''; ?>" href="?tab=offered" role="tab" aria-selected="<?php echo $tab==='offered'?'true':'false'; ?>">Offered</a>
					<a class="mq-tab <?php echo $tab==='posted'?'active':''; ?>" href="?tab=posted" role="tab" aria-selected="<?php echo $tab==='posted'?'true':'false'; ?>">Posted</a>
				</nav>
				<div class="mq-filter-row">
					<button class="mq-filter" id="mqFilterBtn" type="button" aria-haspopup="dialog" aria-controls="mqFilterModal" aria-expanded="false">Filter: <strong id="mqFilterLabel">All</strong></button>
				</div>
			</header>

			<section class="mq-empty" aria-label="Empty state">
				<p class="empty-text">Uh oh! You don't have any activity yet. Head over to the homepage to make offers to gawain that interest you.</p>
				<a class="btn mq-browse" href="./home-gawain.php">Browse gawain</a>
			</section>

			<!-- Filter Bottom Sheet Modal -->
			<div class="mq-filter-modal" id="mqFilterModal" role="dialog" aria-modal="true" aria-labelledby="mqFilterTitle" aria-hidden="true">
				<div class="mq-filter-backdrop" data-filter-close></div>
				<div class="mq-filter-sheet" role="document">
					<div class="mq-filter-header">
						<button class="mq-filter-reset" id="mqFilterReset" type="button">Reset</button>
						<h3 class="mq-filter-title" id="mqFilterTitle">Apply filters</h3>
						<button class="mq-filter-close" type="button" aria-label="Close" data-filter-close>&times;</button>
					</div>
					<form class="mq-filter-form" id="mqFilterForm">
						<label class="mq-filter-item"><input type="checkbox" name="status" value="pending"> <span>Pending offers</span></label>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="inprogress"> <span>In-progress</span></label>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="completed"> <span>Completed</span></label>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="cancelled"> <span>Cancellations</span></label>

						<button class="mq-filter-apply" id="mqFilterApply" type="button">Apply</button>
					</form>
				</div>
			</div>
		</main>
	</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span>Post</span>
		</a>
		<a href="./home-gawain.php" aria-label="Browse">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
			</svg>
			<span>Browse</span>
		</a>
		<a href="./my-gawain.php" class="active" aria-label="My Gawain">
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
</body>
</html>
