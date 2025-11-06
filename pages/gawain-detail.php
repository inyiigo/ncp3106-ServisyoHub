<?php
// Start output buffering to avoid header issues
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// include DB connection (use the requested relative path)
include '../config/db_connect.php';

// support either $conn or $mysqli provided by the included file
$db = $conn ?? $mysqli ?? null;

// ensure correct table name used everywhere
$table = 'jobs';

// Define currentUserId early to avoid "undefined variable" warnings
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt_money($v){
    if ($v === '' || $v === null) return 'Negotiable';
    $n = is_numeric($v) ? (float)$v : 0; return '₱' . number_format($n, 2);
}
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

$jobs = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0 && $db) {
    // fetch all columns from jobs table
    $sql = "SELECT * FROM {$table} WHERE id = ? LIMIT 1";
    if ($stmt = mysqli_prepare($db, $sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) {
            $res = mysqli_stmt_get_result($stmt);
            $jobs = mysqli_fetch_assoc($res) ?: null;
        }
        mysqli_stmt_close($stmt);
    }
}

// Determine final page title from DB (if present) after attempt to fetch $jobs
// Fallback sample if not found
if (!$jobs) {
	$jobs = [
		'id' => 0,
		'title' => 'Household Cleaning (2-Bedroom)',
		'category' => 'Household',
		'location' => 'Quezon City',
		'budget' => 1800,
		'date_needed' => date('D, d M', strtotime('+7 days')) . ' (Anytime)',
		'status' => 'open',
		'duration_hours' => 3,
		'description' => "I am looking for help with project that requires assistance with household cleaning tasks.\n\nTask Description:\n- General cleaning and tidying.\n- Wiping surfaces and light dusting.\n- Assisting with sorting and basic organization.\n\nSkills and Experience Required:\n- Basic experience with household cleaning.\n- Attention to detail and punctuality.",
		'workers_required' => 1,
		'posted_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
		'user_name' => 'Citizen',
		'offers_count' => 0,
	];
}

// (now using $jobs everywhere)

// Ensure the page uses the DB title when available
$avatar = strtoupper(substr(preg_replace('/\s+/', '', ($jobs['user_name'] ?? '')), 0, 1));
$pageTitle = isset($jobs['title']) ? trim((string)$jobs['title']) : '';
// budget/status/duration/offers are not part of the minimal schema you listed;
// keep safe fallbacks — price will show "Negotiable" when budget missing
$priceLabel = fmt_money($jobs['budget'] ?? '');
$status = 'Open'; // default to Open (adjust if your table has a status column)
$durationLabel = '—';
$offers = 0;

// helpers_needed from your schema -> displayable helpers count
$helpersNeeded = (int)($jobs['helpers_needed'] ?? 1);
$displayName = $_SESSION['display_name'] ?? $_SESSION['mobile'] ?? 'You';
$askerInitial = strtoupper(substr(preg_replace('/\s+/', '', (string)$displayName), 0, 1));

// Resolve avatar URLs (client and current user) if available
function url_from_path($p){
  if (!$p) return '';
  // If already absolute http(s) keep it, otherwise prefix to project root
  if (preg_match('#^https?://#i', $p)) return $p;
  return '../' . ltrim($p, '/');
}

$clientAvatarUrl = '';
$jobOwnerId = 0;

