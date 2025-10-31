<?php
// Start output buffering (prevents "headers already sent" warnings)
ob_start();

// Start session safely before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Capture mobile from POST if present and keep in session for future requests
if (!empty($_POST['mobile'])) {
    $_SESSION['mobile'] = trim($_POST['mobile']);
}

// Determine display name
$display = $_SESSION['display_name'] ?? $_SESSION['mobile'] ?? 'there';

// Create avatar initial
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));

// Safe DB connect to fetch recent posts
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
	if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_OFF);
	try {
		$conn = @mysqli_connect($h,$u,$p,$n);
		if ($conn && !mysqli_connect_errno()) { $mysqli = $conn; $dbAvailable = true; break; }
		else { $lastConnError = mysqli_connect_error() ?: 'Connection failed'; if ($conn) { @mysqli_close($conn); } }
	} catch (Throwable $ex) {
		$lastConnError = $ex->getMessage();
	} finally {
		if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);
	}
}
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function time_ago($dt){
	$t = is_numeric($dt) ? (int)$dt : strtotime((string)$dt);
	if (!$t) return '';
	$d = time() - $t;
	if ($d < 60) return $d.'s ago';
	if ($d < 3600) return floor($d/60).'m ago';
	if ($d < 86400) return floor($d/3600).'h ago';
	if ($d < 604800) return floor($d/86400).'d ago';
	return date('M j, Y', $t);
}
$jobs = [];
if ($dbAvailable) {
	$sql = "SELECT id, title, category, COALESCE(location,'') AS location, COALESCE(budget,'') AS budget, COALESCE(date_needed,'') AS date_needed, COALESCE(status,'open') AS status, posted_at
	        FROM jobs
	        WHERE COALESCE(status,'open') IN ('open','pending')
	        ORDER BY posted_at DESC, id DESC
	        LIMIT 10";
	if ($res = @mysqli_query($mysqli, $sql)) {
		while ($row = mysqli_fetch_assoc($res)) $jobs[] = $row;
		@mysqli_free_result($res);
	}
}

