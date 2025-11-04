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

</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="">
	</div>

	<div class="dash-topbar center">
		<div class="dash-brand">
			<img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo">
		</div>
	</div>

	<!-- NEW: place the blue "x" below the top bar -->
	<div class="exit-row">
		<a href="./home-gawain.php" class="filter-exit" aria-label="Exit filters">x</a>
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
							<option value="Errands" <?php echo $cat==='Errands'?'selected':''; ?>>Errands</option>
							<option value="Part-time" <?php echo $cat==='Part-time'?'selected':''; ?>>Part-time</option>
							<option value="Household" <?php echo $cat==='Household'?'selected':''; ?>>Household</option>
							<option value="Creative" <?php echo $cat==='Creative'?'selected':''; ?>>Creative</option>
							<option value="Tech" <?php echo $cat==='Tech'?'selected':''; ?>>Tech</option>
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

					<div class="actions">
						<a class="btn reset" href="./filter.php">Reset</a>
						<button class="apply" type="submit">Apply Filters</button>
					</div>
				</form>
			</section>
		</main>
	</div>

	<!-- Removed floating bottom navigation -->
	<!-- <nav class="dash-bottom-nav"> ... </nav> -->
</body>
</html>
