<?php
session_start();
// Optional: basic guard
// if (empty($_SESSION['is_admin'])) { header('Location: ./login.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Admin Dashboard • ServisyoHub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">
	<style>
		:root{
			--blue:#0078a6;
			--bg:#ffffff;
			--text:#0f172a;
			--muted:#64748b;
			--line:#e2e8f0;
			--card:#ffffff;
			--shadow:0 8px 24px rgba(2,6,23,.08);
			--topbar-h: 0px; /* was 64px; no topbar now */
		}
		*{box-sizing:border-box}
		html,body{height:100%}
		body {
			margin:0;
			background: #fff; /* Remove blue gradient, use plain white */
			color:var(--text);
			font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;
		}
		a{text-decoration:none;color:inherit}

		/* layout */
		.admin-wrap{
			display:grid;
			grid-template-columns:260px 1fr;
			grid-template-areas: "aside main"; /* ensure main is outside sidebar */
			min-height:100dvh
		}
		.topbar{position:sticky;top:0;z-index:30;display:flex;align-items:center;justify-content:space-between;height:var(--topbar-h);padding:0 16px;background:#fff;border-bottom:3px solid var(--blue);box-shadow:0 4px 14px rgba(2,6,23,.06)}
		.brand{display:flex;align-items:center;gap:10px;font-weight:800}
		.brand img{height:28px}
		.menu-btn{display:none;border:0;background:transparent;font-size:22px}
		@media (max-width:960px){ .menu-btn{display:inline-flex} }
		.main{padding:16px; grid-area: main;} /* bind main to its own grid area */
		.container{max-width:1200px;margin:0 auto;display:grid;gap:14px}

		/* sidebar */
		.aside{
			background:#fff;border-right:1px solid var(--line);padding:16px 10px;
			position:sticky;top:var(--topbar-h);height:calc(100dvh - var(--topbar-h));overflow:auto;
			grid-area: aside; /* bind sidebar to aside area */
		}
		.aside h3{margin:4px 10px 10px;font-size:.95rem;color:var(--muted);font-weight:800}
		.nav{display:grid;gap:6px}
		.nav a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;color:var(--text);border:1px solid transparent}
		.nav a.active{background:#f0f9ff;border-color:color-mix(in srgb, var(--blue) 30%, #0000);color:var(--blue);font-weight:800}
		.nav a:hover{background:#f8fafc;border-color:#eef2f7}
		.nav svg{width:18px;height:18px}

		/* mobile drawer keeps content separate */
		@media (max-width: 960px){
			.admin-wrap{grid-template-columns:1fr; grid-template-areas: "main";}
			.aside{
				position:fixed;
				inset:var(--topbar-h) auto 0 0;
				height:calc(100dvh - var(--topbar-h));
				transform:translateX(-100%);
				transition:transform .2s ease;
				z-index:20;
				width:260px;
				overflow:auto;
			}
			.aside.open{transform:none}
			/* mask moved outside .admin-wrap; styles remain the same */
			.aside-mask{
				position:fixed;
				inset:var(--topbar-h) 0 0 0;
				background:rgba(15,23,42,.35);
				backdrop-filter:saturate(140%) blur(2px);
				display:none;
				z-index:19
			}
			.aside-mask.show{display:block}
			body.aside-open{ overflow:hidden; }
		}

		/* grid areas */
		.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
		@media (max-width:1024px){.stats{grid-template-columns:repeat(2,1fr)}}
		@media (max-width:560px){.stats{grid-template-columns:1fr}}
		.card {
			background: linear-gradient(135deg, rgba(255,255,255,0.98) 70%, rgba(124,212,196,0.10) 100%);
			border-radius: 22px;
			padding: 28px 22px 18px 22px;
			box-shadow: 0 16px 40px rgba(37,150,190,.18), 0 2px 12px rgba(0,0,0,.10);
			border: 2px solid #b6e6f7;
			backdrop-filter: blur(8px) saturate(1.12);
			transition: box-shadow .22s, transform .22s, border-color .22s;
			position: relative;
		}
		.card:hover {
			box-shadow: 0 24px 64px rgba(37,150,190,.22), 0 6px 24px rgba(0,0,0,.14);
			transform: translateY(-2px) scale(1.012);
			border-color: #7cd4c4;
		}
		.card h4 {
			margin:0 0 10px;
			font-size:1.08rem;
			color:#2596be;
			letter-spacing:0.02em;
			font-weight: 800;
		}
		.metric{font-size:1.4rem;font-weight:800}
		.delta{font-size:.85rem;color:var(--muted)}

		.charts{display:grid;grid-template-columns:2fr 1fr;gap:12px}
		@media (max-width:960px){.charts{grid-template-columns:1fr}}

		.tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
		@media (max-width:1024px){.tiles{grid-template-columns:repeat(2,1fr)}}
		@media (max-width:560px){.tiles{grid-template-columns:1fr}}
		.tile{background:linear-gradient(135deg,#7c3aed 0%,#9333ea 60%,#a855f7 100%);color:#fff;border-radius:16px;padding:16px;box-shadow:var(--shadow)}
		.tile.orange{background:linear-gradient(135deg,#fb923c 0%,#f97316 60%,#f59e0b 100%)}
		.tile.cyan{background:linear-gradient(135deg,#06b6d4 0%,#0891b2 60%,#0ea5e9 100%)}
		.tile.blue{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 60%,#1d4ed8 100%)}
		.tile .big{font-size:1.2rem;font-weight:800;margin-top:6px}

		/* lower grid */
		.lower{display:grid;grid-template-columns:1fr 1fr;gap:12px}
		@media (max-width:960px){.lower{grid-template-columns:1fr}}
		.activity{display:grid;gap:10px}
		.act{display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--line);border-radius:12px;background:#fff}
		.badge{display:inline-block;padding:4px 8px;border-radius:999px;font-size:.75rem;font-weight:800}
		.badge.purple{background:#ede9fe;color:#6d28d9}
		.badge.green{background:#dcfce7;color:#166534}
		.badge.orange{background:#ffedd5;color:#9a3412}

		.table{width:100%;border-collapse:collapse}
		.table th,.table td{padding:12px;border-bottom:1px solid var(--line);text-align:left;font-size:.95rem}
		.table th {
			font-weight:800;
			color: #0b2c24 !important;
			background: #e0f2fe;
			border-bottom: 2px solid #b6e6f7;
		}
		.table tr {
			transition: background .15s;
		}
		.table tr:hover {
			background: #f0f9ff;
		}
		.status{padding:6px 10px;border-radius:10px;font-weight:800;font-size:.78rem}
		.status.process{background:#e0f2fe;color:#075985}
		.status.open{background:#ede9fe;color:#5b21b6}
		.status.hold{background:#fef3c7;color:#92400e}

		/* Client widgets */
		.client-overview .card .metric { font-size: 1.3rem; }
		.client-widgets { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
		@media (max-width:960px){ .client-widgets{ grid-template-columns:1fr; } }
		.c-list { margin:0; padding-left:18px; display:grid; gap:6px; color: var(--muted); }
		.table.compact th, .table.compact td { padding:8px 10px; font-size:.9rem; }
		.tag { display:inline-block; padding:4px 8px; border-radius:999px; font-size:.78rem; font-weight:800; }
		.tag.pending { background:#fef3c7; color:#92400e; }
		.tag.active { background:#e0f2fe; color:#075985; }
		.tag.done { background:#dcfce7; color:#166534; }
		/* Feedback list */
		.feedback { display:grid; gap:10px; }
		.feedback .item { display:flex; gap:10px; align-items:flex-start; color: var(--muted); }
		.feedback .dot { width:10px; height:10px; border-radius:50%; background:#0078a6; margin-top:7px; flex:0 0 10px; }

		/* NEW: section titles and 2-col layout */
		.section-title {
			margin: 18px 4px 8px;
			color: #2596be;
			font-weight: 900;
			font-size: 1.18rem;
			letter-spacing: 0.01em;
		}
		.layout-two { display:grid; grid-template-columns: 2fr 1fr; gap:12px; }
		@media (max-width: 960px){ .layout-two{ grid-template-columns:1fr; } }

		/* make nav logo small */
		.nav-logo { margin: 6px 10px 12px; display:flex; align-items:center; justify-content:center; }
		.nav-logo img { height: 32px; width: auto; display:block; }

		/* NEW: top bar inside the sidebar */
		.side-topbar{
			position: sticky;
			top: 0;
			display: flex;
			align-items: center;
			justify-content: center; /* center the logo */
			gap: 10px;
			padding: 10px 12px;
			background: #fff;
			border-bottom: 1px solid var(--line);
			z-index: 1;
		}
		.side-topbar img{
			height: 32px;
			width: auto;
			display: block;
			margin: 0 auto; /* ensure centered image */
		}

		/* NEW: floating right-side nav bar */
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
	background: #2596be !important;
	backdrop-filter: saturate(1.15) blur(12px);
	border-top-left-radius: 16px;
	border-bottom-left-radius: 16px;
	border-top-right-radius: 0;
	border-bottom-right-radius: 0;
	box-shadow: 0 8px 24px rgba(0,0,0,.24) !important;
	transition: width .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s ease;
	width: 56px;
	overflow: hidden;
}
.dash-float-nav:hover {
	width: 200px;
	box-shadow: 0 12px 32px rgba(0,0,0,.32) !important;
}
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
.dash-float-nav > .nav-main { display:grid; gap:8px; align-content:start; }
.dash-float-nav > .nav-settings { margin-top:auto; display:grid; gap:8px; }
.dash-float-nav a {
	position: relative;
	width: 40px; height: 40px;
	display: grid; grid-template-columns: 40px 1fr; place-items: center; align-items: center;
	border-radius: 12px; color: #fff !important; text-decoration: none; outline: none; white-space: nowrap;
	transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4,0,0.2,1);
}
.dash-float-nav:hover a { width: 184px; }
.dash-float-nav a:hover:not(.active) {
	background: rgba(255,255,255,.15) !important;
	transform: scale(1.05);
}
.dash-float-nav a:focus-visible { box-shadow: 0 0 0 3px rgba(255,255,255,.3); }
.dash-float-nav a.active {
	background: rgba(255,255,255,.22) !important;
	color: #fff !important;
	box-shadow: 0 6px 18px rgba(0,0,0,.22) !important;
}
.dash-float-nav a.active::after { display: none !important; }
.dash-float-nav .dash-icon {
	width: 18px; height: 18px; justify-self: center;
}
.dash-float-nav .dash-text {
	opacity: 0; transform: translateX(-10px);
	transition: opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s;
	font-weight: 800; font-size: .85rem; color: inherit; justify-self: start; padding-left: 8px;
}
.dash-float-nav:hover .dash-text { opacity: 1; transform: translateX(0); }

		.btn-ghost, .btn-danger, .btn-primary {
			font-weight: 700;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(37,150,190,.08);
			transition: box-shadow .18s, transform .18s, background .18s, color .18s;
			letter-spacing: 0.01em;
			border: none;
		}
		.btn-ghost {
			background: linear-gradient(90deg, #fff 80%, #e0f2fe 100%);
			color: #0078a6;
			border: 1px solid #e0f2fe;
		}
		.btn-primary {
			background: linear-gradient(90deg, #7cd4c4 80%, #b6e6f7 100%);
			color: #0b2c24;
		}
		.btn-danger {
			background: linear-gradient(90deg, #ef4444 80%, #fca5a5 100%);
			color: #fff;
		}
		.btn-ghost:hover, .btn-primary:hover, .btn-danger:hover {
			box-shadow: 0 4px 16px rgba(37,150,190,.14);
			transform: scale(1.06);
			filter: brightness(1.07);
		}
	</style>
</head>
<body>
<!-- Remove .admin-wrap and sidebar markup -->

<!-- Add floating right-side nav bar (copied from profile.php) -->
<nav class="dash-float-nav" id="dashNav">
	<div class="nav-brand">
		<a>	<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub">
		</a>
		<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub" style="pointer-events:none;">
	</div>
	<div class="nav-main">
		<a href="./admin.php" class="active" aria-current="page" aria-label="Dashboard">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z"/>
			</svg>
			<span class="dash-text">Dashboard</span>
		</a>
		<a href="./post-approvals.php" aria-label="Post Approvals">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/>
			</svg>
			<span class="dash-text">Post Approvals</span>
		</a>
		<a href="./manage-users.php" aria-label="Manage Users">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/>
			</svg>
			<span class="dash-text">Manage Users</span>
		</a>
	</div>
	<div class="nav-settings">
		<a href="./profile.php?logout=1" aria-label="Log out">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M15 3h4v4M14 10l5-5M9 7H7a4 4 0 0 0-4 4v5a4 4 0 0 0 4 4h5a4 4 0 0 0 4-4v-2"/>
			</svg>
			<span class="dash-text">Log out</span>
		</a>
	</div>
</nav>

<!-- Main content (dashboard, stats, etc.) -->
<main class="main">
	<div class="container" style="max-width:1200px; margin-top:-18px; gap:0;">
		<!-- Total Users Monitoring Section -->
		<p class="section-title" style="margin-left:8px; font-size:2.1rem; font-weight:900; color:#2596be; letter-spacing:0.01em; margin-top:0; margin-bottom:0;">
			Total Users Monitoring
		</p>
		<div class="card" style="margin-bottom:18px; padding:38px 32px 32px 32px;">
			<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0;">
				<div style="display:flex; flex-direction:column; align-items:flex-start; justify-content:center;">
					<h4 style="margin-bottom:8px; text-align:left;">Total Users</h4>
					<div class="metric" style="margin-bottom:4px;">0</div>
					<div class="delta">+0 this month</div>
				</div>
				<div style="display:flex; flex-direction:column; align-items:flex-start; justify-content:center;">
					<h4 style="margin-bottom:8px; text-align:left;">Active Users</h4>
					<div class="metric" style="margin-bottom:4px;">0</div>
					<div class="delta">0 online</div>
				</div>
				<div style="display:flex; flex-direction:column; align-items:flex-start; justify-content:center;">
					<h4 style="margin-bottom:8px; text-align:left;">Pending Verification</h4>
					<div class="metric" style="margin-bottom:4px;">0</div>
					<div class="delta">—</div>
				</div>
				<div style="display:flex; flex-direction:column; align-items:flex-start; justify-content:center;">
					<h4 style="margin-bottom:8px; text-align:left;">Suspended</h4>
					<div class="metric" style="margin-bottom:4px;">0</div>
					<div class="delta">—</div>
				</div>
			</div>
		</div>
	</div>
</main>

<!-- Move mask OUTSIDE the grid so it doesn't sit in the sidebar column -->
<div class="aside-mask" id="asideMask"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const aside = document.getElementById('aside');
const mask = document.getElementById('asideMask');
const menuBtn = document.getElementById('menuBtn');

menuBtn?.addEventListener('click', ()=>{
	aside.classList.toggle('open');
	mask.classList.toggle('show');
	document.body.classList.toggle('aside-open');
});
mask?.addEventListener('click', ()=>{
	aside.classList.remove('open');
	mask.classList.remove('show');
	document.body.classList.remove('aside-open');
});

// Lead Sources doughnut (all zeros)
const trafCtx = document.getElementById('trafChart');
if (trafCtx){
	new Chart(trafCtx, {
		type:'doughnut',
		data:{
			labels:['Search','Referrals','Direct'],
			datasets:[{ data:[0,0,0], backgroundColor:['#8b5cf6','#f472b6','#fde68a'], borderWidth:0 }]
		},
		options:{ plugins:{legend:{display:false}}, cutout:'62%' }
	});
}
</script>
</body>
</html>
