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
		body{margin:0;background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial}
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
		.card{background:var(--card);border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:16px}
		.card h4{margin:0 0 6px;font-size:.95rem;color:var(--muted)}
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
		.section-title { margin: 4px 4px 2px; color: var(--muted); font-weight: 800; font-size: .95rem; }
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
	</style>
</head>
<body>

<div class="admin-wrap">
	<aside class="aside" id="aside">
		<!-- Sidebar top bar with logo -->
		<div class="side-topbar">
			<img src="../assets/images/bluefont.png" alt="ServisyoHub" />
		</div>

		<nav class="nav">
			<a class="active" href="./admin.php">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z"/></svg>
				Dashboard
			</a>
		</nav>
	</aside>

	<main class="main">
		<div class="container">
			<p class="section-title">Jobs Overview</p>
			<!-- Jobs overview -->
			<section class="stats" aria-label="Jobs overview">
				<div class="card">
					<h4>Applications</h4>
					<div class="metric">0</div>
					<div class="delta">0% vs last month</div>
				</div>
				<div class="card">
					<h4>Open Jobs</h4>
					<div class="metric">0</div>
					<div class="delta">—</div>
				</div>
				<div class="card">
					<h4>Active Jobs</h4>
					<div class="metric">0</div>
					<div class="delta">Today</div>
				</div>
				<div class="card">
					<h4>Response Rate</h4>
					<div class="metric">0%</div>
					<div class="delta">Monthly</div>
				</div>
			</section>

			<p class="section-title">Clients Overview</p>
			<!-- Clients overview -->
			<section class="stats" aria-label="Clients overview">
				<div class="card">
					<h4>New Clients</h4>
					<div class="metric">0</div>
					<div class="delta">0% vs last month</div>
				</div>
				<div class="card">
					<h4>Active Bookings</h4>
					<div class="metric">0</div>
					<div class="delta">Today</div>
				</div>
				<div class="card">
					<h4>Pending Requests</h4>
					<div class="metric">0</div>
					<div class="delta">Awaiting review</div>
				</div>
				<div class="card">
					<h4>Unread Messages</h4>
					<div class="metric">0</div>
					<div class="delta">Inbox</div>
				</div>
			</section>

			<!-- Replace separate charts/client-widgets with a 2-col layout -->
			<section class="layout-two" aria-label="Clients and insights">
				<div>
					<p class="section-title">Clients</p>
					<!-- Client widgets -->
					<section class="client-widgets" aria-label="Clients">
						<div class="card">
							<h4>Pending Client Requests</h4>
							<ul class="c-list">
								<li>House cleaning • Brgy. 442 • Today 2:00 PM</li>
								<li>Plumbing check • Sampaloc • Tomorrow 10:00 AM</li>
								<li>Errand runner • Quiapo • Fri 9:00 AM</li>
							</ul>
						</div>
						<div class="card">
							<h4>Active Bookings</h4>
							<table class="table compact" aria-label="Active bookings">
								<thead><tr><th>Ref</th><th>Client</th><th>Service</th><th>When</th><th>Status</th></tr></thead>
								<tbody>
									<tr><td>BK-1042</td><td>A. Santos</td><td>Cleaning</td><td>Today 3:00 PM</td><td><span class="tag active">Active</span></td></tr>
									<tr><td>BK-1041</td><td>J. Cruz</td><td>Plumbing</td><td>Tomorrow 9:30 AM</td><td><span class="tag pending">Pending</span></td></tr>
									<tr><td>BK-1039</td><td>M. Reyes</td><td>Errands</td><td>Fri 8:00 AM</td><td><span class="tag pending">Pending</span></td></tr>
								</tbody>
							</table>
						</div>
					</section>
				</div>
				<div>
					<p class="section-title">Insights</p>
					<!-- Charts -->
					<section class="charts" aria-label="Charts">
						<div class="card">
							<h4>Lead Sources</h4>
							<canvas id="trafChart" height="110"></canvas>
							<div style="display:flex;gap:10px;margin-top:10px;color:var(--muted);font-weight:700">
								<span>0% Search</span><span>•</span><span>0% Referrals</span><span>•</span><span>0% Direct</span>
							</div>
						</div>
					</section>
				</div>
			</section>

			<!-- Details -->
			<section class="lower" aria-label="Details">
				<div class="card">
					<h4>Recent Feedback</h4>
					<div class="feedback">
						<div class="item"><span class="dot" aria-hidden="true"></span><div>“Great service, very responsive.” • Cleaning • K. P.</div></div>
						<div class="item"><span class="dot" aria-hidden="true"></span><div>“Arrived on time and finished quickly.” • Plumbing • R. T.</div></div>
						<div class="item"><span class="dot" aria-hidden="true"></span><div>“Helpful and professional.” • Errands • L. G.</div></div>
					</div>
				</div>

				<div class="card">
					<h4>Work Queue</h4>
					<ul class="list">
						<li>Verify client details • Open</li>
						<li>Confirm schedule • In progress</li>
						<li>Review application • On hold</li>
					</ul>
				</div>
			</section>
		</div>
	</main>
</div>

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
