<?php
session_start();

/* Pull stats from session with safe defaults */
function peso($v){ return 'PHP'.number_format((float)$v, 2); }

$totalEarnings      = (float)($_SESSION['total_earnings']       ?? 0);
$totalQuests        = (int)  ($_SESSION['total_quests']         ?? 0);

$monthEarnings      = (float)($_SESSION['month_earnings']       ?? 0);
$lastMonthEarnings  = (float)($_SESSION['last_month_earnings']  ?? 0);

$monthQuests        = (int)  ($_SESSION['month_quests']         ?? 0);
$lastMonthQuests    = (int)  ($_SESSION['last_month_quests']    ?? 0);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Insights • ServisyoHub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">
	<style>
		/* Copy core look from profile.php */
		body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }
		.dash-topbar { display: none !important; border: 0 !important; position: static !important; z-index: auto !important; }
		.bg-logo { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 25%; max-width: 350px; opacity: .15; z-index: 0; pointer-events: none; }
		.bg-logo img { width: 100%; height: auto; display: block; }

		/* Page container */
		.page-wrap { max-width: 960px; margin: 18px auto 32px; padding: 0 16px; position: relative; z-index: 1; }

		/* Headings and totals */
		.ins-title { margin: 0 0 8px; font-weight: 900; color: #0f172a; text-align: center; }
		.ins-totals { display: grid; gap: 8px; margin: 10px 0 14px; }
		.total-block { display: grid; gap: 6px; }
		.total-label { color: #64748b; font-weight: 700; }
		.total-amt { font-size: 2rem; font-weight: 900; color: #0f172a; line-height: 1; }
		.total-num { font-size: 1.6rem; font-weight: 900; color: #0f172a; line-height: 1; }
		.divider { height: 1px; background: #e2e8f0; margin: 10px 0 14px; }

		/* Section title */
		.sec-title { margin: 0 0 10px; font-weight: 800; color: #0f172a; }

		/* Cards (match profile white cards) */
		.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
		@media (max-width: 680px){ .grid { grid-template-columns: 1fr; } }

		.card {
			background: #fff; color: #0f172a; border: 2px solid #e2e8f0;
			border-radius: 12px; padding: 14px; transition: box-shadow .15s ease, border-color .15s ease;
		}
		.card:hover { border-color: #0078a6; box-shadow: 0 8px 20px rgba(0,0,0,.06); }
		.card-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
		.card-title { font-weight: 800; margin: 0 0 10px; }
		.card-val { font-size: 1.6rem; font-weight: 900; }
		.card-sub { color: #94a3b8; font-size: .9rem; }
		.card-chev { width: 18px; height: 18px; color: #94a3b8; flex: 0 0 18px; }

		/* Bottom back button (consistent with other pages) */
		.bottom-box { position: fixed; right: 20px; bottom: 20px; z-index: 999; background: transparent; border: none; padding: 0; box-shadow: none; }
		.back-box {
			display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 10px;
			background: #0078a6; color: #fff; text-decoration: none; font-weight: 700;
			border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
			transition: transform 160ms ease, box-shadow 160ms ease, background-color 200ms ease;
			box-shadow: 0 6px 18px rgba(0,120,166,.24);
		}
		.back-box:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 12px 28px rgba(0,120,166,.32); background: #006a94; border-color: color-mix(in srgb, #0078a6 60%, #0000); }
		@media (max-width:520px){ .bottom-box{ left:12px; right:12px; bottom:14px; display:flex; justify-content:center; } .back-box{ width:100%; justify-content:center; } }
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo"><img src="../assets/images/job_logo.png" alt=""></div>

	<main class="page-wrap" role="main" aria-labelledby="insights-title">
		<h1 id="insights-title" class="ins-title">Insights</h1>

		<section class="ins-totals" aria-label="Totals">
			<div class="total-block">
				<span class="total-label">Total Earnings</span>
				<div class="total-amt"><?php echo peso($totalEarnings); ?></div>
			</div>
			<div class="total-block">
				<span class="total-label">Total Quests Completed</span>
				<div class="total-num"><?php echo (int)$totalQuests; ?></div>
			</div>
		</section>

		<div class="divider" aria-hidden="true"></div>

		<section aria-label="My Stats This Month">
			<h2 class="sec-title">My Stats This Month</h2>

			<div class="grid">
				<!-- Earnings card -->
				<article class="card" aria-labelledby="c-earnings">
					<h3 id="c-earnings" class="card-title">Earnings</h3>
					<div class="card-row">
						<div>
							<div class="card-val"><?php echo peso($monthEarnings); ?></div>
							<div class="card-sub">Last Month: <?php echo peso($lastMonthEarnings); ?></div>
						</div>
						<svg class="card-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
					</div>
				</article>

				<!-- Quests card -->
				<article class="card" aria-labelledby="c-quests">
					<h3 id="c-quests" class="card-title">Quests Completed</h3>
					<div class="card-row">
						<div>
							<div class="card-val"><?php echo (int)$monthQuests; ?></div>
							<div class="card-sub">Last Month: <?php echo (int)$lastMonthQuests; ?></div>
						</div>
						<svg class="card-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
					</div>
				</article>
			</div>
		</section>
	</main>

	<div class="bottom-box" role="navigation" aria-label="Page actions">
		<a href="./profile.php" class="back-box" title="Back to profile">← Back to profile</a>
	</div>
</body>
</html>
