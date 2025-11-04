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
<<<<<<< HEAD
=======
	<style>
		/* Match bottom nav behavior from home-gawain.php and center at bottom */
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

		/* Ensure main content is above background */
		.dash-shell {
			position: relative;
			z-index: 1;
		}

		/* Right-side floating nav (icon-only, show label tooltip on hover) */
		.dash-float-nav {
			position: fixed;
			top: 84px;              /* sits below the topbar */
			right: 16px;
			z-index: 1000;
			display: grid;
			gap: 10px;
			padding: 10px;
			border: 3px solid #0078a6;
			background: #fff;
			border-radius: 16px;
			box-shadow: 0 8px 20px rgba(0,120,166,.24);
		}
		.dash-float-nav a {
			position: relative;
			width: 44px;
			height: 44px;
			display: grid;
			place-items: center;
			border-radius: 12px;
			color: #0f172a;
			text-decoration: none;
			transition: background .15s ease, color .15s ease;
		}
		.dash-float-nav a.active { background: #0078a6; color: #fff; }
		.dash-float-nav a:hover:not(.active) { background: #f0f9ff; }
		.dash-float-nav .dash-icon { width: 20px; height: 20px; }

		/* Tooltip label that pops out on hover */
		.dash-float-nav a .dash-label {
			position: absolute;
			right: 100%;
			top: 50%;
			transform: translateY(-50%) scale(.95);
			transform-origin: right center;
			margin-right: 8px;
			background: #0f172a;
			color: #fff;
			font-weight: 800;
			font-size: .85rem;
			padding: 6px 10px;
			border-radius: 10px;
			white-space: nowrap;
			opacity: 0;
			pointer-events: none;
			box-shadow: 0 8px 20px rgba(2,6,23,.2);
			transition: opacity .15s ease, transform .15s ease;
		}
		.dash-float-nav a:hover .dash-label { opacity: 1; transform: translateY(-50%) scale(1); }
	</style>
>>>>>>> 1501966ac8735e5c32a1fc11945ef6cd1f34443d
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
					<a class="mq-filter" href="./filter.php" aria-label="Filter">Filter: <strong>All</strong></a>
				</div>
			</header>

			<section class="mq-empty" aria-label="Empty state">
				<div class="mq-illustration"><img src="../assets/images/job_logo.png" alt="" /></div>
				<p class="empty-text">Uh oh! You don't have any activity yet. Head over to the homepage to make offers to gawain that interest you.</p>
				<a class="btn mq-browse" href="./home-gawain.php">Browse gawain</a>
			</section>
		</main>
	</div>

<<<<<<< HEAD
	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./clients-post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span>Post</span>
		</a>
=======
	<!-- Floating right-side navigation (replaces bottom nav) -->
	<nav class="dash-float-nav">
>>>>>>> 1501966ac8735e5c32a1fc11945ef6cd1f34443d
		<a href="./home-gawain.php" aria-label="Browse">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
			</svg>
			<span class="dash-label">Browse</span>
		</a>
<<<<<<< HEAD
=======
		<a href="./post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/>
			</svg>
			<span class="dash-label">Post</span>
		</a>
>>>>>>> 1501966ac8735e5c32a1fc11945ef6cd1f34443d
		<a href="./my-gawain.php" class="active" aria-label="My Gawain">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M4 7h16M4 12h10M4 17h7"/>
			</svg>
			<span class="dash-label">My Gawain</span>
		</a>
		<a href="./chats.php" aria-label="Chats">
<<<<<<< HEAD
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M21 15a4 4 0 0 1-4 4H8l-4 4v-4H5a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4z"/>
			</svg>
			<span>Chats</span>
		</a>
		<a href="./clients-profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
=======
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
			</svg>
			<span class="dash-label">Chats</span>
		</a>
		<a href="./profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
			</svg>
			<span class="dash-label">Profile</span>
>>>>>>> 1501966ac8735e5c32a1fc11945ef6cd1f34443d
		</a>
	</nav>
</body>
</html>
