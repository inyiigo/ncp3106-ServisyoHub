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
	<title>Home • Gawain • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>

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
			<!-- NEW: search bar below hero -->
			<section class="svc-search" aria-label="Search gawain">
				<div class="svc-search-box">
					<svg class="svc-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
					</svg>
					<input class="svc-search-input" type="search" name="svc-search" placeholder="Search gawain (e.g., cleaning, plumbing)" aria-label="Search gawain">
				</div>
			</section>

			<!-- Categories carousel: full list + arrows -->
			<div class="svc-cats-wrap" aria-label="Categories carousel">
				<button type="button" class="cat-nav-btn prev" id="catPrev" aria-label="Previous categories">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
				</button>
				<nav id="svcCats" class="svc-cats" aria-label="Categories">
					<button type="button" class="svc-cat active" data-cat="All">All</button>
					<button type="button" class="svc-cat" data-cat="Business & admin">Business &amp; admin</button>
					<button type="button" class="svc-cat" data-cat="Care services">Care services</button>
					<button type="button" class="svc-cat" data-cat="Creative">Creative</button>
					<button type="button" class="svc-cat" data-cat="Household">Household</button>
					<button type="button" class="svc-cat" data-cat="Part-time">Part-time</button>
					<button type="button" class="svc-cat" data-cat="Research">Research</button>
					<button type="button" class="svc-cat" data-cat="Social media">Social media</button>
					<button type="button" class="svc-cat" data-cat="Talents">Talents</button>
					<button type="button" class="svc-cat" data-cat="Teach me">Teach me</button>
					<button type="button" class="svc-cat" data-cat="Tech & IT">Tech &amp; IT</button>
					<button type="button" class="svc-cat" data-cat="Others">Others</button>
				</nav>
				<button type="button" class="cat-nav-btn next" id="catNext" aria-label="Next categories">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
				</button>
			</div>

			<div class="results-bar">
				<div class="results-left">
					<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
					<span><?php echo (int)count($jobs); ?> results</span>
				</div>
				<div class="results-right">
					<a href="./filter.php" class="toggle-btn" aria-label="Filter">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M3 5h18l-7 8v6l-4 2v-8L3 5z"/>
						</svg>
					</a>
				</div>
			</div>

			<section class="svc-list" aria-label="Nearby posts">
				<?php if (!$dbAvailable): ?>
					<div class="form-card glass-card card-white">
						No posts available right now. <?php echo e($lastConnError); ?>
					</div>
				<?php elseif (empty($jobs)): ?>
					<div class="form-card glass-card card-white">
						No posts yet. Be the first to post using the + button.
					</div>
				<?php else: ?>
					<?php foreach ($jobs as $j): ?>
						<article class="svc-card">
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
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</section>

			<!-- Recent Posts feed (before gawain) -->
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

		</main>

		<aside class="dash-aside">
			<nav class="dash-nav" aria-label="Main navigation">
				<a href="./home-gawain.php" class="active" aria-label="Browse">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
					</svg>
					<span>Browse</span>
				</a>
				<a href="./my-gawain.php" aria-label="My Gawain">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
					<span>My Gawain</span>
				</a>
				<a href="./chats.php" aria-label="Chats">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 15a4 4 0 0 1-4 4H8l-4 4v-4H5a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4z"/>
					</svg>
					<span>Chats</span>
				</a>
				<a href="./clients-profile.php" aria-label="Profile">
					<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
					<span>Profile</span>
				</a>
			</nav>
		</aside>
	</div>

	<!-- Floating bottom navigation (Post button removed) -->
	<nav class="dash-bottom-nav">
		<a href="./home-gawain.php" class="active" aria-label="Browse">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
			</svg>
			<span>Browse</span>
		</a>
		<a href="./clients-post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span>Post</span>
		</a>
		<a href="./my-gawain.php" aria-label="My Gawain">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Gawain</span>
		</a>
		<a href="./chats.php" aria-label="Chats">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M21 15a4 4 0 0 1-4 4H8l-4 4v-4H5a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4z"/>
			</svg>
			<span>Chats</span>
		</a>
		<a href="./clients-profile.php" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>

	<script>
	// Categories carousel + search + filter logic
	(function(){
		const row = document.getElementById('svcCats');
		const prev = document.getElementById('catPrev');
		const next = document.getElementById('catNext');
		const search = document.querySelector('.svc-search-input');
		const feedCards = document.querySelectorAll('.feed-card');
		const svcCards = document.querySelectorAll('.svc-card');
		
		if (!row || !prev || !next) return;

		let activeCategory = 'All';

		function updateArrows(){
			const max = row.scrollWidth - row.clientWidth - 1;
			prev.disabled = row.scrollLeft <= 0;
			next.disabled = row.scrollLeft >= max;
		}
		
		function scrollByStep(dir){
			const step = Math.max(160, Math.floor(row.clientWidth * 0.9));
			row.scrollBy({ left: dir * step, behavior: 'smooth' });
			setTimeout(updateArrows, 250);
		}
		
		prev.addEventListener('click', ()=> scrollByStep(-1));
		next.addEventListener('click', ()=> scrollByStep(1));
		row.addEventListener('scroll', updateArrows, { passive: true });
		window.addEventListener('resize', updateArrows);

		// Category click: activate, fill search, filter posts
		row.addEventListener('click', (e)=>{
			const btn = e.target.closest('.svc-cat');
			if (!btn) return;
			
			// Update active state
			row.querySelectorAll('.svc-cat').forEach(b=>b.classList.remove('active'));
			btn.classList.add('active');
			
			activeCategory = btn.getAttribute('data-cat') || 'All';
			
			// Fill/clear search box
			if (search) {
				if (activeCategory === 'All') {
					search.value = '';
				} else {
					search.value = activeCategory;
				}
			}
			
			// Filter Recent Posts by category
			filterPosts(activeCategory);
		});

		function filterPosts(category){
			// Filter feed cards (Recent Posts)
			feedCards.forEach(card => {
				const cardCat = card.querySelector('.fc-cat')?.textContent.trim() || '';
				if (category === 'All' || cardCat === category) {
					card.style.display = '';
				} else {
					card.style.display = 'none';
				}
			});

			// Filter service list cards
			svcCards.forEach(card => {
				const cardTitle = card.querySelector('.svc-title')?.textContent.trim().toLowerCase() || '';
				if (category === 'All' || cardTitle.includes(category.toLowerCase())) {
					card.style.display = '';
				} else {
					card.style.display = 'none';
				}
			});
		}

		// Search box typing also filters
		if (search) {
			search.addEventListener('input', ()=>{
				const term = search.value.trim().toLowerCase();
				
				// If search matches a category, activate it
				const matchBtn = Array.from(row.querySelectorAll('.svc-cat')).find(b => {
					const cat = (b.getAttribute('data-cat') || '').toLowerCase();
					return cat === term;
				});
				
				if (matchBtn) {
					row.querySelectorAll('.svc-cat').forEach(b=>b.classList.remove('active'));
					matchBtn.classList.add('active');
					activeCategory = matchBtn.getAttribute('data-cat') || 'All';
					filterPosts(activeCategory);
				} else if (term === '') {
					// Empty search = All
					const allBtn = row.querySelector('.svc-cat[data-cat="All"]');
					if (allBtn) {
						row.querySelectorAll('.svc-cat').forEach(b=>b.classList.remove('active'));
						allBtn.classList.add('active');
						activeCategory = 'All';
						filterPosts('All');
					}
				} else {
					// Free-text search: show all that match
					feedCards.forEach(card => {
						const text = card.textContent.toLowerCase();
						card.style.display = text.includes(term) ? '' : 'none';
					});
					svcCards.forEach(card => {
						const text = card.textContent.toLowerCase();
						card.style.display = text.includes(term) ? '' : 'none';
					});
				}
			});
		}

		updateArrows();
	})();
	</script>
</body>
</html>
