<?php
session_start();
$__logout = isset($_GET['logout']);
if ($__logout) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ./login.php');
    exit;
}
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'Guest');
$mobile = isset($_SESSION['mobile']) ? $_SESSION['mobile'] : '';
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Profile • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
	<style>
		/* centered floating bottom navigation (match home-services.php) */
		.dash-bottom-nav {
			position: fixed;
			left: 50%;
			right: auto;           /* ensure true centering */
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

		/* Enhancements: profile header and menu visuals */
		.prof-container { max-width: 480px; }

		.prof-hero {
			border-radius: 16px;
			box-shadow: 0 10px 28px rgba(2,6,23,.18);
			background: #0078a6;
			color: #fff;
		}
		.prof-name, .prof-meta { color: #fff !important; }
		.prof-avatar {
			box-shadow: inset 0 0 0 2px rgba(255,255,255,.85), 0 8px 18px rgba(2,6,23,.14);
		}
		.prof-edit {
			display: inline-flex; align-items: center; gap: 6px;
			background: #fff; color: #0078a6; padding: 6px 12px; border-radius: 999px;
			text-decoration: none; font-weight: 800;
			transition: filter .15s ease, transform .15s ease, box-shadow .15s ease;
			box-shadow: 0 8px 20px rgba(255,255,255,.2);
		}
		.prof-edit:hover { filter: brightness(1.05); transform: translateY(-1px); box-shadow: 0 12px 28px rgba(255,255,255,.3); }

		.prof-menu {
			display: grid;
			gap: 10px;
			padding: 12px;
			border-radius: 16px;
			background: #0078a6;
			box-shadow: 0 10px 28px rgba(0,120,166,.24);
		}
		.prof-sep { display: none; } /* use spacing instead of separators */

		.prof-item {
			display: flex; align-items: center; justify-content: space-between; gap: 12px;
			padding: 14px 16px; border-radius: 14px;
			background: rgba(255,255,255,.15); color: #fff;
			border: 2px solid rgba(255,255,255,.5);
			box-shadow: 0 4px 12px rgba(0,0,0,.15);
			text-decoration: none;
			transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
		}
		.prof-item:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 20px rgba(0,0,0,.2);
			background: #0078a6;
			color: #fff;
		}
		.prof-item:hover .prof-ico,
		.prof-item:hover .prof-chev { color: #fff; }
		.prof-item:active { transform: translateY(0); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
		.prof-item .prof-ico { color: #fff; opacity: .95; transition: color .15s ease; }
		.prof-item span { font-weight: 800; }
		.prof-chev { width: 18px; height: 18px; color: #fff; opacity: .9; flex: 0 0 18px; transition: color .15s ease; }

		/* Entrance animation for the profile container */
		@media (prefers-reduced-motion: no-preference) {
			.prof-container { animation: profIn .45s ease both; }
			@keyframes profIn { from { opacity: .0; transform: translateY(8px); } to { opacity:1; transform:none; } }
		}

		/* Stronger focus-visible rings on actionable elements */
		.prof-edit:focus-visible,
		.prof-item:focus-visible {
			outline: 3px solid rgba(14,116,162,.28);
			outline-offset: 3px;
			border-radius: 14px;
		}

		/* Blue bottom border on topbar */
		.dash-topbar { border-bottom: 3px solid #0078a6; }

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
		.dash-topbar {
			position: relative;
			z-index: 1;
		}
		.profile-bg {
			position: relative;
			z-index: 1;
			background: transparent !important;
		}
		.prof-container {
			position: relative;
			z-index: 1;
		}

		/* page override: white background */
		body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

		/* Tabs + cards (neutral, site-consistent) */
		.prof-tabs { margin-top: 14px; }
		.tabbar { display:flex; gap:8px; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:8px; }
		.tab-link {
			appearance:none; border:2px solid #e2e8f0; background:#fff; color:#0f172a; cursor:pointer;
			padding:8px 12px; border-radius:999px; font-weight:800; font-size:.92rem;
			transition: background .15s ease, color .15s ease, border-color .15s ease, transform .15s ease;
		}
		.tab-link:hover { background:#f8fafc; transform: translateY(-1px); }
		.tab-link.active { background:#0078a6; color:#fff; border-color:#0078a6; }

		.tab-panels { margin-top:12px; }
		.tab-panel[hidden]{ display:none; }

		.card {
			background:#fff; color:#0f172a; border:2px solid #e2e8f0;
			border-radius:12px; padding:14px; margin-bottom:12px;
			transition: box-shadow .15s ease, border-color .15s ease;
		}
		.card:hover { border-color:#0078a6; box-shadow:0 8px 20px rgba(0,0,0,.06); }
		.card h4 { margin:0 0 6px; }
		.card .muted { color:#64748b; }

		/* Wallet summary */
		.wallet { display:grid; gap:10px; }
		.wallet-row { display:flex; align-items:baseline; gap:10px; }
		.wallet-cur { color:#64748b; font-weight:700; }
		.wallet-amt { font-size:2rem; font-weight:900; color:#0f172a; line-height:1; }
		.wallet-actions { display:flex; gap:8px; flex-wrap:wrap; }
		.btn-chip {
			appearance:none; border:2px solid #e2e8f0; background:#fff; color:#0f172a; font-weight:800;
			padding:8px 12px; border-radius:10px; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px;
			transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease;
		}
		.btn-chip:hover { transform: translateY(-1px); box-shadow:0 6px 16px rgba(0,0,0,.08); border-color:#0078a6; background:#f8fafc; }
		.wallet-note { display:flex; align-items:center; gap:6px; color:#94a3b8; font-size:.9rem; }

		/* Transactions list */
		.txn-list { display:grid; gap:8px; }
		.txn-item {
			display:flex; align-items:center; justify-content:space-between; gap:10px;
			background:#fff; border:2px solid #e2e8f0; border-radius:10px; padding:10px 12px;
		}
		.txn-left { display:grid; gap:2px; }
		.txn-title { font-weight:800; color:#0f172a; }
		.txn-sub { color:#94a3b8; font-size:.9rem; }
		.txn-amt { font-weight:900; color:#0f172a; }

		/* Small helpers */
		.sep { height:1px; background:#e2e8f0; margin:8px 0; }

		/* Quick actions in topbar */
		.top-actions {
			position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
			display: flex; gap: 12px; align-items: center;
		}
		.icon-btn {
			position: relative; width: 34px; height: 34px; border-radius: 999px;
			display: grid; place-items: center; text-decoration: none;
			border: 2px solid #e2e8f0; background: #fff; color: #0f172a;
			transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
		}
		.icon-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.08); border-color:#0078a6; }
		.icon-btn svg { width: 18px; height: 18px; }
		.icon-btn .badge {
			position: absolute; top: -6px; right: -4px; min-width: 16px;
			height: 16px; padding: 0 4px; border-radius: 999px;
			background: #ef4444; color: #fff; font-size: 10px; line-height: 16px; font-weight: 900;
		}
	</style>
</head>
<body class="theme-profile-bg">
	<!-- Background Logo -->
	<div class="bg-logo">
		<img src="../assets/images/job_logo.png" alt="" />
	</div>

	<div class="dash-topbar center">
		<div class="dash-brand"><img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" /></div>
		<div class="top-actions" role="toolbar" aria-label="Quick actions">
			<a href="javascript:void(0)" class="icon-btn" aria-label="Notifications">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
				</svg>
			</a>
			<a href="./settings.php" class="icon-btn" aria-label="Settings">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527c.45-.322 1.07-.26 1.45.12l.773.774c.38.38.442 1 .12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.322.45.26 1.07-.12 1.45l-.774.773c-.38.38-1 .442-1.45.12l-.737-.527c-.35-.25-.806-.272-1.204-.107-.397.165-.71.505-.78.93l-.15.893c-.09.542-.56.94-1.109.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.893c-.071-.425-.384-.765-.781-.93-.398-.165-.854-.143-1.204.107l-.738.527c-.45.322-1.07.26-1.45-.12l-.773-.774c-.38-.38-.442-1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15C3.4 13.02 3 12.55 3 12V10.906c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.764-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.806.272 1.204.107.397-.165.71-.505.78-.93l.149-.894z"/>
				<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
			</svg>
			</a>
		</div>
	</div>

	<div class="profile-bg">
		<div class="prof-container">
			<!-- Profile card -->
			<section class="prof-hero" aria-label="Account">
				<div class="prof-avatar" aria-hidden="true"><?php echo htmlspecialchars($avatar); ?></div>
				<div>
					<p class="prof-name"><?php echo htmlspecialchars($display); ?></p>
					<?php if ($mobile): ?><p class="prof-meta"><?php echo htmlspecialchars($mobile); ?></p><?php endif; ?>
					<a class="prof-edit" href="./edit-profile.php">Edit Profile</a>
				</div>
			</section>

			<!-- Tabs copied from the photo's structure (not its design) -->
			<section class="prof-tabs" aria-label="Profile sections">
				<div class="tabbar" role="tablist" aria-label="Profile tabs">
					<button type="button" class="tab-link active" data-tab="earnings" role="tab" aria-selected="true" aria-controls="tab-earnings">Earnings</button>
					<button type="button" class="tab-link" data-tab="reviews" role="tab" aria-selected="false" aria-controls="tab-reviews">Reviews</button>
					<button type="button" class="tab-link" data-tab="about" role="tab" aria-selected="false" aria-controls="tab-about">About me</button>
				</div>

				<div class="tab-panels">
					<!-- Earnings -->
					<div id="tab-earnings" class="tab-panel" role="tabpanel">
						<article class="card" aria-labelledby="prefer-hero-title">
							<h4 id="prefer-hero-title">Become a preferred hero</h4>
							<p class="muted">Unlock more quests and higher earnings by getting the preferred hero badge.</p>
							<div class="wallet-actions">
								<a class="btn-chip" href="">Check eligibility</a>
							</div>
						</article>

						<article class="card wallet" aria-labelledby="wallet-title">
							<h4 id="wallet-title">Wallet</h4>
							<div class="wallet-row">
								<span class="wallet-cur">PHP</span>
								<strong class="wallet-amt">0.00</strong>
							</div>
							<div class="wallet-actions">
								<a class="btn-chip" href="" aria-label="Insights">
									<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 13l3 3 7-7"/></svg>
									Insights
								</a>
								<a class="btn-chip" href="./manage-payment.php" aria-label="Withdraw">
									<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v14"/><path d="M5 10l7 7 7-7"/></svg>
									Withdraw
								</a>
							</div>
							<div class="sep"></div>
							<div class="wallet-note">
								<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
								Your wallet is encrypted and secure.
							</div>
						</article>

						<article class="card" aria-labelledby="txn-title">
							<h4 id="txn-title">Transactions</h4>
							<div class="txn-list">
								<!-- Empty state -->
								<div class="txn-item" aria-label="No transactions yet">
									<div class="txn-left">
										<div class="txn-title">No transactions yet</div>
										<div class="txn-sub">When you receive payments, they will appear here.</div>
									</div>
									<div class="txn-amt">—</div>
								</div>
							</div>
						</article>
					</div>

					<!-- Reviews -->
					<div id="tab-reviews" class="tab-panel" role="tabpanel" hidden>
						<article class="card" aria-labelledby="reviews-title">
							<h4 id="reviews-title">Reviews</h4>
							<p class="muted">You don’t have any reviews yet.</p>
						</article>
					</div>

					<!-- About me -->
					<div id="tab-about" class="tab-panel" role="tabpanel" hidden>
						<article class="card" aria-labelledby="aboutme-title">
							<h4 id="aboutme-title">About me</h4>
							<p class="muted">Add details about yourself in Edit Profile to help clients know you better.</p>
							<div class="wallet-actions">
								<a class="btn-chip" href="./edit-profile.php">Edit Profile</a>
							</div>
						</article>
					</div>
				</div>
			</section>
		</div>
	</div>

	<!-- Floating bottom navigation -->
	<nav class="dash-bottom-nav">
		<a href="./home-gawain.php" aria-label="Browse">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
			</svg>
			<span>Browse</span>
		</a>
		<a href="./post.php" aria-label="Post">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
			<span>Post</span>
		</a>
		<a href="./my-gawain.php" aria-label="My Gawain">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
			<span>My Gawain</span>
		</a>
		<a href="./chats.php" aria-label="Chats">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			<span>Chats</span>
		</a>
		<a href="./profile.php" class="active" aria-label="Profile">
			<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
			<span>Profile</span>
		</a>
	</nav>

	<script>
	// Tabs behavior
	(function(){
		const links = document.querySelectorAll('.tab-link');
		const panels = {
			earnings: document.getElementById('tab-earnings'),
			reviews: document.getElementById('tab-reviews'),
			about: document.getElementById('tab-about')
		};
		links.forEach(btn=>{
			btn.addEventListener('click', ()=>{
				const tab = btn.dataset.tab;
				links.forEach(b=>{ b.classList.toggle('active', b===btn); b.setAttribute('aria-selected', b===btn ? 'true':'false'); });
				Object.keys(panels).forEach(k=> panels[k].hidden = (k!==tab));
			});
		});
	})();
	</script>
</body>
</html>
