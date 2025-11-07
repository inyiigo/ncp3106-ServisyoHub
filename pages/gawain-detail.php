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
$pageTitle = isset($jobs['title']) ? trim((string)$jobs['title']) : '';
// budget/status/duration/offers are not part of the minimal schema you listed;
// keep safe fallbacks — price will show "Negotiable" when budget missing
$priceLabel = fmt_money($jobs['budget'] ?? '');
// use DB status with nice casing
$status = ucfirst(strtolower((string)($jobs['status'] ?? 'open')));
// duration from estimated_hours (jobs schema)
$durationLabel = (!empty($jobs['estimated_hours']))
	? (rtrim(rtrim(number_format((float)$jobs['estimated_hours'], 2, '.', ''), '0'), '.') . ' hr' . (((float)$jobs['estimated_hours'] == 1.0) ? '' : 's'))
	: '—';
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

// Fetch poster identity from users via jobs.user_id
$clientAvatarUrl = '';

// --- Consolidated poster/owner resolution (merged from both conflict sides) ---
$jobOwnerId = (int)($jobs['user_id'] ?? 0);
$posterUsername = $posterFirst = $posterLast = $posterName = '';
$posterFullName = '';

// If we have a numeric owner id, try to fetch name/avatar from users table
if ($jobOwnerId > 0 && $db) {
	if ($u = @mysqli_prepare(
		$db,
		"SELECT 
			COALESCE(username,'')     AS username,
			COALESCE(first_name,'')   AS first_name,
			COALESCE(last_name,'')    AS last_name,
			COALESCE(avatar,'')       AS avatar
		 FROM users WHERE id = ? LIMIT 1"
	)) {
		mysqli_stmt_bind_param($u, 'i', $jobOwnerId);
		if (@mysqli_stmt_execute($u)) {
			$ur = @mysqli_stmt_get_result($u);
			if ($ur && ($row = @mysqli_fetch_assoc($ur))) {
				$posterUsername  = (string)($row['username'] ?? '');
				$posterFirst     = (string)($row['first_name'] ?? '');
				$posterLast      = (string)($row['last_name'] ?? '');
				$clientAvatarUrl = url_from_path($row['avatar'] ?? '');
			}
		}
		@mysqli_stmt_close($u);
	}
}

// Build display name preference: username > "First Last" > Citizen
$posterName = trim($posterUsername !== '' ? $posterUsername : trim($posterFirst . ' ' . $posterLast));
if ($posterName === '') $posterName = trim((string)($jobs['user_name'] ?? ''));
if ($posterName === '') $posterName = 'Citizen';

// Full name for parentheses display (may be empty)
$posterFullName = trim(($posterFirst ?? '') . ' ' . ($posterLast ?? ''));

// Avatar initial from poster name (fallback)
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $posterName), 0, 1));

