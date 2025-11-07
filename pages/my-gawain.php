<?php
session_start();
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'there');

// --- Fetch "Offered" data: only offers received on MY posts ---
require_once __DIR__ . '/../config/db_connect.php';
$db = $conn ?? $mysqli ?? null;
$viewerId = (int)($_SESSION['user_id'] ?? 0);
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'offered';

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function peso($v){ return '₱' . number_format((float)($v ?? 0), 2); }

// Helper to build offerer display name
function offerer_name(array $r): string {
	$n = trim($r['offerer_username'] ?? '');
	if ($n === '') {
		$f = trim($r['offerer_first'] ?? '');
		$l = trim($r['offerer_last'] ?? '');
		$n = trim($f . ' ' . $l);
	}
	return $n !== '' ? $n : 'User';
}

$offered_list = [];
if ($tab === 'offered' && $viewerId && $db) {
	// Only offers received on jobs owned by the current user
	$sql = "SELECT 
	            o.id            AS offer_id,
	            o.job_id        AS job_id,
	            o.user_id       AS offerer_id,
	            o.amount        AS amount,
	            o.status        AS status,
	            o.created_at    AS created_at,
	            j.title         AS job_title,
	            COALESCE(j.location,'Online')     AS location,
	            COALESCE(j.date_needed,'Anytime') AS date_needed,
	            COALESCE(u.username,'')   AS offerer_username,
	            COALESCE(u.first_name,'') AS offerer_first,
	            COALESCE(u.last_name,'')  AS offerer_last,
	            COALESCE(u.avatar,'')     AS offerer_avatar
	        FROM offers o
	        JOIN jobs j  ON j.id = o.job_id
	        LEFT JOIN users u ON u.id = o.user_id
	        WHERE j.user_id = ?
	        ORDER BY o.created_at DESC, o.id DESC";
	if ($st = @mysqli_prepare($db, $sql)) {
		mysqli_stmt_bind_param($st, 'i', $viewerId);
		if (@mysqli_stmt_execute($st)) {
			$res = @mysqli_stmt_get_result($st);
			while ($row = @mysqli_fetch_assoc($res)) $offered_list[] = $row;
		}
		@mysqli_stmt_close($st);
	}
}