// End buffering (send output)
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Home • Services • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
	<style>
		/* Side nav: compact by default, expand on hover */
		.dash-aside {
			width: 64px;                    /* compact */
			transition: width 200ms ease, box-shadow 180ms ease;
			overflow: hidden;               /* hide labels when compact */
		}
		.dash-aside:hover {
			width: 240px;                   /* expand */
			box-shadow: 0 12px 28px rgba(2,6,23,.12);
		}
		/* Keep nav items on one line and hide overflow when compact */
		.dash-aside .dash-nav a {
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.dash-aside .dash-nav .dash-icon {
			width: 20px; height: 20px; flex: 0 0 20px;
		}

		/* Bottom nav: centered at the bottom */
		.dash-bottom-nav {
			position: fixed;
			left: 50%;
			right: auto;
			bottom: 16px;
			transform: translateX(-50%) scale(0.92); /* keep existing scale */
			transform-origin: bottom center;
			margin: 0;
			width: max-content; /* shrink to content so centering is precise */
			transition: transform 180ms ease, box-shadow 180ms ease;
			border: 3px solid #0078a6;
			background: transparent;
			z-index: 999; /* ensure above content */
		}
		.dash-bottom-nav:hover {
			transform: translateX(-50%) scale(1);
			box-shadow: 0 12px 28px rgba(2,6,23,.12);
		}

		@media (max-width:520px) {
			.dash-bottom-nav {
				bottom: 12px;
				transform: translateX(-50%); /* no scale on very small screens */
			}
		}

		/* Center the main content area */
		.dash-content { max-width: 1100px; margin: 0 auto; padding: 0 16px; position: relative; z-index: 1; }

		/* Center hero text */
		.home-hero { text-align: center; }

		/* Center category titles and grids */
		.home-sections .dash-cat { text-align: center; }
		.dash-cat-title { display: flex; justify-content: center; }
		.dash-svc-grid { justify-content: center; }

		/* Make service cards blue */
		.dash-svc-card {
			background: #0078a6 !important;
			color: #fff;
			border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
			box-shadow: 0 8px 24px rgba(0,120,166,.24);
			backdrop-filter: none;
		}
		.dash-svc-card:hover {
			transform: translateY(-2px);
			box-shadow: 0 12px 32px rgba(0,120,166,.32);
		}
		.dash-svc-card .info .title {
			color: #fff;
		}
		.dash-svc-card .info .sub {
			color: rgba(255,255,255,.85);
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

		/* Remove Post button from bottom nav, add floating circular + button */
		.floating-post-btn {
			position: fixed;
			right: 20px;
			bottom: 24px;
			width: 56px;
			height: 56px;
			border-radius: 50%;
			background: #fff;
			color: #0078a6;
			border: 3px solid #0078a6;
			box-shadow: 0 6px 20px rgba(0,120,166,.3);
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 2rem;
			font-weight: 300;
			line-height: 1;
			cursor: pointer;
			transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease;
			z-index: 998;
			text-decoration: none;
		}
		.floating-post-btn:hover {
			transform: translateY(-4px) scale(1.05);
			box-shadow: 0 10px 28px rgba(0,120,166,.4);
			background: #f0f9ff;
		}
		.floating-post-btn:active {
			transform: translateY(-2px) scale(1.02);
		}
		/* Tooltip above the + button: plain blue text, no box */
		.floating-post-btn::after {
			content: 'Post';
			position: absolute;
			bottom: calc(100% + 8px);
			left: 50%;
			transform: translateX(-50%) translateY(4px);
			background: transparent;
			color: #0078a6;
			padding: 0;
			border-radius: 0;
			box-shadow: none;
			font-weight: 800;
			font-size: 0.75rem;
			white-space: nowrap;
			opacity: 0;
			pointer-events: none;
			transition: opacity .16s ease, transform .16s ease;
			z-index: 1001;
		}
		.floating-post-btn::before {
			content: none;
			position: absolute;
			bottom: calc(100% + 2px);
			left: 50%;
			transform: translateX(-50%);
			border-width: 6px;
			border-style: solid;
			border-color: #0f172a transparent transparent transparent;
			opacity: 0;
			transition: opacity .16s ease;
			z-index: 1001;
		}
		.floating-post-btn:hover::after,
		.floating-post-btn:focus-visible::after {
			opacity: 1;
			transform: translateX(-50%) translateY(0);
		}
		.floating-post-btn:hover::before,
		.floating-post-btn:focus-visible::before {
			opacity: 1;
		}

		@media (max-width:520px) {
			.floating-post-btn {
				right: 16px;
				bottom: 20px;
			}
		}

		/* Ensure main content is above background */
		.dash-shell {
			position: relative;
			z-index: 1;
		}

		/* Floating Post modal (iframe) */
		.post-modal {
			position: fixed; inset: 0;
			background: rgba(15,23,42,.55);
			backdrop-filter: blur(2px);
			display: none;
			align-items: center;
			justify-content: center;
			z-index: 2000;
		}
		.post-modal.show { display: flex; }
		.post-modal .modal-card {
			width: min(820px, 96vw);
			height: min(90vh, 720px);
			border-radius: 16px;
			background: #ffffff;
			position: relative;
			box-shadow: 0 18px 48px rgba(2,6,23,.28);
			border: 3px solid #0078a6;
			overflow: hidden;
		}
		.post-modal .modal-close {
			position: absolute; top: 8px; right: 8px;
			width: 36px; height: 36px; border-radius: 999px;
			border: 2px solid #0078a6; background: #fff; color: #0078a6;
			font-weight: 900; line-height: 1;
			display: grid; place-items: center; cursor: pointer;
			box-shadow: 0 8px 22px rgba(2,6,23,.18);
		}
		.post-modal .modal-card iframe {
			position: absolute; inset: 0;
			width: 100%; height: 100%;
			border: 0;
			background: transparent;
		}

		/* Recent Posts feed */
		.jobs-feed { margin-top: 18px; }
		.jobs-feed .feed-title { display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:10px; font-weight:800; }
		.jobs-feed .feed-grid { display:grid; gap:12px; grid-template-columns: 1fr 1fr; }
		@media (max-width: 920px){ .jobs-feed .feed-grid { grid-template-columns: 1fr; } }
		.feed-card {
			background: #0078a6 !important; color:#fff;
			border-radius: 14px; padding: 14px 16px;
			box-shadow: 0 8px 20px rgba(0,120,166,.24);
			border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
		}
		.feed-card .fc-top { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:6px; }
		.feed-card .fc-title { font-weight:800; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
		.feed-card .fc-time { color: rgba(255,255,255,.85); font-size:.85rem; white-space:nowrap; }
		.feed-card .fc-cat { color:#fff; font-size:.9rem; opacity:.95; margin-bottom:6px; }
		.feed-card .fc-meta { display:flex; flex-wrap:wrap; gap:10px 14px; color: rgba(255,255,255,.9); font-size:.9rem; }
		.feed-card .fc-meta .item { display:inline-flex; align-items:center; gap:6px; }
		.feed-empty, .feed-note { text-align:center; color: rgba(15,23,42,.7); }
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" />
		</div>
	</div>

	<div class="dash-overlay"></div>
	<div class="dash-shell">
		<main class="dash-content">
			<!-- Hero banner -->
			<section class="home-hero">
				<p class="hero-tagline">Where skilled hands meet local demand.</p>
			</section>

			<!-- Recent Posts feed (before services) -->
			<section class="jobs-feed" aria-label="Recent posts">
				<div class="feed-title">
					<span>Recent Posts</span>
				</div>

				<?php if (!$dbAvailable): ?>
					<p class="feed-note">Posts are unavailable right now. <?php echo e($lastConnError); ?></p>
				<?php elseif (empty($jobs)): ?>
					<p class="feed-empty">No recent posts yet. Be the first to post using the + button.</p>
				<?php else: ?>
					<div class="feed-grid">
						<?php foreach ($jobs as $j): ?>
							<article class="feed-card">
								<div class="fc-top">
									<div class="fc-title" title="<?php echo e($j['title']); ?>"><?php echo e($j['title']); ?></div>
									<div class="fc-time"><?php echo e(time_ago($j['posted_at'])); ?></div>
								</div>
								<div class="fc-cat"><?php echo e($j['category']); ?></div>
								<div class="fc-meta">
									<?php if (!empty($j['location'])): ?>
										<span class="item" title="<?php echo e($j['location']); ?>">📍 <?php echo e($j['location']); ?></span>
									<?php endif; ?>
									<?php if (!empty($j['budget'])): ?>
										<span class="item">💰 <?php echo e($j['budget']); ?></span>
									<?php endif; ?>
									<?php if (!empty($j['date_needed'])): ?>
										<span class="item">📅 <?php echo e($j['date_needed']); ?></span>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>

			<!-- Category sections -->
			<div class="home-sections" id="available-services">
				<!-- Home Service -->
				<div class="dash-cat">
					<div class="dash-cat-title"><span>Home Service</span></div>
					<div class="dash-svc-grid">
						<a class="dash-svc-card glass-card" href="./services/cleaning.php">
							<div class="info"><div class="title">Cleaning</div><div class="sub">Home and office cleaning</div></div>
							<div class="pic svc-cleaning"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/aircon.php">
							<div class="info"><div class="title">Aircon</div><div class="sub">Cleaning & maintenance</div></div>
							<div class="pic svc-aircon"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/upholstery.php">
							<div class="info"><div class="title">Upholstery</div><div class="sub">Deep clean sofas & more</div></div>
							<div class="pic svc-upholstery"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/electrical-appliance.php">
							<div class="info"><div class="title">Electrical & Appliance</div><div class="sub">Wiring & appliance fix</div></div>
							<div class="pic svc-electrical-appliance"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/plumbing-handyman.php">
							<div class="info"><div class="title">Plumbing & Handyman</div><div class="sub">Repairs & installations</div></div>
							<div class="pic svc-plumbing-handyman"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/pest-control.php">
							<div class="info"><div class="title">Pest Control</div><div class="sub">Termites, roaches, more</div></div>
							<div class="pic svc-pest-control"></div>
						</a>
						<a class="dash-svc-card glass-card" href="./services/ironing.php">
							<div class="info"><div class="title">Ironing</div><div class="sub">Clothes ironing service</div></div>
							<div class="pic svc-ironing"></div>
						</a>
					</div>
				</div>

				<!-- Personal Care -->
				<div class="dash-cat">
					<div class="dash-cat-title"><span>Personal Care</span></div>
					<div class="dash-svc-grid">
						<a class="dash-svc-card glass-card" href="./services/beauty.php"><div class="info"><div class="title">Beauty</div><div class="sub">Skin & nails</div></div><div class="pic svc-beauty"></div></a>
						<a class="dash-svc-card glass-card" href="./services/massage.php"><div class="info"><div class="title">Massage</div><div class="sub">Relaxation & therapy</div></div><div class="pic svc-massage"></div></a>
						<a class="dash-svc-card glass-card" href="./services/spa.php"><div class="info"><div class="title">Spa</div><div class="sub">Pampering & wellness</div></div><div class="pic svc-spa"></div></a>
						<a class="dash-svc-card glass-card" href="./services/medispa.php"><div class="info"><div class="title">Medi-Spa</div><div class="sub">Advanced skin treatments</div></div><div class="pic svc-medispa"></div></a>
					</div>
				</div>

				<!-- Events -->
				<div class="dash-cat">
					<div class="dash-cat-title"><span>Events</span></div>
					<div class="dash-svc-grid">
						<a class="dash-svc-card glass-card" href="./services/birthday.php"><div class="info"><div class="title">Birthday</div><div class="sub">Party planning & more</div></div><div class="pic svc-birthday"></div></a>
						<a class="dash-svc-card glass-card" href="./services/wedding.php"><div class="info"><div class="title">Wedding</div><div class="sub">Ceremony & reception</div></div><div class="pic svc-wedding"></div></a>
						<a class="dash-svc-card glass-card" href="./services/corporate.php"><div class="info"><div class="title">Corporate</div><div class="sub">Events & functions</div></div><div class="pic svc-corporate"></div></a>
						<a class="dash-svc-card glass-card" href="./services/anniversary.php"><div class="info"><div class="title">Anniversary</div><div class="sub">Celebration planning</div></div><div class="pic svc-anniversary"></div></a>
					</div>
				</div>
			</div>
		</main>

		<aside class="dash-aside">
			<nav class="dash-nav">
				<a href="./clients-post.php" aria-label="Post">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M12 5v14m-7-7h14"/>
					</svg>
					<span>Post</span>
				</a>
				<a href="./home-services.php" class="active" aria-label="Home">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
					<span>Home</span>
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
		</aside>
	</div>

	<!-- Floating circular + button -->
	<a href="./clients-post.php" class="floating-post-btn" aria-label="Post" title="Post">+</a>

	<!-- Post modal -->
	<div class="post-modal" id="postModal" role="dialog" aria-modal="true" aria-labelledby="postModalTitle">
		<div class="modal-card">
			<button type="button" class="modal-close" aria-label="Close">×</button>
			<iframe id="postFrame" src="" title="Post a Job" loading="lazy"></iframe>
		</div>
	</div>

	<!-- Floating bottom navigation (Post button removed) -->
	<nav class="dash-bottom-nav">
		<a href="./home-services.php" class="active" aria-label="Home">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
			<span>Home</span>
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
	// Open Post modal (loads post.php in an iframe) and close handlers
	(function(){
		const modal = document.getElementById('postModal');
		const closeBtn = modal.querySelector('.modal-close');
		const frame = document.getElementById('postFrame');

		function openModal(url) {
			frame.src = url || './post.php';
			modal.classList.add('show');
		}
		function closeModal() {
			modal.classList.remove('show');
			frame.src = 'about:blank';
		}

		// Expose close for same-origin iframe direct call
		window.__servisyohubClosePostModal = closeModal;

		document.addEventListener('click', function(e){
			const a = e.target.closest('a[href$="post.php"]');
			if (!a) return;
			e.preventDefault();
			openModal(a.getAttribute('href'));
		});

		closeBtn.addEventListener('click', closeModal);
		modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
		document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('show')) closeModal(); });

		// Accept both string and object postMessage payloads from iframe
		window.addEventListener('message', function(e){
			try {
				const d = e.data;
				if (d === 'close-post-modal' || (d && typeof d === 'object' && (d.action === 'close-post-modal' || d.type === 'close-post-modal'))) {
					closeModal();
				}
			} catch (_) {}
		});
	})();
	</script>
</body>
</html>