// --- Use user_id from the fetched $jobs row when available (most reliable) ---
if (!empty($jobs['user_id'])) {
  $jobOwnerId = (int)$jobs['user_id'];
}
// Best-effort: try jobs.user_id -> users.avatar
if ($id > 0 && $db) {
  if ($jobOwnerId === 0) {
    // 1) try via jobs table user_id column (older schema)
    if ($stmt = @mysqli_prepare($db, "SELECT user_id FROM {$table} WHERE id = ? LIMIT 1")) {
      mysqli_stmt_bind_param($stmt, 'i', $id);
      if (@mysqli_stmt_execute($stmt)) {
        $res = @mysqli_stmt_get_result($stmt);
        if ($res && ($row = @mysqli_fetch_assoc($res)) && !empty($row['user_id'])) {
          $jobOwnerId = (int)$row['user_id'];
        }
      }
      @mysqli_stmt_close($stmt);
    }
  }
  // 1) try via user_id column
  if ($stmt = @mysqli_prepare($db, "SELECT user_id FROM {$table} WHERE id = ? LIMIT 1")) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (@mysqli_stmt_execute($stmt)){
      $res = @mysqli_stmt_get_result($stmt);
      if ($res && ($row = @mysqli_fetch_assoc($res)) && !empty($row['user_id'])) {
        $uid = (int)$row['user_id'];
        // only set if not already set from $jobs
        if ($jobOwnerId === 0) $jobOwnerId = $uid;
        if ($u = @mysqli_prepare($db, "SELECT COALESCE(avatar,'') AS avatar FROM users WHERE id = ? LIMIT 1")) {
          mysqli_stmt_bind_param($u, 'i', $uid);
          if (@mysqli_stmt_execute($u)) {
            $ur = @mysqli_stmt_get_result($u);
            if ($ur && ($urw = @mysqli_fetch_assoc($ur))) {
              $clientAvatarUrl = url_from_path($urw['avatar'] ?? '');
            }
          }
          @mysqli_stmt_close($u);
        }
      }
    }
    @mysqli_stmt_close($stmt);
  }
  // 2) try jobs.user_avatar column (if exists)
  if ($clientAvatarUrl === '' && ($s2 = @mysqli_prepare($db, "SELECT COALESCE(user_avatar,'') AS ua FROM {$table} WHERE id = ? LIMIT 1"))) {
    mysqli_stmt_bind_param($s2, 'i', $id);
    if (@mysqli_stmt_execute($s2)) {
      $r2 = @mysqli_stmt_get_result($s2);
      if ($r2 && ($w2 = @mysqli_fetch_assoc($r2))) {
        $clientAvatarUrl = url_from_path($w2['ua'] ?? '');
      }
    }
    @mysqli_stmt_close($s2);
  }
  // 3) match by name (username or first+last)
  if ($clientAvatarUrl === '' && !empty($jobs['user_name'])) {
    $name = (string)$jobs['user_name'];
    if ($s3 = @mysqli_prepare($db, "SELECT COALESCE(avatar,'') AS avatar FROM users WHERE username = ? OR CONCAT(TRIM(first_name),' ',TRIM(last_name)) = ? LIMIT 1")) {
      mysqli_stmt_bind_param($s3, 'ss', $name, $name);
      if (@mysqli_stmt_execute($s3)) {
        $r3 = @mysqli_stmt_get_result($s3);
        if ($r3 && ($w3 = @mysqli_fetch_assoc($r3))) {
          $clientAvatarUrl = url_from_path($w3['avatar'] ?? '');
        }
      }
      @mysqli_stmt_close($s3);
    }
  }
}

// Current logged-in user avatar (for ask input)
$askerAvatarUrl = '';
if (!empty($_SESSION['user_id']) && $db) {
  $uid = (int)$_SESSION['user_id'];
  if ($s4 = @mysqli_prepare($db, "SELECT COALESCE(avatar,'') AS avatar FROM users WHERE id = ? LIMIT 1")) {
    mysqli_stmt_bind_param($s4, 'i', $uid);
    if (@mysqli_stmt_execute($s4)) {
      $r4 = @mysqli_stmt_get_result($s4);
      if ($r4 && ($w4 = @mysqli_fetch_assoc($r4))) $askerAvatarUrl = url_from_path($w4['avatar'] ?? '');
    }
    @mysqli_stmt_close($s4);
  }
}