$posted_list = [];
if ($tab === 'posted' && $viewerId && $db) {
	$psql = "SELECT id, title, COALESCE(location,'Online') AS location,
	         COALESCE(date_needed,'Anytime') AS date_needed,
	         COALESCE(status,'pending') AS status, posted_at
	         FROM jobs WHERE user_id = ? ORDER BY posted_at DESC, id DESC";
	if ($pst = @mysqli_prepare($db, $psql)) {
		mysqli_stmt_bind_param($pst, 'i', $viewerId);
		if (@mysqli_stmt_execute($pst)) {
			$pr = @mysqli_stmt_get_result($pst);
			while ($row = @mysqli_fetch_assoc($pr)) $posted_list[] = $row;
		}
		@mysqli_stmt_close($pst);
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>My Gawain • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
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

		/* Replace old tooltip nav with profile.php sidebar nav */
		.dash-float-nav {
			position: fixed;
			top: 0;
			right: 0;
			bottom: 0;
			z-index: 1000;
			display: flex !important;
			flex-direction: column;
			justify-content: flex-start;
			gap: 8px;
			padding: 12px 8px 8px 8px;
			border-right: 0;
			background: rgba(255,255,255,.95);
			backdrop-filter: saturate(1.15) blur(12px);
			border-top-left-radius: 16px;
			border-bottom-left-radius: 16px;
			border-top-right-radius: 0;
			border-bottom-right-radius: 0;
			box-shadow: 0 8px 24px rgba(0,120,166,.28), 0 0 0 1px rgba(255,255,255,.4) inset;
			transition: width .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s ease;
			width: 56px;
			overflow: hidden;
		}
		.dash-float-nav:hover {
			width: 200px;
			box-shadow: 0 12px 32px rgba(0,120,166,.35), 0 0 0 1px rgba(255,255,255,.5) inset;
		}

		/* Brand (top-centered) with hover swap: job_logo -> bluefont */
		.dash-float-nav .nav-brand {
			display: grid;
			place-items: center;
			position: relative;
			height: 56px;
			padding: 6px 0;
		}
		.dash-float-nav .nav-brand a {
			display: block; width: 100%; height: 100%; position: relative; text-decoration: none;
		}
		.dash-float-nav .nav-brand img {
			position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
			display: block; object-fit: contain; pointer-events: none;
			transition: opacity .25s ease, transform .25s ease, width .3s ease;
		}
		.dash-float-nav .nav-brand .logo-small { width: 26px; height: auto; opacity: 1; }
		.dash-float-nav .nav-brand .logo-wide { width: 160px; height: auto; opacity: 0; }
		.dash-float-nav:hover .nav-brand .logo-small { opacity: 0; transform: translate(-50%, -50%) scale(.96); }
		.dash-float-nav:hover .nav-brand .logo-wide { opacity: 1; transform: translate(-50%, -50%) scale(1); }

		/* Groups */
		.dash-float-nav > .nav-main { display: grid; gap: 8px; align-content: start; }
		.dash-float-nav > .nav-settings { margin-top: auto; display: grid; gap: 8px; }

		/* Links and icons */
		.dash-float-nav a {
			position: relative;
			width: 40px; height: 40px;
			display: grid; grid-template-columns: 40px 1fr;
			place-items: center; align-items: center;
			border-radius: 12px; color: #0f172a; text-decoration: none;
			transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4,0,0.2,1);
			outline: none; white-space: nowrap;
		}
		.dash-float-nav:hover a { width: 184px; }
		.dash-float-nav a:hover:not(.active) {
			background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
			transform: scale(1.05);
		}
		.dash-float-nav a:focus-visible { box-shadow: 0 0 0 3px rgba(0,120,166,.3); }
		.dash-float-nav a.active {
			background: linear-gradient(135deg, #0078a6 0%, #006a94 100%);
			color: #fff; box-shadow: 0 6px 18px rgba(0,120,166,.4);
		}
		.dash-float-nav .dash-icon {
			width: 18px; height: 18px; justify-self: center; object-fit: contain; transition: transform .2s ease;
		}
		.dash-float-nav a:hover .dash-icon { transform: scale(1.1); }
		/* Browse uses the logo image and no text */
		.dash-float-nav a .dash-text {
			opacity: 0; transform: translateX(-10px);
			transition: opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s;
			font-weight: 800; font-size: .85rem; color: inherit; justify-self: start; padding-left: 8px;
		}
		.dash-float-nav:hover a .dash-text { opacity: 1; transform: translateX(0); }

		/* Remove top bar on this page */
		.dash-topbar { display: none !important; }

		/* Sidebar nav: match settings.php colors and remove border/inset line */
		.dash-float-nav {
			background: #2596be !important;
			border: none !important;
			box-shadow: 0 8px 24px rgba(0,0,0,.24) !important; /* no inset line */
		}
		.dash-float-nav a { color: #fff !important; }
		.dash-float-nav a:hover:not(.active) {
			background: rgba(255,255,255,.15) !important;
			color: #fff !important;
		}
		/* Active state readable on blue background */
		.dash-float-nav a.active {
			background: rgba(255,255,255,.22) !important;
			color: #fff !important;
			box-shadow: 0 6px 18px rgba(0,0,0,.22) !important;
		}
		.dash-float-nav a.active::after { display: none !important; }

		/* Unbold all texts, except title and nav bar */
		:root { --fw-normal: 400; --fw-bold: 800; }
		body, body *:not(svg):not(path) { font-weight: var(--fw-normal) !important; }

		/* Keep page title bold */
		.mq-title { font-weight: var(--fw-bold) !important; }

		/* Keep right floating nav labels bold */
		.dash-float-nav a,
		.dash-float-nav a .dash-text,
		.dash-float-nav a span { font-weight: var(--fw-bold) !important; }

		/* Slightly shift content downward for breathing room */
		.dash-content { margin-top: clamp(12px, 4vh, 28px) !important; }

		/* Empty state: center and push below the fold so background art shows above */
		.mq-empty {
			display: flex;
			flex-direction: column;
			align-items: center;
			text-align: center;
			gap: 12px;
			/* push lower on larger screens, keep reasonable on small */
			margin-top: clamp(18vh, 22vh, 30vh) !important;
			padding: 0 18px;
		}

		/* Browse button styling to match mock */
		.mq-browse {
			display: inline-block;
			background: #7cd4c4;
			color: #0b2c24;
			padding: 12px 20px;
			border-radius: 12px;
			font-weight: 800;
			text-decoration: none;
			box-shadow: 0 6px 18px rgba(11,44,36,0.12);
		}

		/* Filter modal / bottom-sheet styles (page-scoped) */
		.mq-filter-modal { position: fixed; inset: 0; display: none; z-index: 1200; }
		.mq-filter-modal[aria-hidden="false"] { display: block; }
		.mq-filter-backdrop { position: absolute; inset: 0; background: rgba(4,6,8,0.45); }
		.mq-filter-sheet { position: absolute; left: 0; right: 0; bottom: 0; margin: 0 auto; max-width: 720px; background: #fff; border-top-left-radius: 18px; border-top-right-radius: 18px; padding: 18px 18px 28px; box-shadow: 0 -12px 40px rgba(2,6,23,.16); }
		.mq-filter-header { display: grid; grid-template-columns: auto 1fr auto; gap: 12px; align-items: center; margin-bottom: 12px; position: relative; }
		.mq-filter-title { margin: 0; text-align: center; font-size: 1.05rem; font-weight: 800; }
		.mq-filter-reset, .mq-filter-close { background: transparent; border: none; color: #374151; font-weight: 600; font-size: .95rem; padding: 6px 8px; cursor: pointer; }
		.mq-filter-close { font-size: 1.4rem; line-height: 1; }

		.mq-filter-form { display: grid; gap: 14px; }
		.mq-filter-item { display: flex; gap: 12px; align-items: center; padding: 8px 6px; border-radius: 10px; }
		.mq-filter-item input[type="checkbox"] { width: 22px; height: 22px; appearance: none; -webkit-appearance: none; border: 2px solid #d1d5db; border-radius: 6px; display: inline-grid; place-items: center; cursor: pointer; }
		.mq-filter-item input[type="checkbox"]:checked { background: #0b2c24; border-color: #0b2c24; }
		.mq-filter-item span { color: #0b2c24; font-weight: 500; }

		.mq-filter-apply { margin-top: 8px; width: 100%; background: #111827; color: #fff; border: none; padding: 14px 18px; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; }
		/* NEW: simple list styling for Offered cards */
		.mg-list { max-width: 980px; margin: 12px auto; padding: 0 16px; display: grid; gap: 10px; }
		.mg-item { background:#fff; border:2px solid #e2e8f0; border-radius:14px; padding:12px; }
		.mg-top { display:flex; align-items:center; gap:12px; }
		.mg-title { font-weight:800; margin:0; color:#0f172a; flex:1; }
		.mg-amt { margin-left:auto; font-weight:900; color:#0078a6; white-space:nowrap; }
		.mg-sub { display:flex; align-items:center; gap:10px; margin-top:6px; color:#64748b; font-size:.95rem; }
		.mg-dot { width:6px; height:6px; background:#0f172a; border-radius:999px; display:inline-block; }
		.mg-meta { margin-top:6px; color:#94a3b8; font-size:.9rem; }
		.mg-link { text-decoration:none; color:inherit; display:block; }
		/* tiny avatar in meta */
		.mg-av { width:20px; height:20px; border-radius:50%; object-fit:cover; display:inline-block; vertical-align:middle; }

		/* Status badge styling */
		.mg-badge {
			display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px;
			font-size: .8rem; font-weight: 700; white-space: nowrap; margin-left: 8px;
		}
		.mg-badge.pending { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24; }
		.mg-badge.accepted { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
		.mg-badge.rejected { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
		.mg-badge.withdrawn { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
		.mg-badge.inprogress { background: #dbeafe; color: #1e40af; border: 1px solid #60a5fa; }
		.mg-badge.completed { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
		.mg-badge.cancelled { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }

		/* Posted list cards */
		.pg-list { max-width:980px; margin:12px auto; padding:0 16px; display:grid; gap:10px; }
		.pg-item { background:#fff; border:2px solid #e2e8f0; border-radius:14px; padding:12px; }
		.pg-top { display:flex; align-items:center; gap:12px; }
		.pg-title { font-weight:800; margin:0; color:#0f172a; flex:1; }
		.pg-meta { margin-top:6px; color:#94a3b8; font-size:.85rem; display:flex; gap:10px; flex-wrap:wrap; }
		.mg-badge.approved { background:#d1fae5; color:#065f46; border:1px solid #34d399; }
		.mg-badge.closed { background:#f3f4f6; color:#374151; border:1px solid #d1d5db; }
		.mg-badge.rejected { background:#fee2e2; color:#991b1b; border:1px solid #f87171; }
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-shell">
		<main class="dash-content">
			<header class="mq-header mq-header-centered">
				<h1 class="mq-title">My Gawain</h1>
			</header>
			<nav class="mq-tabs" role="tablist" aria-label="Gawain tabs">
				<?php $tab = isset($_GET['tab']) ? $_GET['tab'] : 'offered'; ?>
				<a class="mq-tab <?php echo $tab==='offered'?'active':''; ?>" href="?tab=offered" role="tab" aria-selected="<?php echo $tab==='offered'?'true':'false'; ?>">Offered</a>
				<a class="mq-tab <?php echo $tab==='posted'?'active':''; ?>" href="?tab=posted" role="tab" aria-selected="<?php echo $tab==='posted'?'true':'false'; ?>">Posted</a>
			</nav>
			<div class="mq-filter-row">
				<button class="mq-filter" id="mqFilterBtn" type="button" aria-haspopup="dialog" aria-controls="mqFilterModal" aria-expanded="false">Filter: <strong id="mqFilterLabel">All</strong></button>
			</div>

			<?php if ($tab === 'offered' && !empty($offered_list)): ?>
			<section class="mg-list" aria-label="Offers" id="offerList">
				<?php foreach ($offered_list as $o): 
					$link = './gawain-detail.php?id='.(int)$o['job_id'];
					$name = offerer_name($o);
					$av   = (string)($o['offerer_avatar'] ?? '');
					if ($av !== '' && !preg_match('#^https?://#i', $av)) $av = '../' . ltrim($av, '/');
					$status = strtolower(trim($o['status'] ?? 'pending'));
					$statusLabel = ucfirst($status);
					// Map filter checkbox values to actual DB status
					$filterClass = $status;
					if ($status === 'accepted') $filterClass = 'inprogress'; // if you want "In-progress" to match accepted
				?>
				<a class="mg-link" href="<?php echo e($link); ?>" data-status="<?php echo e($status); ?>">
					<article class="mg-item">
						<div class="mg-top">
							<h3 class="mg-title"><?php echo e($o['job_title'] ?? 'Untitled'); ?></h3>
							<div style="display:flex; align-items:center; gap:4px;">
								<div class="mg-amt"><?php echo peso($o['amount']); ?></div>
								<span class="mg-badge <?php echo e($status); ?>"><?php echo e($statusLabel); ?></span>
							</div>
						</div>
						<div class="mg-sub">
							<span style="display:inline-flex; align-items:center; gap:6px;">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
								<?php echo e($o['location']); ?>
							</span>
							<span class="mg-dot"></span>
							<span>On <?php echo e($o['date_needed']); ?></span>
						</div>
						<div class="mg-meta">
							<?php if ($av): ?><img class="mg-av" src="<?php echo e($av); ?>" alt=""><?php endif; ?>
							Offer received • <?php echo date('M j, Y g:ia', strtotime($o['created_at'])); ?>
						</div>
					</article>
				</a>
				<?php endforeach; ?>
			</section>
			<?php elseif ($tab === 'posted'): ?>
		<?php if (!empty($posted_list)): ?>
		<section class="pg-list" aria-label="Posted jobs">
			<?php foreach ($posted_list as $j): 
				$st = strtolower(trim($j['status']));
				$label = ucfirst($st);
				$link = './gawain-detail.php?id='.(int)$j['id'];
			?>
			<a href="<?php echo e($link); ?>" class="mg-link" style="text-decoration:none;">
				<article class="pg-item">
					<div class="pg-top">
						<h3 class="pg-title"><?php echo e($j['title'] ?: 'Untitled'); ?></h3>
						<span class="mg-badge <?php echo e($st); ?>"><?php echo e($label); ?></span>
					</div>
					<div class="mg-sub" style="margin-top:6px; display:flex; flex-wrap:wrap; gap:10px; color:#64748b; font-size:.95rem;">
						<span style="display:inline-flex; align-items:center; gap:6px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
							<?php echo e($j['location']); ?>
						</span>
						<span class="mg-dot"></span>
						<span>On <?php echo e($j['date_needed']); ?></span>
					</div>
					<div class="pg-meta">
						<span>Posted <?php echo date('M j, Y g:ia', strtotime($j['posted_at'])); ?></span>
						<?php if ($st === 'pending'): ?><span style="font-weight:700;">Awaiting admin review</span><?php endif; ?>
					</div>
				</article>
			</a>
			<?php endforeach; ?>
		</section>
		<?php else: ?>
		<section class="mq-empty" aria-label="Empty posted">
			<p class="empty-text">You have not posted any quests yet. Create one to see it here pending review.</p>
			<a class="btn mq-browse" href="./post.php">Post a quest</a>
		</section>
		<?php endif; ?>
			<?php else: ?>
			<section class="mq-empty" aria-label="Empty state">
				<p class="empty-text">Uh oh! You don't have any activity yet. Head over to the homepage to make offers to gawain that interest you.</p>
				<a class="btn mq-browse" href="./home-gawain.php">Browse Gawain</a>
			</section>
			<?php endif; ?>

			<!-- Filter Bottom Sheet Modal -->
			<div class="mq-filter-modal" id="mqFilterModal" role="dialog" aria-modal="true" aria-labelledby="mqFilterTitle" aria-hidden="true" data-tab="<?php echo $tab; ?>">
				<div class="mq-filter-backdrop" data-filter-close></div>
				<div class="mq-filter-sheet" role="document">
					<div class="mq-filter-header">
						<button class="mq-filter-reset" id="mqFilterReset" type="button">Reset</button>
						<h3 class="mq-filter-title" id="mqFilterTitle">Apply filters</h3>
						<button class="mq-filter-close" type="button" aria-label="Close" data-filter-close>&times;</button>
					</div>
					<form class="mq-filter-form" id="mqFilterForm">
						<?php if($tab === 'offered'): ?>
							<label class="mq-filter-item"><input type="checkbox" name="status" value="pending"> <span>Pending offers</span></label>
							<label class="mq-filter-item"><input type="checkbox" name="status" value="inprogress"> <span>In-progress</span></label>
							<label class="mq-filter-item"><input type="checkbox" name="status" value="completed"> <span>Completed</span></label>
							<label class="mq-filter-item"><input type="checkbox" name="status" value="cancelled"> <span>Cancellations</span></label>
						<?php else: ?>
							<label class="mq-filter-item"><input type="checkbox" name="status" value="open"> <span>Open Gawain</span></label>
							<label class="mq-filter-item"><input type="checkbox" name="status" value="inprogress"> <span>In-progress Gawain</span></label>
							<label class="mq-filter-item"><input type="checkbox" name="status" value="completed"> <span>Completed Gawain</span></label>
							<label class="mq-filter-item"><input type="checkbox" name="status" value="deleted"> <span>Deleted Gawain</span></label>
						<?php endif; ?>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="pending"> <span>Pending offers</span></label>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="accepted"> <span>In-progress</span></label>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="completed"> <span>Completed</span></label>
						<label class="mq-filter-item"><input type="checkbox" name="status" value="rejected"> <span>Cancellations</span></label>

						<button class="mq-filter-apply" id="mqFilterApply" type="button">Apply</button>
					</form>
				</div>
			</div>
		</main>
	</div>

	<!-- Floating right-side navigation (replaces bottom nav) -->
	<nav class="dash-float-nav" id="dashNav">
		<div class="nav-brand">
			<a href="./home-gawain.php" title="">
				<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub logo">
				<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub">
			</a>
		</div>

		<div class="nav-main">
			<a href="./profile.php" aria-label="Profile">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
				</svg>
				<span class="dash-text">Profile</span>
			</a>
			<a href="./post.php" aria-label="Post">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/>
				</svg>
				<span class="dash-text">Post</span>
			</a>
			<a href="./my-gawain.php" class="active" aria-current="page" aria-label="My Gawain">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M4 7h16M4 12h10M4 17h7"/>
				</svg>
				<span class="dash-text">My Gawain</span>
			</a>
			<a href="./chats.php" aria-label="Chats">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
				</svg>
				<span class="dash-text">Chats</span>
			</a>
		</div>

		<div class="nav-settings">
			<a href="./about-us.php" aria-label="About Us">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
				</svg>
				<span class="dash-text">About Us</span>
			</a>
			<a href="./terms-and-conditions.php" aria-label="Terms & Conditions">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M6 4h12v16H6z"/><path d="M8 8h8M8 12h8M8 16h5"/>
				</svg>
				<span class="dash-text">Terms & Conditions</span>
			</a>
			<a href="./profile.php?logout=1" aria-label="Log out">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 21V3"/>
				</svg>
				<span class="dash-text">Log out</span>
			</a>
		</div>
	</nav>

	<script>
	// Filter modal logic
	(function(){
		const modal = document.getElementById('mqFilterModal');
		const btn = document.getElementById('mqFilterBtn');
		const closeEls = modal?.querySelectorAll('[data-filter-close]');
		const resetBtn = document.getElementById('mqFilterReset');
		const applyBtn = document.getElementById('mqFilterApply');
		const form = document.getElementById('mqFilterForm');
		const label = document.getElementById('mqFilterLabel');
		const list = document.getElementById('offerList');

		if (!modal || !btn || !form || !label || !list) return;

		function open(){ modal.setAttribute('aria-hidden','false'); btn.setAttribute('aria-expanded','true'); }
		function close(){ modal.setAttribute('aria-hidden','true'); btn.setAttribute('aria-expanded','false'); }

		btn.addEventListener('click', open);
		closeEls.forEach(el => el.addEventListener('click', close));

		resetBtn.addEventListener('click', ()=>{
			form.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
			applyFilters();
		});

		applyBtn.addEventListener('click', ()=>{
			applyFilters();
			close();
		});

		function applyFilters(){
			const checked = Array.from(form.querySelectorAll('input[name=status]:checked')).map(c => c.value);
			const cards = list.querySelectorAll('.mg-link');
			let visible = 0;

			cards.forEach(card => {
				const status = card.getAttribute('data-status');
				if (checked.length === 0 || checked.includes(status)){
					card.style.display = 'block';
					visible++;
				} else {
					card.style.display = 'none';
				}
			});

			label.textContent = checked.length === 0 ? 'All' : `${checked.length} filter${checked.length>1?'s':''}`;
			
			// Show empty state if no results
			const empty = list.parentElement.querySelector('.mq-empty');
			if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
		}

		// Initialize
		applyFilters();
	})();
	</script>
</body>
</html>