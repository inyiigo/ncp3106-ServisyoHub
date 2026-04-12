<?php
session_start();

$display = $_SESSION['display_name'] ?? ($_SESSION['mobile'] ?? 'Guest');
$viewerId = (int)($_SESSION['user_id'] ?? 0);
$tab = (isset($_GET['tab']) && $_GET['tab'] === 'citizen') ? 'citizen' : 'kasangga';
$offerId = isset($_GET['offer']) ? (int)$_GET['offer'] : 0;
$jobId = isset($_GET['job']) ? (int)$_GET['job'] : 0;
$isLoggedIn = $viewerId > 0;

function e($value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Chats • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
	<script defer src="../assets/js/script.js"></script>
	<style>
		body.theme-profile-bg {
			background:
				radial-gradient(circle at top left, rgba(0,120,166,.08), transparent 30%),
				linear-gradient(180deg, #f7fbfd 0%, #ffffff 42%, #ffffff 100%) !important;
			background-attachment: initial !important;
		}
		.dash-topbar { display:none !important; }
		html, body { height:100%; overflow:hidden; }
		body { padding-top:0 !important; }

		.chat-page {
			max-width: 1320px;
			margin: 0 auto;
			padding: 20px;
			height: 100dvh;
			display: grid;
			grid-template-rows: auto 1fr;
			overflow: hidden;
			position: relative;
			z-index: 1;
		}
		.chat-hero { display:grid; gap:8px; margin: 10px 0 18px; }
		.chat-title { margin:0; font-size: clamp(2rem, 4vw, 2.65rem); font-weight:900; color:#0f172a; letter-spacing:-0.03em; }
		.chat-subtitle { margin:0; color:#64748b; font-size:.98rem; max-width:64ch; }

		.chat-tabs {
			display:flex; gap:12px; max-width:560px; margin:0 0 18px;
		}
		.chat-tabs .mq-tab { flex:1; text-align:center; text-decoration:none; }
		.chat-tabs .mq-indicator { display:none !important; }

		.chat-shell {
			display:grid;
			grid-template-columns:minmax(280px,360px) minmax(0,1fr);
			gap:18px;
			align-items:stretch;
			height: 100%;
			min-height: 0;
			overflow: hidden;
		}
		.chat-panel {
			background: rgba(255,255,255,.96);
			border:1px solid #dbe5ee;
			border-radius:24px;
			box-shadow:0 14px 36px rgba(15,23,42,.06);
			backdrop-filter: blur(14px);
			height: 100%;
			min-height: 0;
			overflow:hidden;
		}
		.chat-list-panel { display:grid; grid-template-rows:auto 1fr; }
		.chat-list-head, .chat-thread-head { padding:18px 18px 14px; border-bottom:1px solid #dbe5ee; }
		.chat-list-title, .chat-thread-title { margin:0; font-size:1.05rem; font-weight:900; color:#0f172a; }
		.chat-list-note, .chat-thread-note { margin:6px 0 0; color:#64748b; font-size:.88rem; line-height:1.45; }
		.chat-list { overflow:auto; padding:10px; display:grid; gap:10px; align-content:start; grid-auto-rows:max-content; }

		.chat-item {
			display:grid;
			grid-template-columns:52px minmax(0,1fr);
			gap:12px;
			align-items:center;
			width:100%;
			border:1px solid transparent;
			border-radius:18px;
			padding:12px;
			background:transparent;
			cursor:pointer;
			text-align:left;
			transition: background .18s ease, transform .18s ease, border-color .18s ease, box-shadow .18s ease;
		}
		.chat-item:hover { background:#f3f8fb; transform:translateY(-1px); }
		.chat-item.active { border-color:rgba(0,120,166,.24); background:linear-gradient(180deg, rgba(0,120,166,.08), rgba(0,120,166,.03)); box-shadow:0 10px 24px rgba(0,120,166,.08); }
		.chat-avatar { width:52px; height:52px; border-radius:50%; overflow:hidden; background:linear-gradient(135deg,#dbeafe,#f0f9ff); display:grid; place-items:center; font-weight:900; color:#0f172a; flex-shrink:0; }
		.chat-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
		.chat-item-body { min-width:0; display:grid; gap:4px; }
		.chat-item-top { display:flex; justify-content:space-between; gap:10px; align-items:flex-start; }
		.chat-name { margin:0; font-size:.98rem; font-weight:900; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
		.chat-pill { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.04em; background:#e0f2fe; color:#075985; }
		.chat-meta, .chat-preview, .chat-time { color:#64748b; font-size:.86rem; line-height:1.45; }
		.chat-preview { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
		.chat-preview.unread { font-weight:900; color:#0f172a; }
		.chat-time { font-size:.76rem; white-space:nowrap; }
		.chat-unread { display:inline-grid; place-items:center; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#0f172a; color:#fff; font-size:.72rem; font-weight:900; }

		.chat-thread-panel { display:grid; grid-template-rows:auto minmax(0,1fr) auto; }
		.thread-head-row { display:flex; gap:12px; align-items:center; }
		.thread-head-row .chat-avatar { width:60px; height:60px; }
		.thread-meta { display:grid; gap:6px; min-width:0; }
		.thread-meta-line { display:flex; flex-wrap:wrap; gap:8px; align-items:center; color:#64748b; font-size:.85rem; }
		.thread-body {
			padding:18px;
			overflow:auto;
			display:grid;
			align-content:start;
			gap:12px;
			background:linear-gradient(180deg, rgba(255,255,255,.8), rgba(255,255,255,.9)), radial-gradient(circle at top right, rgba(0,120,166,.08), transparent 30%);
		}
		.empty-state { display:grid; place-items:center; text-align:center; padding:36px 18px; gap:10px; color:#64748b; }
		.empty-state h3 { margin:0; color:#0f172a; font-size:1.1rem; font-weight:900; }
		.message { display:grid; gap:6px; max-width:min(82%, 640px); }
		.message.self { justify-self:end; }
		.message.other { justify-self:start; }
		.message-topline { display:flex; gap:8px; align-items:center; font-size:.76rem; color:#64748b; }
		.message.self .message-topline { justify-content:flex-end; }
		.message-bubble { background:#fff; border:1px solid #dbe5ee; border-radius:18px; padding:12px 14px; box-shadow:0 8px 22px rgba(15,23,42,.05); white-space:pre-wrap; word-break:break-word; line-height:1.55; color:#0f172a; }
		.message.self .message-bubble { background:#2c9cbc; color:#fff; border-color:rgba(44,156,188,.45); }
		.composer { padding:14px 16px 16px; border-top:1px solid #dbe5ee; background:rgba(255,255,255,.96); display:grid; gap:10px; }
		.composer-row { display:grid; grid-template-columns:1fr auto; gap:10px; align-items:center; }
		.composer-input { width:100%; border:1px solid #dbe5ee; border-radius:16px; padding:14px 16px; font-size:.98rem; color:#0f172a; background:#fff; }
		.composer-input:focus { outline:none; border-color:rgba(0,120,166,.5); box-shadow:0 0 0 4px rgba(0,120,166,.1); }
		.composer-button { appearance:none; border:0; border-radius:16px; padding:14px 18px; font-weight:900; background:#2c9cbc; color:#fff; cursor:pointer; box-shadow:0 10px 22px rgba(44,156,188,.26); }
		.composer-button:disabled { background:#cbd5e1; color:#fff; cursor:default; box-shadow:none; }
		.offer-decision-actions { display:flex; gap:10px; }
		.offer-decision-actions[hidden] { display:none !important; }
		.offer-decision-button { appearance:none; border:0; border-radius:14px; padding:12px 16px; font-weight:900; cursor:pointer; }
		.offer-decision-button.accept { background:#16a34a; color:#fff; }
		.offer-decision-button.reject { background:#dc2626; color:#fff; }
		.composer-status-note { margin:0; font-size:.82rem; color:#64748b; line-height:1.45; }
		.composer-status-note[hidden] { display:none !important; }
		.composer-hint { font-size:.76rem; color:#64748b; }
		.chat-guard { padding:14px 16px 0; color:#b45309; font-size:.88rem; }

		.bg-logo { position:fixed; inset:50% auto auto 50%; transform:translate(-50%,-50%); width:150px; max-width:150px; opacity:.22; z-index:0; pointer-events:none; }
		.bg-logo img { width:100%; height:auto; display:block; }

		.dash-float-nav {
			position:fixed; top:0; right:0; bottom:0; z-index:1000; display:flex; flex-direction:column; gap:8px; padding:12px 8px 8px; width:56px; overflow:hidden;
			border-top-left-radius:16px; border-bottom-left-radius:16px; background:#2596be !important; box-shadow:0 8px 24px rgba(0,0,0,.24) !important; transition:width .3s cubic-bezier(0.4,0,0.2,1), box-shadow .2s ease;
		}
		.dash-float-nav:hover { width:200px; box-shadow:0 12px 32px rgba(0,120,166,.35), 0 0 0 1px rgba(255,255,255,.5) inset; }
		.dash-float-nav .nav-brand { display:grid; place-items:center; position:relative; height:56px; padding:6px 0; }
		.dash-float-nav .nav-brand a { display:block; width:100%; height:100%; position:relative; text-decoration:none; }
		.dash-float-nav .nav-brand img { position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); display:block; object-fit:contain; pointer-events:none; transition:opacity .25s ease, transform .25s ease, width .3s ease; }
		.dash-float-nav .nav-brand .logo-small { width:26px; height:auto; opacity:1; }
		.dash-float-nav .nav-brand .logo-wide { width:160px; height:auto; opacity:0; }
		.dash-float-nav:hover .nav-brand .logo-small { opacity:0; transform:translate(-50%,-50%) scale(.96); }
		.dash-float-nav:hover .nav-brand .logo-wide { opacity:1; transform:translate(-50%,-50%) scale(1); }
		.dash-float-nav > .nav-main { display:grid; gap:8px; align-content:start; }
		.dash-float-nav > .nav-settings { margin-top:auto; display:grid; gap:8px; }
		.dash-float-nav a { position:relative; width:40px; height:40px; display:grid; grid-template-columns:40px 1fr; place-items:center; border-radius:12px; color:#fff !important; text-decoration:none; white-space:nowrap; transition:background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease, width .3s cubic-bezier(0.4,0,0.2,1); }
		.dash-float-nav:hover a { width:184px; }
		.dash-float-nav a:hover:not(.active) { background:rgba(255,255,255,.15) !important; color:#fff !important; }
		.dash-float-nav a.active { background:rgba(255,255,255,.22) !important; color:#fff !important; box-shadow:0 6px 18px rgba(0,0,0,.22) !important; }
		.dash-float-nav a.active::after { display:none !important; }
		.dash-icon { width:18px; height:18px; justify-self:center; object-fit:contain; transition:transform .2s ease; }
		.dash-float-nav a:hover .dash-icon { transform:scale(1.1); }
		.dash-text { opacity:0; transform:translateX(-10px); transition:opacity .3s cubic-bezier(0.4,0,0.2,1) .1s, transform .3s cubic-bezier(0.4,0,0.2,1) .1s; font-weight:800; font-size:.85rem; color:inherit; justify-self:start; padding-left:8px; }
		.dash-float-nav:hover .dash-text { opacity:1; transform:translateX(0); }

		@media (max-width: 1100px) {
			.chat-shell { grid-template-columns:1fr; }
			.chat-list-panel { max-height:260px; }
		}
		@media (max-width: 640px) {
			.chat-page { padding:16px 12px 24px; }
			.chat-tabs { max-width:none; flex-direction:column; }
			.chat-thread-head, .chat-list-head, .composer, .thread-body { padding-left:14px; padding-right:14px; }
			.message { max-width:92%; }
		}
	</style>
</head>
<body class="theme-profile-bg page-fade is-ready">
	<div class="bg-logo">
		<img id="bgLogo" src="../assets/images/<?php echo $tab === 'citizen' ? 'citizen' : 'kasangga'; ?>.png" alt="" onerror="this.style.display='none'">
	</div>

	<div class="chat-page">
		<nav class="mq-tabs chat-tabs" role="tablist" aria-label="Chat role tabs">
			<a class="mq-tab <?php echo $tab === 'kasangga' ? 'active' : ''; ?>" href="?tab=kasangga<?php echo $offerId ? '&offer=' . (int)$offerId : ''; ?><?php echo $jobId ? '&job=' . (int)$jobId : ''; ?>" data-tab="kasangga" role="tab" aria-selected="<?php echo $tab === 'kasangga' ? 'true' : 'false'; ?>">As a Kasangga</a>
			<a class="mq-tab <?php echo $tab === 'citizen' ? 'active' : ''; ?>" href="?tab=citizen<?php echo $offerId ? '&offer=' . (int)$offerId : ''; ?><?php echo $jobId ? '&job=' . (int)$jobId : ''; ?>" data-tab="citizen" role="tab" aria-selected="<?php echo $tab === 'citizen' ? 'true' : 'false'; ?>">As a Citizen</a>
		</nav>

		<div class="chat-shell">
			<section class="chat-panel chat-list-panel">
				<div class="chat-list-head">
					<h2 class="chat-list-title" id="conversationTitle"><?php echo $tab === 'citizen' ? 'Incoming offers' : 'Outgoing offers'; ?></h2>
					<p class="chat-list-note" id="conversationNote"><?php echo $tab === 'citizen' ? 'Offers from different users on your posted gawain will appear here.' : 'Offers you sent will appear here with the latest replies.'; ?></p>
				</div>
				<div class="chat-list" id="conversationList" aria-live="polite">
					<div class="empty-state" id="listEmptyState">
						<h3>Loading conversations</h3>
						<p>Please wait while we fetch your latest offers.</p>
					</div>
				</div>
			</section>

			<section class="chat-panel chat-thread-panel">
				<div class="chat-thread-head">
					<div class="thread-head-row">
						<div class="chat-avatar" id="threadAvatar">?</div>
						<div class="thread-meta">
							<h2 class="chat-thread-title" id="threadTitle">Select a conversation</h2>
							<p class="chat-thread-note" id="threadNote">Pick an offer from the list to open the live thread.</p>
							<div class="thread-meta-line" id="threadMetaLine"></div>
						</div>
					</div>
				</div>

				<div class="thread-body" id="threadBody">
					<div class="empty-state">
						<h3>No conversation selected</h3>
						<p>Once you choose a thread, messages will appear here in real time.</p>
					</div>
				</div>

				<div class="composer">
					<form id="chatComposerForm" class="composer-row" autocomplete="off">
						<input type="text" id="chatComposerInput" class="composer-input" placeholder="Write a message..." aria-label="Write a message" <?php echo $isLoggedIn ? '' : 'disabled'; ?> />
						<button type="submit" class="composer-button" id="chatComposerButton" <?php echo $isLoggedIn ? '' : 'disabled'; ?>>Send</button>
					</form>
					<div class="offer-decision-actions" id="offerDecisionActions" hidden>
						<button type="button" class="offer-decision-button accept" id="offerAcceptButton">Accept</button>
						<button type="button" class="offer-decision-button reject" id="offerRejectButton">Reject</button>
					</div>
					<p class="composer-status-note" id="composerStatusNote" hidden></p>
				</div>
				<?php if (!$isLoggedIn): ?>
					<div class="chat-guard">You need to log in before you can join a chat.</div>
				<?php endif; ?>
			</section>
		</div>
	</div>

	<nav class="dash-float-nav" id="dashNav">
		<div class="nav-brand">
			<a href="./home-gawain.php" title="">
				<img class="logo-small" src="../assets/images/job_logo.png" alt="ServisyoHub logo">
				<img class="logo-wide" src="../assets/images/newlogo2.png" alt="ServisyoHub">
			</a>
		</div>

		<div class="nav-main">
			<a href="./profile.php" aria-label="Profile">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
				<span class="dash-text">Profile</span>
			</a>
			<a href="./post.php" aria-label="Post">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
				<span class="dash-text">Post</span>
			</a>
			<a href="./my-gawain.php" aria-label="My Gawain">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
				<span class="dash-text">My Gawain</span>
			</a>
			<a href="./chats.php" class="active" aria-current="page" aria-label="Chats">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
				<span class="dash-text">Chats</span>
			</a>
		</div>

		<div class="nav-settings">
			<a href="./about-us.php" aria-label="About Us">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
				<span class="dash-text">About Us</span>
			</a>
			<a href="./terms-and-conditions.php" aria-label="Terms & Conditions">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4h12v16H6z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
				<span class="dash-text">Terms & Conditions</span>
			</a>
			<a href="./profile.php?logout=1" aria-label="Log out">
				<svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 21V3"/></svg>
				<span class="dash-text">Log out</span>
			</a>
		</div>
	</nav>

	<script>
	(function(){
		const apiUrl = './chat-api.php';
		const viewerId = <?php echo (int)$viewerId; ?>;
		const defaultTab = <?php echo json_encode($tab); ?>;
		const initialOfferId = <?php echo (int)$offerId; ?>;
		const initialJobId = <?php echo (int)$jobId; ?>;

		const state = {
			tab: defaultTab,
			offerId: initialOfferId,
			jobId: initialJobId,
			conversations: [],
			thread: null,
			firstLoad: true
		};

		const els = {
			list: document.getElementById('conversationList'),
			tabs: Array.from(document.querySelectorAll('.chat-tabs .mq-tab')),
			bgLogo: document.getElementById('bgLogo'),
			threadBody: document.getElementById('threadBody'),
			threadTitle: document.getElementById('threadTitle'),
			threadNote: document.getElementById('threadNote'),
			threadMetaLine: document.getElementById('threadMetaLine'),
			threadAvatar: document.getElementById('threadAvatar'),
			composerForm: document.getElementById('chatComposerForm'),
			composerInput: document.getElementById('chatComposerInput'),
			composerButton: document.getElementById('chatComposerButton'),
			offerDecisionActions: document.getElementById('offerDecisionActions'),
			offerAcceptButton: document.getElementById('offerAcceptButton'),
			offerRejectButton: document.getElementById('offerRejectButton'),
			composerStatusNote: document.getElementById('composerStatusNote'),
			conversationTitle: document.getElementById('conversationTitle'),
			conversationNote: document.getElementById('conversationNote')
		};

		function setComposerState(locked, note){
			if (!els.composerInput || !els.composerButton) return;
			els.composerInput.disabled = locked || !viewerId;
			els.composerButton.disabled = locked || !viewerId;
			if (note) {
				els.composerInput.placeholder = note;
			} else {
				els.composerInput.placeholder = 'Write a message...';
			}
			if (els.composerStatusNote) {
				if (note) {
					els.composerStatusNote.textContent = note;
					els.composerStatusNote.hidden = false;
				} else {
					els.composerStatusNote.textContent = '';
					els.composerStatusNote.hidden = true;
				}
			}
		}

		function renderComposerState(thread){
			if (!thread) {
				if (els.offerDecisionActions) els.offerDecisionActions.hidden = true;
				setComposerState(true, 'Select a conversation to continue.');
				return;
			}

			const status = String(thread.offer_status || 'pending').toLowerCase();
			const isCitizenOwner = String(thread.viewer_role || '') === 'citizen';

			if (status === 'pending') {
				if (isCitizenOwner) {
					if (els.offerDecisionActions) els.offerDecisionActions.hidden = false;
					setComposerState(true, 'Decide on this offer first before chatting.');
				} else {
					if (els.offerDecisionActions) els.offerDecisionActions.hidden = true;
					setComposerState(true, 'Waiting for the citizen to accept your offer.');
				}
				return;
			}

			if (els.offerDecisionActions) els.offerDecisionActions.hidden = true;

			if (status === 'rejected') {
				setComposerState(true, 'This offer was rejected. Messaging is disabled.');
				return;
			}

			setComposerState(false, '');
		}

		function escapeHtml(value){
			return String(value)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;');
		}

		function formatDate(value){
			if (!value) return '';
			const date = new Date(String(value).replace(' ', 'T'));
			if (Number.isNaN(date.getTime())) return value;
			return new Intl.DateTimeFormat(undefined, {
				month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit'
			}).format(date);
		}

		function formatAmount(value){
			const amount = Number(value || 0);
			return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'PHP', maximumFractionDigits: 2 }).format(amount);
		}

		function initials(name){
			const trimmed = String(name || '').trim();
			if (!trimmed) return '?';
			return trimmed.split(/\s+/).slice(0, 2).map(part => part.charAt(0).toUpperCase()).join('');
		}

		function updateUrl(){
			const params = new URLSearchParams(window.location.search);
			params.set('tab', state.tab);
			if (state.offerId) params.set('offer', String(state.offerId)); else params.delete('offer');
			if (state.jobId) params.set('job', String(state.jobId)); else params.delete('job');
			history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
		}

		function setConversationCopy(){
			if (els.tabs && els.tabs.length) {
				els.tabs.forEach((tabLink) => {
					const isActive = tabLink.dataset.tab === state.tab;
					tabLink.classList.toggle('active', isActive);
					tabLink.setAttribute('aria-selected', isActive ? 'true' : 'false');
				});
			}
			if (els.bgLogo) {
				els.bgLogo.src = state.tab === 'citizen' ? '../assets/images/citizen.png' : '../assets/images/kasangga.png';
			}
			if (els.conversationTitle) els.conversationTitle.textContent = state.tab === 'citizen' ? 'Incoming offers' : 'Outgoing offers';
			if (els.conversationNote) {
				els.conversationNote.textContent = state.tab === 'citizen'
					? 'Offers from different users on your posted gawain will appear here.'
					: 'Offers you sent will appear here with the latest replies.';
			}
		}

		function renderConversations(){
			if (!els.list) return;
			setConversationCopy();
			if (!state.conversations.length) {
				els.list.innerHTML = `
					<div class="empty-state">
						<h3>${state.tab === 'citizen' ? 'No incoming offers yet' : 'No outgoing offers yet'}</h3>
						<p>${state.tab === 'citizen' ? 'When users submit offers on your posted gawain, they will show up here automatically.' : 'When you make an offer, your chat thread will show up here automatically.'}</p>
						<a class="empty-btn" href="${state.tab === 'citizen' ? './my-gawain.php?tab=posted' : './home-gawain.php'}">${state.tab === 'citizen' ? 'Review my posts' : 'Browse gawain'}</a>
					</div>`;
				return;
			}

			els.list.innerHTML = state.conversations.map((item) => {
				const active = Number(item.offer_id) === Number(state.offerId) && Number(item.job_id) === Number(state.jobId) ? 'active' : '';
				const avatar = item.counterparty_avatar
					? `<img src="${escapeHtml(item.counterparty_avatar)}" alt="${escapeHtml(item.counterparty_name)}">`
					: `<span>${escapeHtml(initials(item.counterparty_name))}</span>`;
				const preview = item.latest_message_body ? item.latest_message_body : `Offer ${formatAmount(item.amount)}`;
				const unread = Number(item.unread_count || 0);
				const previewClass = unread > 0 ? 'chat-preview unread' : 'chat-preview';
				const badge = state.tab === 'citizen' ? 'Offerer' : 'Citizen';
				return `
					<button type="button" class="chat-item ${active}" data-offer="${escapeHtml(item.offer_id)}" data-job="${escapeHtml(item.job_id)}">
						<div class="chat-avatar">${avatar}</div>
						<div class="chat-item-body">
							<div class="chat-item-top">
								<h3 class="chat-name">${escapeHtml(item.counterparty_name || 'User')}</h3>
								<span class="chat-time">${escapeHtml(formatDate(item.latest_message_created_at || item.offer_created_at))}</span>
							</div>
							<div class="${previewClass}">${escapeHtml(preview)}</div>
							<div class="chat-item-top" style="align-items:center;">
								<span class="chat-pill">${escapeHtml(badge)}</span>
								${unread > 0 ? `<span class="chat-unread">${unread}</span>` : ''}
							</div>
						</div>
					</button>`;
			}).join('');

			els.list.querySelectorAll('.chat-item').forEach((button) => {
				button.addEventListener('click', () => {
					state.offerId = Number(button.dataset.offer || 0);
					state.jobId = Number(button.dataset.job || 0);
					updateUrl();
					loadThread(true);
					renderConversations();
				});
			});
		}

		function renderThread(thread){
			if (!thread) {
				renderComposerState(null);
				return;
			}
			const otherName = thread.counterparty_name || 'User';
			if (els.threadAvatar) {
				els.threadAvatar.innerHTML = thread.counterparty_avatar
					? `<img src="${escapeHtml(thread.counterparty_avatar)}" alt="${escapeHtml(otherName)}">`
					: `<span>${escapeHtml(initials(otherName))}</span>`;
			}
			if (els.threadTitle) els.threadTitle.textContent = otherName;
			if (els.threadNote) {
				els.threadNote.textContent = `${thread.title || 'Untitled gawain'} • ${thread.location || 'Online'} • ${thread.date_needed || 'Anytime'}`;
			}
			if (els.threadMetaLine) {
				const meta = [];
				if (thread.offer_amount !== null && thread.offer_amount !== undefined) meta.push(formatAmount(thread.offer_amount));
				if (thread.offer_status) meta.push(String(thread.offer_status).toUpperCase());
				els.threadMetaLine.innerHTML = meta.map((value) => `<span class="chat-pill">${escapeHtml(value)}</span>`).join('');
			}

			const messages = Array.isArray(thread.messages) ? thread.messages : [];
			if (!messages.length) {
				renderComposerState(thread);
				els.threadBody.innerHTML = `
					<div class="empty-state">
						<h3>Conversation is empty</h3>
						<p>Send the first message to start the live chat.</p>
					</div>`;
				return;
			}

			els.threadBody.innerHTML = messages.map((message) => {
				const self = Number(message.sender_id) === viewerId;
				const sender = self ? 'You' : (message.sender_name || otherName);
				const topAvatar = self ? '' : `<div class="chat-avatar" style="width:28px;height:28px;">${message.sender_avatar ? `<img src="${escapeHtml(message.sender_avatar)}" alt="${escapeHtml(sender)}">` : `<span>${escapeHtml(initials(sender))}</span>`}</div>`;
				return `
					<article class="message ${self ? 'self' : 'other'}" data-message-id="${escapeHtml(message.id)}">
						<div class="message-topline">${topAvatar}<span>${escapeHtml(sender)}</span><span>•</span><span>${escapeHtml(formatDate(message.created_at))}</span></div>
						<div class="message-bubble">${escapeHtml(message.body)}</div>
					</article>`;
			}).join('');

			const last = els.threadBody.lastElementChild;
			renderComposerState(thread);
			if (state.firstLoad) {
				last?.scrollIntoView({ behavior: 'auto', block: 'end' });
				state.firstLoad = false;
			} else {
				const nearBottom = els.threadBody.scrollHeight - els.threadBody.scrollTop - els.threadBody.clientHeight < 160;
				if (nearBottom) last?.scrollIntoView({ behavior: 'smooth', block: 'end' });
			}
		}

		async function loadConversations(autoSelect){
			try {
				const response = await fetch(`${apiUrl}?action=list&tab=${encodeURIComponent(state.tab)}`, { credentials: 'same-origin' });
				const data = await response.json();
				if (!data.ok) throw new Error(data.error || 'Unable to load conversations');
				state.conversations = Array.isArray(data.conversations) ? data.conversations : [];
				renderConversations();
				if (autoSelect && state.conversations.length) {
					const current = state.conversations.find((item) => Number(item.offer_id) === Number(state.offerId) && Number(item.job_id) === Number(state.jobId));
					const next = current || state.conversations[0];
					state.offerId = Number(next.offer_id);
					state.jobId = Number(next.job_id);
					updateUrl();
					await loadThread(true);
				}
				if (!state.conversations.length) {
					renderComposerState(null);
					els.threadBody.innerHTML = `
						<div class="empty-state">
							<h3>No conversation selected</h3>
							<p>Choose an offer from the list to open the live chat.</p>
						</div>`;
				}
			} catch (error) {
				els.list.innerHTML = `
					<div class="empty-state">
						<h3>Unable to load conversations</h3>
						<p>${escapeHtml(error.message || 'Please refresh the page.')}</p>
					</div>`;
			}
		}

		async function loadThread(){
			if (!state.offerId || !state.jobId) return;
			try {
				const params = new URLSearchParams({ action: 'thread', tab: state.tab, offer_id: String(state.offerId), job_id: String(state.jobId) });
				const response = await fetch(`${apiUrl}?${params.toString()}`, { credentials: 'same-origin' });
				const data = await response.json();
				if (!data.ok) throw new Error(data.error || 'Unable to load thread');
				state.thread = data.thread || null;
				renderThread(state.thread);
				renderConversations();
			} catch (error) {
				els.threadBody.innerHTML = `
					<div class="empty-state">
						<h3>Unable to load thread</h3>
						<p>${escapeHtml(error.message || 'Please try again.')}</p>
					</div>`;
			}
		}

		async function pollThread(){
			if (!state.offerId || !state.jobId) return;
			try {
				const params = new URLSearchParams({ action: 'thread', tab: state.tab, offer_id: String(state.offerId), job_id: String(state.jobId) });
				const response = await fetch(`${apiUrl}?${params.toString()}`, { credentials: 'same-origin' });
				const data = await response.json();
				if (!data.ok) return;
				state.thread = data.thread || null;
				renderThread(state.thread);
				renderConversations();
			} catch (error) {
				// polling is best-effort
			}
		}

		async function sendMessage(body){
			if (!state.offerId || !state.jobId || !body.trim()) return;
			const response = await fetch(apiUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ action: 'send', tab: state.tab, offer_id: state.offerId, job_id: state.jobId, body: body.trim() })
			});
			const data = await response.json();
			if (!data.ok) throw new Error(data.error || 'Message send failed');
			els.composerInput.value = '';
			await loadThread();
			await loadConversations(false);
		}

		async function submitOfferDecision(decision){
			if (!state.offerId || !state.jobId) return;
			const response = await fetch(apiUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ action: 'decision', tab: state.tab, offer_id: state.offerId, job_id: state.jobId, decision })
			});
			const data = await response.json();
			if (!data.ok) throw new Error(data.error || 'Could not update offer');
			await loadThread();
			await loadConversations(false);
		}

		if (els.composerForm) {
			els.composerForm.addEventListener('submit', async (event) => {
				event.preventDefault();
				if (!viewerId || !els.composerInput.value.trim()) return;
				els.composerButton.disabled = true;
				try {
					await sendMessage(els.composerInput.value);
				} catch (error) {
					alert(error.message || 'Could not send message.');
				} finally {
					els.composerButton.disabled = !viewerId;
				}
			});
		}

		if (els.offerAcceptButton) {
			els.offerAcceptButton.addEventListener('click', async () => {
				els.offerAcceptButton.disabled = true;
				if (els.offerRejectButton) els.offerRejectButton.disabled = true;
				try {
					await submitOfferDecision('accepted');
				} catch (error) {
					alert(error.message || 'Could not accept offer.');
				} finally {
					els.offerAcceptButton.disabled = false;
					if (els.offerRejectButton) els.offerRejectButton.disabled = false;
				}
			});
		}

		if (els.offerRejectButton) {
			els.offerRejectButton.addEventListener('click', async () => {
				els.offerRejectButton.disabled = true;
				if (els.offerAcceptButton) els.offerAcceptButton.disabled = true;
				try {
					await submitOfferDecision('rejected');
				} catch (error) {
					alert(error.message || 'Could not reject offer.');
				} finally {
					els.offerRejectButton.disabled = false;
					if (els.offerAcceptButton) els.offerAcceptButton.disabled = false;
				}
			});
		}

		if (els.tabs && els.tabs.length) {
			els.tabs.forEach((tabLink) => {
				tabLink.addEventListener('click', async (event) => {
					event.preventDefault();
					const nextTab = tabLink.dataset.tab === 'citizen' ? 'citizen' : 'kasangga';
					if (nextTab === state.tab) return;
					state.tab = nextTab;
					state.offerId = 0;
					state.jobId = 0;
					state.thread = null;
					state.firstLoad = true;
					updateUrl();
					setConversationCopy();
					await loadConversations(true);
				});
			});
		}

		window.addEventListener('popstate', () => {
			const params = new URLSearchParams(window.location.search);
			state.tab = params.get('tab') === 'citizen' ? 'citizen' : 'kasangga';
			state.offerId = Number(params.get('offer') || 0);
			state.jobId = Number(params.get('job') || 0);
			setConversationCopy();
			loadConversations(false);
			loadThread();
		});

		async function boot(){
			setConversationCopy();
			await loadConversations(true);
			window.setInterval(() => loadConversations(false), 5000);
			window.setInterval(() => pollThread(), 3500);
		}

		boot();
	})();
	</script>
</body>
</html>
