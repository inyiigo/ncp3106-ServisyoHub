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
<<<<<<< HEAD
		/* Match bottom nav behavior from home-gawain.php and center at bottom */
		.dash-bottom-nav {
			position: fixed;
			left: 50%;
			right: auto;
			bottom: 16px;
=======
		/* Side nav: compact by default, expand on hover */
		.dash-aside { width: 64px; transition: width 200ms ease, box-shadow 180ms ease; overflow: hidden; }
		.dash-aside:hover { width: 240px; box-shadow: 0 12px 28px rgba(2,6,23,.12); }
		.dash-aside .dash-nav a { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 10px; }
		.dash-aside .dash-nav .dash-icon { width: 20px; height: 20px; flex: 0 0 20px; }

		/* Bottom nav: centered at the bottom */
		.dash-bottom-nav { position: fixed; left: 50%; right: auto; bottom: 16px; transform: translateX(-50%) scale(0.92); transform-origin: bottom center; margin: 0; width: max-content; transition: transform 180ms ease, box-shadow 180ms ease; border: 3px solid #0078a6; background: transparent; z-index: 999; }
		.dash-bottom-nav:hover { transform: translateX(-50%) scale(1); box-shadow: 0 12px 28px rgba(2,6,23,.12); }
		@media (max-width:520px) { .dash-bottom-nav { bottom: 12px; transform: translateX(-50%); } }
		/* Main content wrapper: add responsive top margin for comfortable spacing */
		.dash-content { max-width: 1100px; margin: clamp(12px, 9vh, 96px) auto 0; padding: 0 16px; position: relative; z-index: 1; }
		.home-hero { text-align: center; }
		.dash-topbar { border-bottom: 3px solid #0078a6; position: relative; z-index: 1; }
		.bg-logo { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 25%; max-width: 350px; opacity: 0.15; z-index: 0; pointer-events: none; }
		.bg-logo img { width: 100%; height: auto; display: block; }
		.svc-cats { max-width: 1100px; margin: 10px auto 8px; padding: 0 16px; display: flex; gap: 10px; overflow-x: auto; scrollbar-width: none; }
		.svc-cats::-webkit-scrollbar { display: none; }
		.svc-cat { appearance: none; border: 2px solid #e2e8f0; background: #fff; color: #0f172a; border-radius: 999px; padding: 8px 16px; font-weight: 800; font-size: .9rem; white-space: nowrap; cursor: pointer; transition: all .15s ease; }
		.svc-cat:hover { background: #f1f5f9; }
		.svc-cat.active { background:#0078a6; color:#fff; border-color:#0078a6; }
		.svc-cats-wrap { max-width:1100px; margin:10px auto 8px; padding:0 16px; position:relative; }
		/* Ensure pills don't sit under the nav arrows */
		.svc-cats-wrap .svc-cats { margin:0 !important; padding:0 48px !important; }
		.cat-nav-btn { position:absolute; top:50%; transform:translateY(-50%); width:34px; height:34px; border-radius:999px; border:2px solid #e2e8f0; background:#fff; color:#0f172a; display:grid; place-items:center; cursor:pointer; box-shadow:0 6px 16px rgba(0,0,0,.08); transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease; z-index:2; }
		.cat-nav-btn:hover { transform:translateY(-50%) scale(1.05); border-color:#0078a6; background:#f8fafc; }
		.cat-nav-btn[disabled] { opacity:.35; cursor:default; transform:translateY(-50%); box-shadow:none; }
		.cat-nav-btn.prev { left:8px; }
		.cat-nav-btn.next { right:8px; }
		.cat-nav-btn svg { width:16px; height:16px; }
		.results-bar { max-width: 1100px; margin: 0 auto 10px; padding: 0 16px; display:flex; align-items:center; justify-content: space-between; gap:10px; }
		.results-left { display:flex; align-items:center; gap:8px; color:#64748b; font-weight:700; }
		.results-left .ico { width:18px; height:18px; color: #64748b; }
		.results-right { display:flex; align-items:center; gap:8px; }
		.notify-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border-radius:10px; border:2px solid #e2e8f0; background:#fff; color:#0f172a; font-weight:800; cursor:pointer; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
		.notify-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.08); border-color:#0078a6; }
		.toggle-btn { display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:10px; border:2px solid #e2e8f0; background:#fff; color:#0f172a; cursor:pointer; text-decoration:none; transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease; }
		.toggle-btn:hover { background:#f8fafc; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.08); border-color:#0078a6; }
		.toggle-btn svg, .toggle-btn img { width:18px; height:18px; }
		.svc-list { max-width: 1100px; margin: 0 auto 18px; padding: 0 16px; display:grid; gap:10px; }
		/* Align Recent Posts with above elements */
		.jobs-feed { max-width: 1100px; margin: 0 auto 18px; padding: 0 16px; }
		.feed-title { font-weight:800; color:#0f172a; margin: 0 0 8px; }
		.svc-card { display:grid; grid-template-columns: 1fr auto; gap:12px; align-items:flex-start; background:#fff; border:2px solid #e2e8f0; border-radius:12px; padding:14px 16px; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
		.svc-card:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0,0,0,.08); border-color:#0078a6; }
		.svc-title { margin:0 0 8px; font-weight:800; color:#0f172a; }
		.svc-meta { display:flex; flex-wrap:wrap; gap:8px 14px; color:#64748b; font-size:.9rem; }
		.svc-meta .item { display:inline-flex; align-items:center; gap:6px; white-space:nowrap; }
		.svc-meta .item svg { width:14px; height:14px; }
		.svc-posted { margin-top:8px; display:flex; align-items:center; gap:8px; color:#94a3b8; font-size:.85rem; }
		.svc-av { width:22px; height:22px; border-radius:50%; background:#e2e8f0; color:#0f172a; display:grid; place-items:center; font-weight:800; font-size:.75rem; }
		.svc-price { display:grid; align-content:center; gap:4px; text-align:right; }
		.svc-price .amt { font-weight:800; color:#0078a6; }
		.svc-price .note { color:#94a3b8; font-size:.8rem; }
		/* Search row: bar on left, bell on right */
		.svc-search { max-width: 1100px; margin: clamp(16px, 6vh, 72px) auto 16px; padding: 0 16px; position: relative; display:flex; align-items:center; gap:12px; }
		.svc-search-box { display: flex; align-items: center; gap: 12px; background: #fff; border: 2px solid #e2e8f0; border-radius: 999px; padding: 10px 12px; box-shadow: 0 4px 12px rgba(0,0,0,.06); transition: box-shadow .15s ease, border-color .15s ease; }
		.svc-search-box { flex: 1; }
		.svc-search-box:focus-within { border-color: #0078a6; box-shadow: 0 8px 20px rgba(0,120,166,.12); }
		.svc-search-icon { width: 20px; height: 20px; color: #64748b; flex-shrink: 0; }
		.svc-search-input { appearance: none; border: 0; outline: 0; background: transparent; font: inherit; color: #0f172a; flex: 1; font-size: .95rem; }
		.svc-search-input::placeholder { color: #94a3b8; }

		/* Notification button inside search */
		.svc-notify-btn { appearance:none; border:0; outline:0; background: transparent; display:inline-grid; place-items:center; width: 32px; height: 32px; border-radius: 999px; color:#64748b; cursor:pointer; flex-shrink:0; position: relative; transition: background .15s ease, color .15s ease, transform .15s ease; }
		.svc-notify-btn:hover { background:#f8fafc; color:#0078a6; transform: translateY(-1px); }
		.svc-notify-btn:active { transform: translateY(0); }
		.svc-notify-btn svg { width: 18px; height: 18px; }

		/* Counter badge */
		.svc-badge { position:absolute; top:-2px; right:-2px; min-width:16px; height:16px; padding:0 4px; border-radius:999px; background:#ef4444; color:#fff; font-weight:800; font-size:10px; line-height:16px; display:inline-grid; place-items:center; box-shadow:0 0 0 2px #fff; }
		.svc-badge[data-count="0"], .svc-badge[hidden] { display:none; }

		/* Drawer panel */
		.notify-wrap { position: relative; display: inline-flex; align-items: center; }
		.svc-notify-drawer { position:absolute; right:0; top: calc(100% + 10px); width: min(92vw, 360px); max-height: 60vh; overflow:auto; background:#fff; border:2px solid #e2e8f0; border-radius: 14px; box-shadow: 0 12px 28px rgba(0,0,0,.16); z-index:1100; opacity:0; transform: translateY(-4px); transition: opacity .18s ease, transform .18s ease; }
		.svc-notify-drawer[hidden] { display:block !important; opacity:0; pointer-events:none; }
		.svc-notify-drawer.is-open { opacity:1; transform: translateY(0); }
		.svc-notify-header { position:sticky; top:0; background:#fff; padding:12px 14px; border-bottom:2px solid #e2e8f0; font-weight:800; color:#0f172a; z-index:1; }
		.svc-tabs { display:flex; gap:8px; padding:8px; border-bottom:2px solid #e2e8f0; background:#fff; position:sticky; top:44px; z-index:1; }
		.svc-tab { flex:1; appearance:none; border:2px solid #e2e8f0; background:#fff; color:#0f172a; border-radius:999px; padding:8px 12px; font-weight:800; cursor:pointer; transition: background .15s ease, color .15s ease, border-color .15s ease, transform .15s ease; }
		.svc-tab:hover { background:#f8fafc; transform: translateY(-1px); }
		.svc-tab[aria-selected="true"], .svc-tab.is-active { background:#0078a6; color:#fff; border-color:#0078a6; box-shadow:0 6px 16px rgba(0,120,166,.22); transform:none; }
		.svc-notify-list { list-style:none; margin:0; padding:8px; display:grid; gap:8px; }
		.svc-notify-item { display:grid; grid-template-columns: 1fr auto; gap:8px; align-items:flex-start; padding:10px 12px; border:2px solid #e2e8f0; border-radius:12px; background:#fff; }
		.svc-notify-item .title { font-weight:800; color:#0f172a; margin:0 0 4px; }
		.svc-notify-item .meta { color:#64748b; font-size:.85rem; }
		.svc-notify-item .time { color:#94a3b8; font-size:.8rem; white-space:nowrap; }

		/* Backdrop */
		.svc-notify-backdrop { position:fixed; inset:0; background: rgba(15,23,42,.28); backdrop-filter: blur(1px); z-index:1090; opacity:0; pointer-events:none; transition: opacity .18s ease; }
		.svc-notify-backdrop.is-open { opacity:1; pointer-events:auto; }
		/* Sample post layout container */
		.sample-post { max-width:1100px; margin:0 auto 24px; padding:0 16px; }
		.sample-post .sample-title { font-weight:800; color:#0f172a; margin: 0 0 8px; }
		/* Page background: use plain white (homebackground asset left intact but not applied) */
		body.theme-profile-bg {
			background: #ffffff !important;
			background-attachment: initial !important;
		}
		.dash-shell { position: relative; z-index: 1; }
		.dash-overlay { display: none !important; }

		/* Right-side full-height sidebar nav (from profile.php) */
		.dash-float-nav {
			position: fixed; top: 0; right: 0; bottom: 0;
>>>>>>> 396fc958b334ad4ea2089ce90cb5a9f70664fb00
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
<<<<<<< HEAD
		.dash-float-nav a.active::after {
			content: ""; position: absolute; left: -5px; width: 3px; height: 18px;
			background: linear-gradient(180deg, #0078a6 0%, #0078a6 100%);
			border-radius: 2px; box-shadow: 0 0 0 2px rgba(255,255,255,.9), 0 0 12px rgba(0,120,166,.6);
		}
=======
		/* Remove the active left strip line */
		.dash-float-nav a.active::after { display: none !important; }

		/* Unbold all texts sitewide, except nav bars */
		:root { --fw-normal: 400; --fw-bold: 800; }
		body, body *:not(svg):not(path) { font-weight: var(--fw-normal) !important; }
		.dash-aside .dash-nav a,
		.dash-aside .dash-nav a span,
		.dash-float-nav a,
		.dash-float-nav a .dash-text { font-weight: var(--fw-bold) !important; }

		/* Re-bold specific titles (e.g., "Household Cleaning (2-Bedroom)") */
		.svc-title,
		.fc-title { font-weight: var(--fw-bold) !important; }
>>>>>>> 396fc958b334ad4ea2089ce90cb5a9f70664fb00
	</style>
>>>>>>> 1501966ac8735e5c32a1fc11945ef6cd1f34443d
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
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
<<<<<<< HEAD
=======

			<div class="svc-notify-backdrop" hidden></div>

			<section class="svc-list" aria-label="Nearby posts">
				<?php if (!$dbAvailable): ?>
					<!-- DB unavailable: hide message from UI -->
				<?php elseif (empty($jobs)): ?>
					<div class="form-card glass-card" style="background:#fff;color:#0f172a;border-color:#e2e8f0">
						No posts yet. Be the first to post using the + button.
					</div>
				<?php else: ?>
					<?php foreach ($jobs as $j): ?>
						<?php $jid = isset($j['id']) ? (int)$j['id'] : 0; ?>
						<a class="svc-card" href="./gawain-detail.php<?php echo $jid ? ('?id=' . $jid) : ''; ?>" aria-label="View post: <?php echo e($j['title']); ?>">
							<div>
								<h3 class="svc-title" title="<?php echo e($j['title']); ?>"><?php echo e($j['title']); ?></h3>
								<div class="svc-meta">
									<?php if (!empty($j['location'])): ?>
										<span class="item" title="<?php echo e($j['location']); ?>">
											<svg viewBox="0 0 24 24"><path d="M12 21s-6-4.35-6-9a6 6 0 1 1 12 0c0 4.65-6 9-6 9Z"/><circle cx="12" cy="12" r="2"/></svg>
											<?php echo e($j['location']); ?>
										</span>
									<?php endif; ?>
									<?php if (!empty($j['date_needed'])): ?>
										<span class="item">
											<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
											On <?php echo e($j['date_needed']); ?>
										</span>
									<?php endif; ?>
								</div>
								<div class="svc-posted">
									<span class="svc-av"><?php echo htmlspecialchars($avatar); ?></span>
									<span>Posted <?php echo e(time_ago($j['posted_at'])); ?></span>
								</div>
							</div>
							<div class="svc-price">
								<span class="amt"><?php echo !empty($j['budget']) ? '₱'.e($j['budget']) : 'Negotiable'; ?></span>
								<?php if (empty($j['budget'])): ?><span class="note">Negotiable</span><?php endif; ?>
							</div>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</section>

			<!-- Recent Posts feed (before gawain) -->
			<section class="jobs-feed" aria-label="Recent posts">
				<div class="feed-title">
					<span>Recent Posts</span>
				</div>

				<?php if (!$dbAvailable): ?>
					<!-- Hide DB error details from UI -->
				<?php elseif (empty($jobs)): ?>
					<p class="feed-empty">No recent posts yet. Be the first to post using the + button.</p>
				<?php else: ?>
					<div class="feed-grid">
						<?php foreach ($jobs as $j): ?>
							<?php $jid = isset($j['id']) ? (int)$j['id'] : 0; ?>
							<a href="./gawain-detail.php<?php echo $jid ? ('?id=' . $jid) : ''; ?>" class="feed-card" style="text-decoration:none; color:inherit;">
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
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>

			<!-- Sample Post Layout -->
			<section class="sample-post" aria-label="Sample post layout">
				<a class="svc-card" href="./gawain-detail.php" aria-label="View sample post">
					<div>
						<h3 class="svc-title" title="Household Cleaning (2-Bedroom)">Household Cleaning (2-Bedroom)</h3>
						<div class="svc-meta">
							<span class="item" title="Quezon City">
								<svg viewBox="0 0 24 24"><path d="M12 21s-6-4.35-6-9a6 6 0 1 1 12 0c0 4.65-6 9-6 9Z"/><circle cx="12" cy="12" r="2"/></svg>
								Quezon City
							</span>
							<span class="item">
								<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
								On Nov 12, 2025
							</span>
						</div>
						<div class="svc-posted">
							<span class="svc-av">S</span>
							<span>Posted 1h ago</span>
						</div>
					</div>
					<div class="svc-price">
						<span class="amt">₱1,800</span>
						<span class="note">Fixed price</span>
					</div>
				</a>
			</section>

>>>>>>> 396fc958b334ad4ea2089ce90cb5a9f70664fb00
		</main>
	</div>

<<<<<<< HEAD
<<<<<<< HEAD
	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./post.php" aria-label="Post">
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
		</a>
=======
	<!-- Replace previous tooltip nav with profile.php sidebar nav -->
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
<<<<<<< HEAD
			<a href="./settings.php" aria-label="Settings">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527c.45-.322 1.07-.26 1.45.12l.773.774c.38.38.442 1 .12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.322.45.26 1.07-.12 1.45l-.774.773c-.38.38-1 .442-1.45.12l-.737-.527c-.35-.25-.806-.272-1.204-.107-.397.165-.71.505-.78.93l-.15.893c-.09.542-.56.94-1.109.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.893c-.071-.425-.384-.765-.781-.93-.398-.165-.854-.143-1.204.107l-.738.527c-.45.322-1.07.26-1.45-.12l-.773-.774c-.38-.38-.442-1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15C3.4 13.02 3 12.55 3 12V10.906c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.764-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35 .25 .806 .272 1.204 .107 .397 -.165 .71 -.505 .78 -.93l .149 -.894z"/>
					<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
=======
			<a href="./about-us.php" aria-label="About Us">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
>>>>>>> 396fc958b334ad4ea2089ce90cb5a9f70664fb00
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
</body>
</html>