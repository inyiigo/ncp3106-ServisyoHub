<?php
session_start();
// read current values so form stays sticky
$sort     = $_GET['sort']     ?? 'recent';
$cat      = $_GET['cat']      ?? '';
$loc_type = $_GET['loc_type'] ?? 'all';
$min      = $_GET['min']      ?? '';
$max      = $_GET['max']      ?? '';
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Filter Options • ServisyoHub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">

    <style>
		/* page override: white background */
		body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

		/* Blue bottom border on topbar */
		.dash-topbar { border-bottom: 3px solid var(--blue); position: relative; z-index: 1; } /* was #0078a6 */

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
		.bg-logo img { width: 100%; height: auto; display: block; }

		/* Ensure main content is above background */
		.dash-shell { position: relative; z-index: 1; }

		/* Center the main content area */
		.dash-content { max-width: 1100px; margin: 0 auto; padding: 0 16px; position: relative; z-index: 1; }

		/* Floating bottom navigation (centered) */
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
			border: 3px solid var(--blue); /* was #0078a6 */
			background: transparent;
		}
		.dash-bottom-nav:hover {
			transform: translateX(-50%) scale(1);
			box-shadow: 0 12px 28px rgba(2,6,23,.12);
		}

		/* Filter card (white box) */
		.filter-card {
			background:#fff;
			border:2px solid #e2e8f0;
			border-radius:12px;
			padding:16px;
			box-shadow: 0 8px 20px rgba(0,0,0,.06);
			margin: 14px 0 80px;
		}
		.filter-card h2 { margin: 0 0 10px; }

		/* Minimal form styles (keep functionality) */
		fieldset { border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; margin: 0 0 12px; }
		legend { padding: 0 6px; font-weight: 800; font-size: 0.98rem; }
		label { font-weight: 700; display: block; margin: 0 0 6px; }
		.inline { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
		.row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
		@media (max-width:640px){ .row{ grid-template-columns:1fr; } }
		select, input[type="text"], input[type="number"] { width: 100%; padding: 10px; border:1px solid #e2e8f0; border-radius:10px; }
		.actions { display: flex; gap: 10px; margin-top: 12px; }
		button, .btn { appearance: none; border-radius: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; font-weight: 800; text-decoration: none; color: inherit; }
		.apply {
			background: var(--blue);
			color: #fff;
			border-color: var(--blue);
			transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease;
		}
		.apply:hover,
		.apply:focus-visible {
			transform: translateY(-1px);
			box-shadow: 0 6px 16px rgba(0,120,166,.24);
			background: #006a94;
			border-color: #006a94;
			outline: none;
		}
		.reset {
			background: #f1f5f9;
			transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease;
		}
		.reset:hover,
		.reset:focus-visible {
			transform: translateY(-1px);
			box-shadow: 0 6px 16px rgba(0,0,0,.08);
			background: #f8fafc;
			border-color: var(--blue);
			outline: none;
		}
		.help { color:#64748b; font-size:.9rem; margin: 6px 0 0; }

		/* NEW: exit row below top bar */
		.exit-row {
			display: flex;
			justify-content: flex-end;
			align-items: center;
			padding: 8px 12px 0;
		}
		/* Update exit button to be text-only, blue, and not fixed */
		.filter-exit{
			position: static;
			display: inline-block;
			margin: 0;
			padding: 2px 6px; /* larger hit area without a box */
			background: transparent;
			border: 0;
			color: var(--blue); /* was #0078a6 */
			text-decoration: none;
			font-weight: 400; /* not bold */
			font-size: 28px;  /* slightly bigger */
			letter-spacing: .02em;
			line-height: 1;
			cursor: pointer;
			-webkit-tap-highlight-color: transparent;
			transition: color .18s ease, transform .12s ease;
			transform-origin: center;
		}
		.filter-exit:hover{ color:#006a94; transform: scale(1.08); }
		.filter-exit:active { transform: scale(0.96); }
		.filter-exit:focus-visible { outline: 3px solid rgba(14,116,162,.28); outline-offset: 4px; border-radius: 6px; }

		/* Unbold everything by default */
		:root { --fw-normal: 400; --fw-bold: 800; }
		body, body *:not(svg):not(path) { font-weight: var(--fw-normal) !important; }

		/* Keep only the “Filter Options” title bold */
		#filterTitle, .filter-title { font-weight: var(--fw-bold) !important; }

		/* Remove top bar on this page */
		.dash-topbar, .top-bar { display: none !important; }
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="">
	</div>

	<div class="dash-shell">
		<main class="dash-content">
			<section class="filter-card" aria-label="Filters">
				<h2>Filter Options</h2>
				<p class="help">Choose how to filter results. Applied filters will be sent to the Gawain page.</p>

				<form method="get" action="./home-gawain.php">
					<fieldset>
						<legend>Sort Results</legend>
						<div class="inline" role="radiogroup" aria-label="Sort Results">
							<label><input type="radio" name="sort" value="recent" <?php echo $sort==='recent'?'checked':''; ?>> Recently Posted</label>
							<label><input type="radio" name="sort" value="price_asc" <?php echo $sort==='price_asc'?'checked':''; ?>> Price: Low to High</label>
							<label><input type="radio" name="sort" value="price_desc" <?php echo $sort==='price_desc'?'checked':''; ?>> Price: High to Low</label>
						</div>
					</fieldset>

					<fieldset>
						<legend>Category</legend>
						<label for="cat">Pick a category</label>
						<select id="cat" name="cat">
							<option value="" <?php echo $cat===''?'selected':''; ?>>All</option>
							<option value="Business & admin" <?php echo $cat==='Business & admin'?'selected':''; ?>>Business &amp; admin</option>
							<option value="Care services" <?php echo $cat==='Care services'?'selected':''; ?>>Care services</option>
							<option value="Creative" <?php echo $cat==='Creative'?'selected':''; ?>>Creative</option>
							<option value="Household" <?php echo $cat==='Household'?'selected':''; ?>>Household</option>
							<option value="Part-time" <?php echo $cat==='Part-time'?'selected':''; ?>>Part-time</option>
							<option value="Research" <?php echo $cat==='Research'?'selected':''; ?>>Research</option>
							<option value="Social media" <?php echo $cat==='Social media'?'selected':''; ?>>Social media</option>
							<option value="Talents" <?php echo $cat==='Talents'?'selected':''; ?>>Talents</option>
							<option value="Teach me" <?php echo $cat==='Teach me'?'selected':''; ?>>Teach me</option>
							<option value="Tech & IT" <?php echo $cat==='Tech & IT'?'selected':''; ?>>Tech &amp; IT</option>
							<option value="Others" <?php echo $cat==='Others'?'selected':''; ?>>Others</option>
						</select>
					</fieldset>

					<fieldset>
						<legend>Location Type</legend>
						<div class="inline" role="radiogroup" aria-label="Location Type">
							<label><input type="radio" name="loc_type" value="all" <?php echo $loc_type==='all'?'checked':''; ?>> All</label>
							<label><input type="radio" name="loc_type" value="in_person" <?php echo $loc_type==='in_person'?'checked':''; ?>> In-person</label>
							<label><input type="radio" name="loc_type" value="online" <?php echo $loc_type==='online'?'checked':''; ?>> Online</label>
						</div>
					</fieldset>

					<fieldset>
						<legend>Earning Range</legend>
						<div class="row">
							<div>
								<label for="min">Min (PHP)</label>
								<input id="min" type="number" name="min" inputmode="decimal" step="any" value="<?php echo htmlspecialchars($min); ?>">
							</div>
							<div>
								<label for="max">Max (PHP)</label>
								<input id="max" type="number" name="max" inputmode="decimal" step="any" value="<?php echo htmlspecialchars($max); ?>">
							</div>
						</div>
					</fieldset>

					<div class="actions" style="justify-content: flex-end;">
						<button class="apply" type="submit">Apply Filters</button>
						<a class="btn reset" href="./home-gawain.php" style="margin-left:0;">Cancel</a>
					</div>
				</form>
			</section>
		</main>
	</div>

	<script>
	// Ensure the Filter Options heading is bold even if markup lacks an id/class
	(function(){
		var cand = Array.from(document.querySelectorAll('h1,h2,h3,h4,h5,h6,.title,.page-title'))
			.find(el => (el.textContent || '').trim().toLowerCase() === 'filter options');
		if (cand) {
			cand.classList.add('filter-title');
			if (!cand.id) cand.id = 'filterTitle';
		}
	})();
	</script>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> 744f4df74bfac2cecea22afda6091adc2e353d2f
