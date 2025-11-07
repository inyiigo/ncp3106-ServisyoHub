<?php
session_start();
// ...existing code for DB connection if needed...

// Dummy data for pending client posts (replace with DB query)
$pendingPosts = [
	['id'=>1, 'client'=>'Juan Dela Cruz', 'title'=>'Need help with moving', 'date'=>'2024-06-05', 'status'=>'Pending'],
	['id'=>2, 'client'=>'Maria Santos', 'title'=>'Booth staff for pop-up', 'date'=>'2024-06-06', 'status'=>'Pending'],
	// ...more rows...
];
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Post Approvals • ServisyoHub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">
	<style>
		/* ...copy styles from admin.php... */
		body { background: #ffffff !important; }
		.container { max-width: 980px; margin: 24px auto; padding: 18px; position: relative; z-index: 1; }
		.section-title { margin: 4px 4px 2px; color: #64748b; font-weight: 800; font-size: .95rem; }
		.card { 
			background: linear-gradient(135deg, rgba(255,255,255,0.97) 70%, rgba(37,150,190,0.08) 100%);
			color: #2596be;
			border-radius: 28px;
			padding: 36px 32px 24px 32px;
			box-shadow: 0 16px 40px rgba(37,150,190,.22), 0 2px 12px rgba(0,0,0,.10);
			border: 2px solid #b6e6f7;
			height: 480px;
			overflow: hidden;
			display: flex;
			flex-direction: column;
			backdrop-filter: blur(10px) saturate(1.18);
			transition: box-shadow .22s, transform .22s, border-color .22s;
			position: relative;
		}
		.card::before {
			content: "";
			position: absolute;
			inset: 0;
			border-radius: 28px;
			pointer-events: none;
			background: linear-gradient(120deg, rgba(124,212,196,0.08) 0%, rgba(37,150,190,0.10) 100%);
			z-index: 0;
		}
		.card > * { position: relative; z-index: 1; }
		.card:hover {
			box-shadow: 0 24px 64px rgba(37,150,190,.28), 0 6px 24px rgba(0,0,0,.14);
			transform: translateY(-3px) scale(1.015);
			border-color: #7cd4c4;
		}
		.card h4 { margin:0 0 10px; font-size:1.12rem; color:#2596be; letter-spacing:0.03em; font-weight: 800; } /* match text color */
		.table { width:100%; border-collapse:collapse; margin-top:12px; }
		.table thead { position: sticky; top: 0; background: #2596be; z-index: 1; }
		.table th, .table td { 
			padding:12px; 
			border-bottom:1px solid rgba(255,255,255,.2); 
			text-align:left; 
			font-size:.95rem; 
		}
		/* Make ID, Client, Title, Date columns black */
		.table td.id, 
		.table td.client, 
		.table td.title, 
		.table td.date {
			color: #222 !important;
			font-weight: 500;
		}
		.table th { 
			font-weight:800; 
			color: #0b2c24 !important; /* header text color */
		}
		/* Status pill for Pending */
		.status-pending {
			background: #fef3c7;
			color: #92400e;
			font-weight: 800;
			border-radius: 999px;
			padding: 4px 14px;
			display: inline-block;
			font-size: .95rem;
			letter-spacing: 0.02em;
			border: none;
		}
		.card .table tbody {
			display: block;
			max-height: 370px; /* scrollable area inside card */
			overflow-y: auto;
			width: 100%;
		}
		.card .table thead, .card .table tfoot {
			display: table;
			width: 100%;
			table-layout: fixed;
		}
		.card .table tr {
			display: table;
			width: 100%;
			table-layout: fixed;
		}
		.btn-ghost { background: #fff; border: 1px solid rgba(255,255,255,.3); color: #0078a6; padding:8px 10px; border-radius:10px; cursor:pointer; text-decoration:none; }
		.btn-danger { background:#ef4444; color:#fff; padding:8px 10px; border-radius:10px; border:none; cursor:pointer; }
		.btn-primary { 
			background: #7cd4c4; /* updated color */
			color: #0b2c24;      /* updated text color */
			padding:8px 10px; 
			border-radius:10px; 
			border:none; 
			cursor:pointer; 
		}
		/* ...nav bar styles from admin.php... */
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
		.dash-float-nav:hover { width: 200px; box-shadow: 0 12px 32px rgba(0,0,0,.32) !important; }
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
		.dash-float-nav a:hover:not(.active) { background: rgba(255,255,255,.15) !important; transform: scale(1.05); }
		.dash-float-nav a:focus-visible { box-shadow: 0 0 0 3px rgba(255,255,255,.3); }
		.dash-float-nav a.active { background: rgba(255,255,255,.22) !important; color: #fff !important; box-shadow: 0 6px 18px rgba(0,0,0,.22) !important; }
		.dash-float-nav a.active::after { display: none !important; }
		.dash-float-nav .dash-icon { width: 18px; height: 18px; justify-self: center; }
		.dash-float-nav .dash-text { opacity: 0; transform: translateX(-10px); transition: opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s; font-weight: 800; font-size: .85rem; color: inherit; justify-self: start; padding-left: 8px; }
		.dash-float-nav:hover .dash-text { opacity: 1; transform: translateX(0); }
	</style>
</head>
<body>
<nav class="dash-float-nav" id="dashNav">
	<div class="nav-brand">
		<a>
			<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub">
			<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub">
		</a>
	</div>
	<div class="nav-main">
		<a href="./admin.php" aria-label="Dashboard">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z"/>
			</svg>
			<span class="dash-text">Dashboard</span>
		</a>
		<a href="./approvals-clients.php" class="active" aria-current="page" aria-label="Post Approvals">
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

<main class="container">
	<!-- Title row with left alignment -->
	<div style="margin-bottom:8px;">
		<h2 class="section-title" style="margin:0; text-align:left;">Pending Client Posts</h2>
	</div>
	<!-- Blue card box copied from admin.php -->
	<section class="card" aria-label="Pending Client Posts" style="margin-bottom:24px;">
		<h4 style="margin:0 0 10px; text-align:left;">Approve or Reject Client Posts</h4>
		<table class="table" aria-label="Pending Client Posts">
			<thead>
				<tr>
					<th>ID</th>
					<th>Client</th>
					<th>Title</th>
					<th>Date</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($pendingPosts as $post): ?>
				<tr>
					<td class="id"><?php echo $post['id']; ?></td>
					<td class="client"><?php echo htmlspecialchars($post['client']); ?></td>
					<td class="title"><?php echo htmlspecialchars($post['title']); ?></td>
					<td class="date"><?php echo htmlspecialchars($post['date']); ?></td>
					<td>
						<span class="status-pending">Pending</span>
					</td>
					<td>
						<form method="post" style="display:inline">
							<input type="hidden" name="action" value="approve">
							<input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
							<button type="submit" class="btn-primary">Approve</button>
						</form>
						<form method="post" style="display:inline" onsubmit="return confirm('Reject this post?');">
							<input type="hidden" name="action" value="reject">
							<input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
							<button type="submit" class="btn-danger">Reject</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
</main>
</body>
</html>