// Determine if current viewer is the owner of this post
$isOwner = false;
// Primary check: compare logged-in user's id with job owner id
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId > 0 && $jobOwnerId > 0) {
  $isOwner = ($currentUserId === (int)$jobOwnerId);
} else {
  // Fallback: if no numeric owner id, try name-based fallback (less reliable)
  if ($jobOwnerId === 0) {
    $viewerName = (string)($displayName ?? ($_SESSION['display_name'] ?? ''));
    $isOwner = (trim(strtolower($viewerName)) === trim(strtolower((string)($jobs['user_name'] ?? ''))));
  } else {
    $isOwner = false;
  }
}

// DEBUG (temporary): uncomment to see values in UI during testing
// echo "<div style='position:fixed;right:10px;top:10px;background:#ffd;'>isOwner=".($isOwner?'Y':'N')." currentUser={$currentUserId} jobOwner={$jobOwnerId}</div>";

// Compute offers count for this job
if ($id > 0 && $db) {
  if ($s = @mysqli_prepare($db, "SELECT COUNT(*) AS c FROM offers WHERE job_id = ?")) {
    mysqli_stmt_bind_param($s, 'i', $id);
    if (@mysqli_stmt_execute($s)) {
      $r = @mysqli_stmt_get_result($s);
      if ($r && ($w = @mysqli_fetch_assoc($r))) {
        $offers = (int)($w['c'] ?? 0);
      }
    }
    @mysqli_stmt_close($s);
  }
}
 
 ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo e($pageTitle ?: ($jobs['title'] ?? '')); ?> • Servisyo Hub</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <style>
    .detail-wrap { max-width: 980px; margin: clamp(16px, 6vh, 80px) auto 24px; padding: 0 16px; }
    .detail-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom: 8px; }
    /* Back button style copied from make-offer */
    .back-btn { display:inline-grid; place-items:center; width:38px; height:38px; border-radius:10px; border:2px solid #e2e8f0; color:#0f172a; text-decoration:none; }
    .back-btn:hover { background:#f8fafc; }
    .detail-title { margin: 6px 0 0; font-weight: 900; font-size: clamp(22px, 5vw, 32px); }

    .price-row { display:flex; align-items:center; gap:12px; margin: 8px 0 12px; }
    .price { font-weight:900; font-size: 1.2rem; }
    .badge { display:inline-flex; align-items:center; padding: 6px 10px; border-radius: 999px; background: #e9f8ef; color:#166534; font-weight: 800; border: 2px solid #bbf7d0; }

    /* Two-column layout: main + right-side ask panel */
    .detail-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
    @media (min-width: 980px){ .detail-grid { grid-template-columns: 1fr 360px; align-items: start; } }
    .detail-aside { position: sticky; top: 80px; align-self: start; }
    /* Make the main details card span full width to align with the Ask box margins */
    @media (min-width: 980px){
      .detail-main { grid-column: 1 / -1; }
      .detail-aside { grid-column: 1 / -1; position: static; top: auto; }
    }

  .meta-grid { display:grid; grid-template-columns: 1fr; gap: 12px; margin: 14px 0; }
  .meta-card { background:#fff; border:2px solid #e2e8f0; border-radius:12px; padding:12px; display:grid; gap:6px; }
  /* Single-box meta layout */
  .meta-merged { background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; padding:12px; margin: 14px 0; }
  .meta-merged .meta-grid2 { display:grid; grid-template-columns: 1fr; gap: 12px; }
  @media (min-width:720px){ .meta-merged .meta-grid2 { grid-template-columns: 1fr 1fr; } }
  .meta-item { display:grid; gap:6px; }
  /* subtle separators to visually unify as one box */
  .meta-merged .meta-item { padding: 4px 2px; }
  .meta-merged .meta-item:not(:nth-child(1)) { border-top: 1px solid #e2e8f0; padding-top: 10px; }
  @media (min-width:720px){
    .meta-merged .meta-item { border-top: 0; }
    .meta-merged .meta-item:nth-child(n+3) { border-top: 1px solid #e2e8f0; padding-top: 10px; }
  }
  /* compact single-box list layout */
  .info-list { display:grid; gap:10px; margin-top:8px; position: relative; }
  @media (min-width:720px){
    .info-list { grid-template-columns: 1fr 1fr; column-gap: 24px; row-gap: 12px; }
    .info-list::after { content:""; position:absolute; top:0; bottom:0; left:50%; width:1px; background:#e2e8f0; transform: translateX(-0.5px); }
  }
  .info-row { display:grid; gap:4px; }
  .info-row .label { font-weight:600; font-size:.9rem; color:#64748b; display:inline-flex; align-items:center; gap:6px; }
  .info-row .label .ico { width:14px; height:14px; color:#64748b; }
  .info-row .value { font-weight:800; font-size:1.05rem; color:#0f172a; }
  .meta-divider { height:1px; background:#e2e8f0; margin:10px 0; }
  /* description inside the unified box */
  .merged-desc { display:grid; gap:6px; }
  .merged-desc h3 { margin:0; font-size:.95rem; font-weight:600; color:#64748b; }
  .merged-desc pre { margin:0; white-space: pre-wrap; font-family: inherit; color:#0f172a; }
    .meta-title { font-weight:600; font-size:.9rem; color:#64748b; margin:0; }
    .meta-item .value { font-weight:800; font-size:1.05rem; color:#0f172a; }
    .poster { display:grid; grid-template-columns: 42px 1fr; gap:10px; align-items:center; }
  .avatar { width:42px; height:42px; border-radius:50%; background:#e2e8f0; display:grid; place-items:center; font-weight:900; color:#0f172a; overflow: hidden; }
  .avatar-img { width: 100%; height: 100%; object-fit: cover; display: block; }

  .desc-card { background:#fff; border:2px solid #e2e8f0; border-radius:12px; padding:16px; margin-top: 8px; }
  .desc-card h3 { margin: 0 0 8px; font-size: .95rem; font-weight: 600; color:#64748b; }

  /* Ask a question panel */
  .ask-panel { background:#fff; border:2px solid #e2e8f0; border-radius:12px; padding:14px; box-shadow: 0 6px 16px rgba(2,6,23,.04); }
  .ask-title { margin:0 0 4px; font-weight:900; font-size:1.05rem; display:flex; align-items:center; gap:8px; }
  .ask-sub { margin:0 0 10px; color:#64748b; font-size:.92rem; }
  /* Comment-style thread */
  .comment-list { display:grid; gap:10px; }
  .comment { display:grid; grid-template-columns: 36px 1fr; gap:10px; align-items:flex-start; }
  .comment .avatar { width:36px; height:36px; border-radius:50%; overflow:hidden; background:#e2e8f0; display:grid; place-items:center; font-weight:900; color:#0f172a; }
  .comment .avatar img { width:100%; height:100%; object-fit:cover; display:block; }
  .bubble { background:#fff; border:2px solid #e2e8f0; border-radius:18px; padding:10px 12px; box-shadow: 0 1px 0 rgba(2,6,23,.02); }
  .bubble .name { font-weight:900; color:#0f172a; display:block; }
  .bubble .text { margin:4px 0 6px; color:#0f172a; }
  .bubble .meta { color:#94a3b8; font-size:.85rem; display:flex; gap:10px; }
  .bubble .meta a { color:#64748b; text-decoration:none; font-weight:800; }
  .bubble .meta a:hover { text-decoration:underline; }
  .bubble .meta a.delete-link { color:#ef4444; }
  /* Place replies in the text column so right edges align with parent bubble */
  .comment .replies { grid-column: 2 / -1; margin-left:0; padding-left:0; width: 100%; display:grid; gap:8px; }
  /* Smaller look for sub-comments */
  .replies .comment { grid-template-columns: 28px 1fr; gap:8px; }
  .replies .comment .avatar { width:28px; height:28px; }
  .replies .comment .bubble { border-radius:14px; padding:8px 10px; }
  .replies .comment .bubble .name { font-size:.92rem; }
  .replies .comment .bubble .text { font-size:.95rem; }
  .replies .comment .bubble .meta { font-size:.8rem; }
  .reply-form { display:grid; grid-template-columns:36px 1fr; gap:10px; align-items:center; margin-top:6px; }
  .reply-form .ask-av { width:32px; height:32px; }
  /* Make nested reply forms match smaller grid */
  .replies .reply-form { grid-template-columns:28px 1fr; }
  
  .ask-input { display:grid; grid-template-columns: 36px 1fr; gap:10px; align-items:center; margin-top:8px; }
  .ask-av { width:36px; height:36px; border-radius:50%; background:#e2e8f0; display:grid; place-items:center; font-weight:900; color:#0f172a; overflow:hidden; }
  .ask-av img { width:100%; height:100%; object-fit:cover; display:block; }
  .ask-field { width:100%; border:2px solid #e2e8f0; border-radius:999px; padding:10px 14px; background:#fff; color:#0f172a; }
  .ask-field::placeholder { color:#94a3b8; }
  /* ask bar variant matching the screenshot */
  .ask-input-row { display:flex; align-items:center; gap:10px; margin-top:8px; }
  .ask-counter { width:36px; height:36px; border-radius:50%; background:#eef2f7; color:#0f172a; display:grid; place-items:center; font-weight:800; border:2px solid #94a3b8; box-shadow: inset 0 1px 0 rgba(255,255,255,.5); }
  .ask-form { flex:1; display:flex; align-items:center; gap:8px; }
  .ask-send { appearance:none; border:0; background:transparent; display:inline-grid; place-items:center; width:32px; height:32px; border-radius:8px; color:#1d4ed8; cursor:pointer; flex-shrink:0; }
  .ask-send:hover { background:#f1f5f9; }
  .ask-send:active { transform: translateY(0.5px); }
  .ask-send svg { width:18px; height:18px; display:block; }
  /* slightly smaller send button in nested reply forms */
  .replies .ask-send { width:28px; height:28px; border-radius:6px; }
  /* inset style when ask lives inside the meta-merged card */
  .ask-inset { background: transparent; border: 0; padding: 0; box-shadow: none; }
  .ask-inset .ask-title { margin-top: 6px; }
    .desc-card pre { margin: 0; white-space: pre-wrap; font-family: inherit; color:#0f172a; }

  .footer-bar { position: sticky; bottom: 0; background: rgba(255,255,255,.96); border-top: 0; backdrop-filter: saturate(110%) blur(6px); }
  .footer-inner { max-width:980px; margin:0 auto; padding: 10px 16px; display:flex; align-items:center; gap:12px; justify-content: flex-end; }
    
    .footer-actions { display:flex; gap:8px; }
    .btn-ghost { background:#fff; border:2px solid #e2e8f0; color:#0f172a; padding:12px 16px; border-radius:12px; font-weight:800; text-decoration:none; }
    .btn-solid { background:#111827; color:#fff; border:0; padding:12px 16px; border-radius:12px; font-weight:900; text-decoration:none; }

    @media (min-width:720px){ .meta-grid { grid-template-columns: 1fr 1fr; } }
    /* Detail page header: no bottom border, align left */
    .detail-topbar { border-bottom: 0 !important; justify-content: flex-start; }

    /* Unbold all texts on this page */
    :root { --fw-normal: 400; }
    body, body *:not(svg):not(path) { font-weight: var(--fw-normal) !important; }

    /* Re-bold only the job title */
    :root { --fw-bold: 800; }
    body.theme-profile-bg .detail-title { font-weight: var(--fw-bold) !important; }
  </style>
</head>
<body class="theme-profile-bg page-fade is-ready">
  <header class="dash-topbar detail-topbar">
    <a class="back-btn" href="./home-gawain.php" aria-label="Back to posts">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
  </header>

  <main class="detail-wrap">
    <h1 class="detail-title"><?php echo e($pageTitle ?: ($jobs['title'] ?? '')); ?></h1>

    <div class="price-row">
      <div class="price"><?php echo e($priceLabel); ?></div>
      <span class="badge"><?php echo e($status); ?></span>
      <span style="margin-left:auto; color:#64748b; font-weight:700;">Posted <?php echo e(date('M j', strtotime($jobs['posted_at'] ?? 'now'))); ?></span>
    </div>

    <div class="detail-grid">
      <div class="detail-main">
        <div class="meta-merged" aria-label="Details">
            <div class="meta-item">
              <h3 class="meta-title">Posted by</h3>
              <div class="poster">
                <div class="avatar">
                  <?php
                  // Show user avatar if available, else show attached photo (your-photo.png)
                  // Make avatar clickable and link to user-detail.php
                  $userDetailUrl = '';
                  if (!empty($jobOwnerId)) {
                    $userDetailUrl = './user-detail.php?id=' . (int)$jobOwnerId;
                  } elseif (!empty($jobs['user_name'])) {
                    $userDetailUrl = './user-detail.php?name=' . urlencode($jobs['user_name']);
                  } else {
                    $userDetailUrl = '#';
                  }
                  if (!empty($clientAvatarUrl)) {
                    echo '<a href="' . e($userDetailUrl) . '" title="View user details"><img class="avatar-img" src="' . e($clientAvatarUrl) . '" alt="' . e($jobs['user_name'] ?? '') . '" /></a>';
                  } else {
                    echo '<a href="' . e($userDetailUrl) . '" title="View user details"><img class="avatar-img" src="../assets/images/your-photo.png" alt="User Photo" style="object-fit:cover;" /></a>';
                  }
                  ?>
                </div>
                <div>
                  <div style="font-weight:800;"><?php echo e($jobs['user_name'] ?? ''); ?></div>
                  <div style="color:#64748b; font-size:.9rem;">No reviews yet</div>
                </div>
              </div>
              <div class="meta-divider" role="presentation"></div>

              <div class="info-list">
                <div class="info-row">
                  <div class="label">
                    <span class="ico" aria-hidden="true">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <span>Location</span>
                  </div>
                  <div class="value"><?php echo ($jobs['location'] ?? '') ? e($jobs['location']) : 'Online'; ?></div>
                </div>
                <div class="info-row">
                  <div class="label">
                    <span class="ico" aria-hidden="true">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span>
                    <span>Completion Date</span>
                  </div>
                  <div class="value"><?php echo ($jobs['date_needed'] ?? '') ? e($jobs['date_needed']) : 'Anytime'; ?></div>
                </div>
                <div class="info-row">
                  <div class="label">
                    <span class="ico" aria-hidden="true">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </span>
                    <span>Duration</span>
                  </div>
                  <div class="value"><?php echo e($durationLabel); ?></div>
                </div>
                <div class="info-row">
                  <div class="label">
                    <span class="ico" aria-hidden="true">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H7l-4 2V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/></svg>
                    </span>
                    <span>Offers Received</span>
                  </div>
                  <div class="value"><?php echo e($offers); ?></div>
                </div>
                <div class="info-row">
                  <div class="label">
                    <span class="ico" aria-hidden="true">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    <span>Heroes Required</span>
                  </div>
                  <div class="value"><?php echo $helpersNeeded; ?></div>
                </div>
              </div>

              <div class="meta-divider" role="presentation"></div>
              <div class="merged-desc" aria-label="Description">
                <h3>Description</h3>
                <pre><?php echo e($jobs['description'] ?? ''); ?></pre>
              </div>

              <div class="meta-divider" role="presentation"></div>
              <section class="ask-panel ask-inset" id="ask-box" aria-label="Ask a question">
                <h3 class="ask-title">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 9a3 3 0 0 1 6 0c0 2-3 2-3 5"/><circle cx="12" cy="17" r="1"/></svg>
                  Ask a question
                </h3>
                <p class="ask-sub">Clarify the details of the quest with the Citizen before making an offer!</p>

                <div class="comment-list" aria-label="Recent Q&A">
                  <div class="comment">
                    <div class="avatar">
                      <img src="../assets/images/avatar-placeholder.png" alt="Jericho bien V." onerror="this.style.display='none'" />
                    </div>
                    <div class="bubble">
                      <span class="name">Jericho bien V.</span>
                      <p class="text">location po??</p>
                      <div class="meta"><span>4m ago</span><a href="#" class="reply-link">Reply</a> <a href="#" class="delete-link">Delete</a></div>
                    </div>
                    <div class="replies">
                      <div class="comment">
                        <div class="avatar">
                          <?php if (!empty($clientAvatarUrl)) : ?>
                            <img src="<?php echo e($clientAvatarUrl); ?>" alt="<?php echo e($jobs['user_name'] ?? ''); ?>" />
                          <?php else: ?>
                            <?php echo e(strtoupper($avatar)); ?>
                          <?php endif; ?>
                        </div>
                        <div class="bubble">
                          <span class="name"><?php echo e($jobs['user_name'] ?? ''); ?></span>
                          <p class="text"><?php echo ($jobs['location'] ?? '') ? e($jobs['location']) : 'Online'; ?></p>
                          <div class="meta"><span>4m ago</span><a href="#" class="reply-link">Reply</a> <a href="#" class="delete-link">Delete</a></div>
                        </div>
                        <div class="replies"></div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="ask-input-row">
                  <div class="ask-counter" id="askCount" aria-label="Questions count">0</div>
                  <form class="ask-form" id="askForm" novalidate>
                    <input class="ask-field" type="text" name="question" placeholder="Ask <?php echo e($jobs['user_name'] ?? ''); ?> a question" aria-label="Ask a question" required />
                    <button type="submit" class="ask-send" aria-label="Send question" title="Send">
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3 12l18-9-9 18-1.5-6L3 12z"/>
                      </svg>
                    </button>
                  </form>
                </div>
              </section>
            </div>
          </div>
        </div>
      </div>

      
    </div>
  </main>

  <footer class="footer-bar">
    <div class="footer-inner">
      <div class="footer-actions">
        <a class="btn-ghost" href="#ask-box" role="button">Ask a question</a>
        <?php if ($isOwner): ?>
          <!-- Owner sees View offers button -->
          <a class="btn-solid" href="./my-gawain.php?tab=offered&job_id=<?php echo (int)$id; ?>" role="button">View offers (<?php echo (int)$offers; ?>)</a>
        <?php else: ?>
          <!-- Non-owner sees Make offer button -->
          <a class="btn-solid" href="./make-offer.php<?php echo $id ? ('?id='.(int)$id) : ''; ?>" role="button">Make offer</a>
        <?php endif; ?>
      </div>
    </div>
  </footer>
  <script>
    // Lightweight reply interaction inside the embedded ask section
    document.addEventListener('click', function(evt){
      const a = evt.target.closest('.reply-link');
      if (!a) return;
      evt.preventDefault();
      const comment = a.closest('.comment');
      if (!comment) return;
      // Prefer the direct replies child, not a deeper descendant
      let replies = comment.querySelector(':scope > .replies');
      if (!replies) {
        replies = document.createElement('div');
        replies.className = 'replies';
        comment.appendChild(replies);
      }
      // If a form already exists under this specific comment, focus it
      let form = replies.querySelector(':scope > .reply-form');
      if (form) { const input = form.querySelector('input'); if (input) input.focus(); return; }

      const targetName = comment.querySelector('.bubble .name')?.textContent?.trim() || '<?php echo e($jobs['user_name'] ?? ''); ?>';

      // Build a small reply form with contextual placeholder and a Cancel link
      form = document.createElement('form');
      form.className = 'reply-form';
      form.innerHTML = `
        <div class="ask-av"><?php if (!empty($askerAvatarUrl)) : ?><img src="<?php echo e($askerAvatarUrl); ?>" alt="You" /><?php else: ?><?php echo e($askerInitial); ?><?php endif; ?></div>
        <div style="display:flex; gap:8px; align-items:center; width:100%;">
          <input class="ask-field" type="text" name="reply" placeholder="Reply to ${targetName}" aria-label="Write a reply" required />
          <button type="submit" class="ask-send" aria-label="Send reply" title="Send">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 12l18-9-9 18-1.5-6L3 12z"/></svg>
          </button>
          <button type="button" class="btn-cancel-reply" style="border:0;background:transparent;color:#64748b;font-weight:800;">Cancel</button>
        </div>
      `;
      replies.appendChild(form);
      const input = form.querySelector('input');
      if (input) input.focus();

      const cancel = form.querySelector('.btn-cancel-reply');
      if (cancel) {
        cancel.addEventListener('click', function(){ form.remove(); });
      }

      form.addEventListener('submit', function(e){
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        // Render a new nested comment bubble
        const wrap = document.createElement('div');
        wrap.className = 'comment';
        wrap.innerHTML = `
          <div class="avatar"><?php if (!empty($askerAvatarUrl)) : ?><img src="<?php echo e($askerAvatarUrl); ?>" alt="You" /><?php else: ?><?php echo e($askerInitial); ?><?php endif; ?></div>
          <div class="bubble">
            <span class="name">You</span>
            <p class="text"></p>
            <div class="meta"><span>just now</span><a href="#" class="reply-link">Reply</a> <a href="#" class="delete-link">Delete</a></div>
          </div>
          <div class="replies"></div>
        `;
        wrap.querySelector('.text').textContent = text;
        replies.appendChild(wrap);
        input.value = '';
        // Optionally remove the form after submit
        form.remove();
        wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      });
    });
    // Ask bar submit -> post into comments list
    (function(){
      const form = document.getElementById('askForm');
      const input = form ? form.querySelector('input[name="question"]') : null;
      const list = document.querySelector('.comment-list');
      const counter = document.getElementById('askCount');

      function topLevelCount(){
        if (!list) return 0;
        // count only direct children with class comment
        return Array.from(list.children).filter(el => el.classList && el.classList.contains('comment')).length;
      }

      function refreshCount(){
        if (counter) counter.textContent = String(topLevelCount());
      }

      refreshCount();

      if (form && input && list) {
        form.addEventListener('submit', function(e){
          e.preventDefault();
          const text = (input.value || '').trim();
          if (!text) { input.focus(); return; }
          const wrap = document.createElement('div');
          wrap.className = 'comment';
          wrap.innerHTML = `
            <div class="avatar"><?php if (!empty($askerAvatarUrl)) : ?><img src="<?php echo e($askerAvatarUrl); ?>" alt="You" /><?php else: ?><?php echo e($askerInitial); ?><?php endif; ?></div>
            <div class="bubble">
              <span class="name">You</span>
              <p class="text"></p>
              <div class="meta"><span>just now</span><a href="#" class="reply-link">Reply</a> <a href="#" class="delete-link">Delete</a></div>
            </div>
            <div class="replies"></div>
          `;
          wrap.querySelector('.text').textContent = text;
          list.appendChild(wrap);
          input.value = '';
          refreshCount();
          // Optional: scroll new comment into view
          wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
        // Submit on Enter when focused
        input.addEventListener('keydown', function(e){
          if (e.key === 'Enter') {
            e.preventDefault();
            form.requestSubmit();
          }
        });
      }
    })();

    // Owner-only delete: remove a comment (top-level or nested)
    document.addEventListener('click', function(evt){
      const del = evt.target.closest('.delete-link');
      if (!del) return;
      evt.preventDefault();
      // Guard in UI only; real enforcement needs server-side auth
      const comment = del.closest('.comment');
      if (!comment) return;
      const ok = confirm('Delete this comment?');
      if (!ok) return;
      const parent = comment.parentElement;
      comment.remove();
      // Update counter if a top-level comment was removed
      const list = document.querySelector('.comment-list');
      const counter = document.getElementById('askCount');
      if (parent && list && parent === list && counter) {
        const cnt = Array.from(list.children).filter(el => el.classList && el.classList.contains('comment')).length;
        counter.textContent = String(cnt);
      }
    });
  </script>
</body>
</html>