// Fallbacks to find avatar if not found above
// 1) try jobs.user_avatar column (if exists)
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
// 2) match by name (username or first+last) if we at least have a name
if ($clientAvatarUrl === '' && $posterName !== '') {
	if ($s3 = @mysqli_prepare($db, "SELECT COALESCE(avatar,'') AS avatar FROM users WHERE username = ? OR CONCAT(TRIM(first_name),' ',TRIM(last_name)) = ? LIMIT 1")) {
		mysqli_stmt_bind_param($s3, 'ss', $posterName, $posterName);
		if (@mysqli_stmt_execute($s3)) {
			$r3 = @mysqli_stmt_get_result($s3);
			if ($r3 && ($w3 = @mysqli_fetch_assoc($r3))) {
				$clientAvatarUrl = url_from_path($w3['avatar'] ?? '');
			}
		}
		@mysqli_stmt_close($s3);
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

// Viewer / session id (use a single canonical variable)
$viewerId = (int)($_SESSION['user_id'] ?? 0);

// Determine if current viewer is the owner of this post
$isOwner = false;
if ($viewerId && $jobOwnerId) {
	$isOwner = ($viewerId === $jobOwnerId);
} elseif ($jobOwnerId === 0) {
	// Fallback: compare display name and job user_name if user_id isn't available
	$viewerName = (string)($displayName ?? ($_SESSION['display_name'] ?? ''));
	$isOwner = (trim(strtolower($viewerName)) === trim(strtolower((string)($jobs['user_name'] ?? ''))));
}

// Build the destination href depending on ownership
$profileHref = $isOwner
  ? './profile.php'
  : ($jobOwnerId
      ? ('./user-detail.php?id=' . (int)$jobOwnerId)
      : ($posterName !== '' ? ('./user-detail.php?name=' . urlencode($posterName)) : '#'));

// Compute offers count for this job (ensure $offers is set)
$offers = 0;
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

// --- CSRF + POST handlers (add_comment, add_reply, delete_comment) ---
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
$csrf_ok = function($t){ return is_string($t ?? '') && hash_equals($_SESSION['csrf'] ?? '', $t); };

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db && $id > 0) {
	$action = $_POST['action'] ?? '';
	$token  = $_POST['csrf'] ?? '';
	if ($csrf_ok($token)) {
		if ($action === 'add_comment' && $viewerId) {
			$body = trim((string)($_POST['body'] ?? ''));
			if ($body !== '') {
				if ($st = @mysqli_prepare($db, "INSERT INTO comments (job_id,user_id,parent_id,body,created_at) VALUES (?,?,?,?,NOW())")) {
					$null = null;
					mysqli_stmt_bind_param($st, 'iiis', $id, $viewerId, $null, $body);
					@mysqli_stmt_execute($st);
					$cid = (int)@mysqli_insert_id($db);
					@mysqli_stmt_close($st);

					// create notification for job owner (if different user)
					if ($cid > 0 && $jobOwnerId && $viewerId !== (int)$jobOwnerId) {
						if ($nst = @mysqli_prepare($db, "INSERT INTO notifications (user_id,actor_id,job_id,comment_id) VALUES (?,?,?,?)")) {
							mysqli_stmt_bind_param($nst, 'iiii', $jobOwnerId, $viewerId, $id, $cid);
							@mysqli_stmt_execute($nst);
							@mysqli_stmt_close($nst);
						}
					}
				}
			}
			header('Location: ./gawain-detail.php?id='.$id.'#ask-box'); exit;
		}
		if ($action === 'add_reply' && $viewerId) {
			$body = trim((string)($_POST['body'] ?? ''));
			$parent_id = (int)($_POST['parent_id'] ?? 0);
			if ($body !== '' && $parent_id > 0) {
				// ensure parent belongs to this job
				$ok = false;
				if ($chk = @mysqli_prepare($db, "SELECT id FROM comments WHERE id=? AND job_id=? LIMIT 1")) {
					mysqli_stmt_bind_param($chk, 'ii', $parent_id, $id);
					if (@mysqli_stmt_execute($chk)) {
						$r = @mysqli_stmt_get_result($chk);
						$ok = (bool)@mysqli_fetch_assoc($r);
					}
					@mysqli_stmt_close($chk);
				}
				if ($ok && ($st = @mysqli_prepare($db, "INSERT INTO comments (job_id,user_id,parent_id,body,created_at) VALUES (?,?,?,?,NOW())"))) {
					mysqli_stmt_bind_param($st, 'iiis', $id, $viewerId, $parent_id, $body);
					@mysqli_stmt_execute($st);
					$cid = (int)@mysqli_insert_id($db);
					@mysqli_stmt_close($st);

					// notify job owner (skip self)
					if ($cid > 0 && $jobOwnerId && $viewerId !== (int)$jobOwnerId) {
						if ($nst = @mysqli_prepare($db, "INSERT INTO notifications (user_id,actor_id,job_id,comment_id) VALUES (?,?,?,?)")) {
							mysqli_stmt_bind_param($nst, 'iiii', $jobOwnerId, $viewerId, $id, $cid);
							@mysqli_stmt_execute($nst);
							@mysqli_stmt_close($nst);
						}
					}
				}
			}
			header('Location: ./gawain-detail.php?id='.$id.'#c'.$parent_id); exit;
		}
		if ($action === 'delete_comment') {
			$cid = (int)($_POST['comment_id'] ?? 0);
			if ($cid > 0) {
				// Allow delete by comment owner or job owner
				$owner_id = 0;
				if ($get = @mysqli_prepare($db, "SELECT user_id FROM comments WHERE id=? LIMIT 1")) {
					mysqli_stmt_bind_param($get, 'i', $cid);
					if (@mysqli_stmt_execute($get)) {
						$res = @mysqli_stmt_get_result($get);
						if ($row = @mysqli_fetch_assoc($res)) $owner_id = (int)$row['user_id'];
					}
					@mysqli_stmt_close($get);
				}
				if ($viewerId && ($viewerId === $owner_id || $viewerId === $jobOwnerId)) {
					if ($del = @mysqli_prepare($db, "DELETE FROM comments WHERE id=? LIMIT 1")) {
						mysqli_stmt_bind_param($del, 'i', $cid);
						@mysqli_stmt_execute($del);
						@mysqli_stmt_close($del);
					}
				}
			}
			header('Location: ./gawain-detail.php?id='.$id.'#ask-box'); exit;
		}
	}
}

// Fetch comments for this job (thread + replies)
$thread = [];   // parent comments
$replies = [];  // replies grouped by parent_id
if ($db && $id > 0) {
	$sql = "SELECT c.id, c.parent_id, c.body, c.created_at, c.user_id,
	               COALESCE(u.username,'') AS username,
	               COALESCE(u.first_name,'') AS first_name,
	               COALESCE(u.last_name,'')  AS last_name,
	               COALESCE(u.avatar,'')     AS avatar
	        FROM comments c
	        LEFT JOIN users u ON u.id = c.user_id
	        WHERE c.job_id = ?
	        ORDER BY c.created_at ASC, c.id ASC";
	if ($st = @mysqli_prepare($db, $sql)) {
		mysqli_stmt_bind_param($st, 'i', $id);
		if (@mysqli_stmt_execute($st)) {
			$res = @mysqli_stmt_get_result($st);
			while ($row = @mysqli_fetch_assoc($res)) {
				if (empty($row['parent_id'])) $thread[] = $row;
				else $replies[(int)$row['parent_id']][] = $row;
			}
		}
		@mysqli_stmt_close($st);
	}
}

// Small helpers for rendering names/avatars
$display_of = function($r){
	$name = trim($r['username'] ?? '');
	if ($name === '') $name = trim(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')));
	return $name !== '' ? $name : 'User';
};
$avatar_of = function($r){
	$p = (string)($r['avatar'] ?? '');
	if ($p !== '') {
		if (preg_match('#^https?://#i', $p)) return $p;
		return '../' . ltrim($p,'/');
	}
	return ''; // will show initial
};

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
    /* Base layout */
    .detail-wrap { max-width: 1080px; margin: clamp(16px,5vh,64px) auto 32px; padding:0 20px; }
    .detail-title { margin: 6px 0 8px; font-weight: 900; font-size: clamp(22px, 5vw, 32px); word-wrap:break-word; }

    .price-row { display:flex; align-items:center; gap:12px; margin: 8px 0 16px; flex-wrap:wrap; }
    .price { font-weight:900; font-size: 1.2rem; }
    .badge { display:inline-flex; align-items:center; padding: 6px 12px; border-radius: 999px; font-size:.75rem; font-weight:700; text-transform:uppercase; }
    .badge.pending { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24; }
    .badge.open { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
    .badge.accepted { background: #dbeafe; color: #1e40af; border: 1px solid #60a5fa; }

    /* Two-column grid: main + ask panel */
    .detail-grid { display: grid; gap: 24px; grid-template-columns: 1fr; }
    @media (min-width: 1000px){
      .detail-grid { grid-template-columns: minmax(0, 1fr) 380px; align-items: start; }
    }

    /* Main details card */
    .detail-main-card {
      background:#fff; border:2px solid #e2e8f0; border-radius:18px;
      padding:20px 22px; box-shadow:0 8px 24px rgba(15,23,42,.06);
      display:grid; gap:20px; overflow:hidden;
    }

    /* Posted by + info grid */
    .meta-top { display:grid; gap:18px; }
    @media (min-width:640px){
      .meta-top { grid-template-columns: 1fr 1fr; align-items:start; position:relative; }
      .meta-top::before {
        content:""; position:absolute; left:50%; top:0; bottom:0;
        width:1px; background:#e2e8f0; transform:translateX(-50%);
      }
    }

    .poster-row { display:flex; gap:14px; align-items:center; }
    .poster-row .avatar { width:56px; height:56px; border-radius:50%; overflow:hidden; background:#e2e8f0; display:grid; place-items:center; font-weight:700; color:#0f172a; }
    .poster-row .avatar-img { width:100%; height:100%; object-fit:cover; }

    .info-block { display:grid; gap:14px; }
    .info-item { display:grid; gap:4px; }
    .info-label { font-size:.75rem; font-weight:600; text-transform:uppercase; color:#64748b; }
    .info-value { font-weight:700; color:#0f172a; font-size:1rem; word-wrap:break-word; overflow-wrap:break-word; }

    /* Description box with scroll */
    .description-box {
      background:#f8fafc; border:2px solid #e2e8f0; border-radius:14px;
      padding:16px; display:grid; gap:10px;
      max-height:280px; overflow:auto; scrollbar-width:thin;
    }
    .description-box h3 { margin:0; font-size:.8rem; font-weight:700; text-transform:uppercase; color:#64748b; }
    .description-box pre {
      margin:0; white-space:pre-wrap; font-family:inherit; line-height:1.5;
      color:#0f172a; word-wrap:break-word; overflow-wrap:break-word;
    }

    /* Ask panel as sticky aside */
    .detail-aside {
      position:sticky; top:86px;
      background:#fff; border:2px solid #e2e8f0; border-radius:18px;
      padding:18px 20px; box-shadow:0 8px 24px rgba(15,23,42,.06);
      display:grid; gap:16px;
    }
    .ask-title { margin:0; font-weight:800; font-size:1.05rem; display:flex; align-items:center; gap:8px; }
    .ask-sub { margin:0; color:#64748b; font-size:.85rem; }

    .comment-list { display:grid; gap:12px; }
    .comment { display:grid; grid-template-columns:36px 1fr; gap:10px; align-items:flex-start; }
    .comment .avatar { width:36px; height:36px; border-radius:50%; background:#e2e8f0; display:grid; place-items:center; font-weight:700; color:#0f172a; overflow:hidden; }
    .comment .avatar img { width:100%; height:100%; object-fit:cover; }
    .bubble { background:#fff; border:2px solid #e2e8f0; border-radius:16px; padding:10px 12px; word-wrap:break-word; overflow-wrap:break-word; }
    .bubble .name { font-weight:700; color:#0f172a; font-size:.9rem; }
    .bubble .text { margin:4px 0 6px; line-height:1.45; font-size:.92rem; }
    .bubble .meta { display:flex; gap:12px; font-size:.7rem; color:#94a3b8; font-weight:600; flex-wrap:wrap; }
    .bubble .meta a { text-decoration:none; font-weight:700; color:#64748b; }
    .bubble .meta a.delete-link { color:#ef4444; }

    .replies { grid-column:2 / -1; display:grid; gap:8px; margin-top:6px; }
    .replies .comment { grid-template-columns:28px 1fr; gap:8px; }
    .replies .avatar { width:28px; height:28px; }
    .replies .bubble { border-radius:14px; padding:8px 10px; }

    .reply-form { display:grid; grid-template-columns:32px 1fr; gap:10px; margin-top:4px; align-items:center; }
    .ask-input-row { display:flex; gap:10px; align-items:center; margin-top:4px; }
    .ask-counter { width:36px; height:36px; border-radius:50%; background:#eef2f7; border:2px solid #94a3b8; display:grid; place-items:center; cursor:pointer; color:#0f172a; font-size:.75rem; }
    .ask-form { flex:1; display:flex; gap:6px; align-items:center; }
    .ask-field { flex:1; border:2px solid #e2e8f0; border-radius:999px; padding:10px 14px; font-size:.9rem; }
    .ask-field:focus { outline:none; border-color:#0f172a; }
    .ask-send { appearance:none; background:transparent; border:0; width:32px; height:32px; border-radius:8px; display:grid; place-items:center; cursor:pointer; color:#111827; }
    .ask-send:hover { background:#f1f5f9; }

    /* Footer */
    .footer-bar { position:sticky; bottom:0; background:rgba(255,255,255,.94); backdrop-filter:saturate(1.1) blur(6px); border-top:1px solid #e2e8f0; }
    .footer-inner { max-width:1080px; margin:0 auto; padding:12px 20px; display:flex; justify-content:flex-end; gap:12px; }
    .btn-ghost, .btn-solid { font-size:.85rem; padding:12px 18px; border-radius:12px; text-decoration:none; font-weight:700; }
    .btn-ghost { background:#fff; border:2px solid #e2e8f0; color:#0f172a; }
    .btn-solid { background:#111827; color:#fff; border:0; }
    .btn-solid:hover { background:#0f172a; }

    .back-btn { display:inline-grid; place-items:center; width:38px; height:38px; border-radius:10px; border:2px solid #e2e8f0; color:#0f172a; text-decoration:none; }
    .back-btn:hover { background:#f8fafc; }
    .detail-topbar { border-bottom:0 !important; justify-content:flex-start; }

    :root { --fw-normal: 400; --fw-bold: 800; }
    body, body *:not(svg):not(path) { font-weight: var(--fw-normal) !important; }
    .detail-title { font-weight: var(--fw-bold) !important; }
  </style>
</head>
<body class="theme-profile-bg page-fade is-ready">
  <header class="dash-topbar detail-topbar">
    <a class="back-btn" href="./home-gawain.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M15 18l-6-6 6-6"/></svg></a>
  </header>

  <main class="detail-wrap">
    <h1 class="detail-title"><?php echo e($pageTitle ?: ($jobs['title'] ?? '')); ?></h1>

    <div class="price-row">
      <div class="price"><?php echo e($priceLabel); ?></div>
      <span class="badge <?php echo strtolower($status); ?>"><?php echo e($status); ?></span>
      <span style="margin-left:auto; color:#64748b; font-weight:600; font-size:.8rem;">Posted <?php echo e(date('M j', strtotime($jobs['posted_at'] ?? 'now'))); ?></span>
    </div>

    <div class="detail-grid">
      <!-- LEFT: main details -->
      <div class="detail-main-card">
        <div class="meta-top">
          <div>
            <div class="poster-row">
              <div class="avatar">
                <?php if (!empty($clientAvatarUrl)): ?>
                  <a href="<?php echo e($profileHref); ?>"><img class="avatar-img" src="<?php echo e($clientAvatarUrl); ?>" alt="<?php echo e($posterName); ?>"></a>
                <?php else: ?>
                  <a href="<?php echo e($profileHref); ?>"><img class="avatar-img" src="../assets/images/your-photo.png" alt="<?php echo e($posterName); ?>"></a>
                <?php endif; ?>
              </div>
              <div>
                <div style="font-weight:800; display:flex; flex-wrap:wrap; gap:6px;">
                  <a href="<?php echo e($profileHref); ?>" style="text-decoration:none; color:inherit;"><?php echo e($posterName); ?></a>
                  <?php if ($posterFullName): ?><span style="color:#64748b; font-weight:600;">(<?php echo e($posterFullName); ?>)</span><?php endif; ?>
                </div>
                <small style="color:#64748b;">No reviews yet</small>
              </div>
            </div>
            <div class="info-block" style="margin-top:18px;">
              <div class="info-item"><span class="info-label">Location</span><span class="info-value"><?php echo ($jobs['location'] ?? '') ? e($jobs['location']) : 'Online'; ?></span></div>
              <div class="info-item"><span class="info-label">Completion Date</span><span class="info-value"><?php echo ($jobs['date_needed'] ?? '') ? e($jobs['date_needed']) : 'Anytime'; ?></span></div>
              <div class="info-item"><span class="info-label">Duration</span><span class="info-value"><?php echo e($durationLabel); ?></span></div>
              <div class="info-item"><span class="info-label">Offers Received</span><span class="info-value"><?php echo e($offers); ?></span></div>
              <div class="info-item"><span class="info-label">Heroes Required</span><span class="info-value"><?php echo $helpersNeeded; ?></span></div>
            </div>
          </div>
          <div>
            <div class="description-box"><h3>Description</h3><pre><?php echo e($jobs['description'] ?? ''); ?></pre></div>
          </div>
        </div>
      </div>

      <!-- RIGHT: ask panel -->
      <aside class="detail-aside" id="ask-box">
        <h3 class="ask-title"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 9a3 3 0 0 1 6 0c0 2-3 2-3 5"/><circle cx="12" cy="17" r="1"/></svg>Ask a question</h3>
        <p class="ask-sub">Clarify the details before making an offer.</p>

        <div class="comment-list">
          <?php if (empty($thread)): ?>
            <div class="bubble" style="border-radius:14px;">Be the first to ask a question.</div>
          <?php else: ?>
            <?php foreach ($thread as $c): ?>
              <?php
                $cName = $display_of($c);
                $cAv   = $avatar_of($c);
                $cInit = strtoupper(substr(preg_replace('/\s+/', '', $cName), 0, 1));
                $canDel = $viewerId && ($viewerId === (int)$c['user_id'] || $viewerId === (int)$jobOwnerId);
              ?>
              <div id="c<?php echo (int)$c['id']; ?>" class="comment">
                <div class="avatar">
                  <?php if ($cAv): ?><img src="<?php echo e($cAv); ?>" alt="<?php echo e($cName); ?>" /><?php else: ?><?php echo e($cInit); ?><?php endif; ?>
                </div>
                <div class="bubble">
                  <span class="name"><?php echo e($cName); ?></span>
                  <p class="text"><?php echo e($c['body']); ?></p>
                  <div class="meta">
                    <span><?php echo e(time_ago(strtotime($c['created_at']))); ?></span>
                    <a href="#" class="reply-toggle">Reply</a>
                    <?php if ($canDel): ?>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="action" value="delete_comment"/>
                      <input type="hidden" name="comment_id" value="<?php echo (int)$c['id']; ?>"/>
                      <input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf']); ?>"/>
                      <button class="delete-link" type="submit" style="background:none;border:0;cursor:pointer;">Delete</button>
                    </form>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="replies">
                  <?php if (!empty($replies[(int)$c['id']])): ?>
                    <?php foreach ($replies[(int)$c['id']] as $r): ?>
                      <?php
                        $rName = $display_of($r);
                        $rAv   = $avatar_of($r);
                        $rInit = strtoupper(substr(preg_replace('/\s+/', '', $rName), 0, 1));
                        $rDel  = $viewerId && ($viewerId === (int)$r['user_id'] || $viewerId === (int)$jobOwnerId);
                      ?>
                      <div class="comment">
                        <div class="avatar">
                          <?php if ($rAv): ?><img src="<?php echo e($rAv); ?>" alt="<?php echo e($rName); ?>" /><?php else: ?><?php echo e($rInit); ?><?php endif; ?>
                        </div>
                        <div class="bubble">
                          <span class="name"><?php echo e($rName); ?></span>
                          <p class="text"><?php echo e($r['body']); ?></p>
                          <div class="meta">
                            <span><?php echo e(time_ago(strtotime($r['created_at']))); ?></span>
                            <?php if ($rDel): ?>
                            <form method="post" style="display:inline;">
                              <input type="hidden" name="action" value="delete_comment"/>
                              <input type="hidden" name="comment_id" value="<?php echo (int)$r['id']; ?>"/>
                              <input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf']); ?>"/>
                              <button class="delete-link" type="submit" style="background:none;border:0;cursor:pointer;">Delete</button>
                            </form>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>

                  <!-- Inline reply form (toggles) -->
                  <form class="reply-form" method="post" style="display:none; margin-top:6px;">
                    <input type="hidden" name="action" value="add_reply"/>
                    <input type="hidden" name="parent_id" value="<?php echo (int)$c['id']; ?>"/>
                    <input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf']); ?>"/>
                    <div class="avatar" style="width:32px;height:32px;"><?php if (!empty($askerAvatarUrl)) : ?><img src="<?php echo e($askerAvatarUrl); ?>" alt="You" /><?php else: ?><?php echo e($askerInitial); ?><?php endif; ?></div>
                    <div style="display:flex; gap:8px; align-items:center; width:100%;">
                      <input class="ask-field" name="body" type="text" placeholder="Reply to <?php echo e($cName); ?>" aria-label="Write a reply" required <?php echo $viewerId ? '' : 'disabled'; ?> />
                      <button type="submit" class="ask-send" aria-label="Send reply" title="Send"<?php echo $viewerId ? '' : ' disabled'; ?>>
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 12l18-9-9 18-1.5-6L3 12z"/></svg>
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="ask-input-row">
          <div class="ask-counter" id="askCount" aria-label="Questions count"><?php echo (int)count($thread); ?></div>
          <form class="ask-form" id="askForm" method="post" novalidate>
            <input type="hidden" name="action" value="add_comment"/>
            <input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf']); ?>"/>
            <input class="ask-field" type="text" name="body" placeholder="Write a comment..." aria-label="Write a comment" required <?php echo $viewerId ? '' : 'disabled'; ?> />
            <button type="submit" class="ask-send" aria-label="Send question" title="Send"<?php echo $viewerId ? '' : ' disabled'; ?>>
              <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 12l18-9-9 18-1.5-6L3 12z"/></svg>
            </button>
          </form>
        </div>
        <?php if (!$viewerId): ?>
          <small class="hint">You must log in to post.</small>
        <?php endif; ?>
      </aside>
    </div>
  </main>

  <footer class="footer-bar">
    <div class="footer-inner">
      <a class="btn-ghost" href="#ask-box">Ask a question</a>
      <?php if ($isOwner): ?>
        <a class="btn-solid" href="./my-gawain.php?tab=offered&job_id=<?php echo (int)$id; ?>">View offers (<?php echo (int)$offers; ?>)</a>
      <?php else: ?>
        <a class="btn-solid" href="./make-offer.php<?php echo $id ? ('?id='.(int)$id) : ''; ?>">Make offer</a>
      <?php endif; ?>
    </div>
  </footer>

  <script>
    // Toggle inline reply forms (no API)
    document.addEventListener('click', function(e){
      const a = e.target.closest('.reply-toggle');
      if (!a) return;
      e.preventDefault();
      const comment = a.closest('.comment');
      const form = comment?.querySelector('.replies > .reply-form');
      if (form) form.style.display = (form.style.display === 'none' || !form.style.display) ? 'grid' : 'none';
    });
  </script>
</body>
</html>
