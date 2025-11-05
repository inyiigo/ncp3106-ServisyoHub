<?php
// Start output buffering to avoid header issues
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../config/db_connect.php';

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

$job = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0 && isset($conn) && $conn) {
    if ($stmt = mysqli_prepare($conn, "SELECT id, title, category, COALESCE(location,'') AS location, COALESCE(budget,'') AS budget, COALESCE(date_needed,'') AS date_needed, COALESCE(status,'open') AS status, COALESCE(duration_hours, 0) AS duration_hours, COALESCE(description,'') AS description, COALESCE(workers_required,1) AS workers_required, COALESCE(posted_at, NOW()) AS posted_at, COALESCE(user_name,'Citizen') AS user_name, COALESCE(offers_count,0) AS offers_count FROM jobs WHERE id = ? LIMIT 1")) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) {
            $res = mysqli_stmt_get_result($stmt);
            $job = mysqli_fetch_assoc($res) ?: null;
        }
        mysqli_stmt_close($stmt);
    }
}

// Fallback sample if not found
if (!$job) {
    $job = [
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

$avatar = strtoupper(substr(preg_replace('/\s+/', '', $job['user_name']), 0, 1));
$priceLabel = fmt_money($job['budget']);
$status = strtolower($job['status']) === 'open' ? 'Open' : ucfirst((string)$job['status']);
$durationLabel = ((int)$job['duration_hours'] > 0) ? ((int)$job['duration_hours'] . ' Hour(s)') : '—';
$offers = (int)($job['offers_count'] ?? 0);
$views = 10; // placeholder

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo e($job['title']); ?> • Servisyo Hub</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <style>
    .detail-wrap { max-width: 980px; margin: clamp(16px, 6vh, 80px) auto 24px; padding: 0 16px; }
    .detail-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom: 8px; }
    .detail-back { display:inline-flex; align-items:center; gap:8px; color:#0f172a; text-decoration:none; font-weight:800; }
    .detail-title { margin: 6px 0 0; font-weight: 900; font-size: clamp(22px, 5vw, 32px); }

    .price-row { display:flex; align-items:center; gap:12px; margin: 8px 0 12px; }
    .price { font-weight:900; font-size: 1.2rem; }
    .badge { display:inline-flex; align-items:center; padding: 6px 10px; border-radius: 999px; background: #e9f8ef; color:#166534; font-weight: 800; border: 2px solid #bbf7d0; }

  .meta-grid { display:grid; grid-template-columns: 1fr; gap: 12px; margin: 14px 0; }
  .meta-card { background:#fff; border:2px solid #e2e8f0; border-radius:12px; padding:12px; display:grid; gap:6px; }
  /* Single-box meta layout */
  .meta-merged { background:#fff; border:2px solid #e2e8f0; border-radius:12px; padding:12px; margin: 14px 0; }
  .meta-merged .meta-grid2 { display:grid; grid-template-columns: 1fr; gap: 12px; }
  @media (min-width:720px){ .meta-merged .meta-grid2 { grid-template-columns: 1fr 1fr; } }
  .meta-item { display:grid; gap:6px; }
    .meta-title { font-weight:600; font-size:.9rem; color:#64748b; margin:0; }
    .meta-item .value { font-weight:800; font-size:1.05rem; color:#0f172a; }
    .poster { display:grid; grid-template-columns: 42px 1fr; gap:10px; align-items:center; }
    .avatar { width:42px; height:42px; border-radius:50%; background:#e2e8f0; display:grid; place-items:center; font-weight:900; color:#0f172a; }

  .desc-card { background:#fff; border:2px solid #e2e8f0; border-radius:12px; padding:16px; margin-top: 8px; }
  .desc-card h3 { margin: 0 0 8px; font-size: .95rem; font-weight: 600; color:#64748b; }
    .desc-card pre { margin: 0; white-space: pre-wrap; font-family: inherit; color:#0f172a; }

  .footer-bar { position: sticky; bottom: 0; background: rgba(255,255,255,.96); border-top: 0; backdrop-filter: saturate(110%) blur(6px); }
  .footer-inner { max-width:980px; margin:0 auto; padding: 10px 16px; display:flex; align-items:center; gap:12px; justify-content: flex-end; }
    .footer-left { display:flex; align-items:center; gap:8px; color:#475569; font-weight:700; }
    .footer-actions { display:flex; gap:8px; }
    .btn-ghost { background:#fff; border:2px solid #e2e8f0; color:#0f172a; padding:12px 16px; border-radius:12px; font-weight:800; text-decoration:none; }
    .btn-solid { background:#111827; color:#fff; border:0; padding:12px 16px; border-radius:12px; font-weight:900; text-decoration:none; }

    @media (min-width:720px){ .meta-grid { grid-template-columns: 1fr 1fr; } }
    /* Detail page header: no bottom border, align left */
    .detail-topbar { border-bottom: 0 !important; justify-content: flex-start; }
  </style>
</head>
<body class="theme-profile-bg page-fade is-ready">
  <header class="dash-topbar detail-topbar">
    <a class="detail-back" href="./home-gawain.php" aria-label="Back to posts">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      <span class="sr-only">Back</span>
    </a>
  </header>

  <main class="detail-wrap">
    <h1 class="detail-title"><?php echo e($job['title']); ?></h1>

    <div class="price-row">
      <div class="price"><?php echo e($priceLabel); ?></div>
      <span class="badge"><?php echo e($status); ?></span>
      <span style="margin-left:auto; color:#64748b; font-weight:700;">Posted <?php echo e(date('M j', strtotime($job['posted_at']))); ?></span>
    </div>

    <div class="meta-merged" aria-label="Details">
      <div class="meta-grid2">
        <div class="meta-item">
          <h3 class="meta-title">Posted by</h3>
          <div class="poster">
            <div class="avatar"><?php echo e(strtoupper($avatar)); ?></div>
            <div>
              <div style="font-weight:800;"><?php echo e($job['user_name']); ?></div>
              <div style="color:#64748b; font-size:.9rem;">No reviews yet</div>
            </div>
          </div>
        </div>

        <div class="meta-item">
          <h3 class="meta-title">Location</h3>
          <div class="value"><?php echo $job['location'] ? e($job['location']) : 'Online'; ?></div>
        </div>

        <div class="meta-item">
          <h3 class="meta-title">Completion Date</h3>
          <div class="value"><?php echo $job['date_needed'] ? e($job['date_needed']) : 'Anytime'; ?></div>
        </div>

        <div class="meta-item">
          <h3 class="meta-title">Duration</h3>
          <div class="value"><?php echo e($durationLabel); ?></div>
        </div>

        <div class="meta-item">
          <h3 class="meta-title">Offers Received</h3>
          <div class="value"><?php echo e($offers); ?></div>
        </div>

        <div class="meta-item">
          <h3 class="meta-title">Heroes Required</h3>
          <div class="value"><?php echo (int)($job['workers_required'] ?? 1); ?></div>
        </div>
      </div>
    </div>

    <section class="desc-card" aria-label="Description">
      <h3>Description</h3>
      <pre><?php echo e($job['description']); ?></pre>
    </section>
  </main>

  <footer class="footer-bar">
    <div class="footer-inner">
      <div class="footer-actions">
        <a class="btn-ghost" href="#" role="button">View questions</a>
        <a class="btn-solid" href="#" role="button">View offers</a>
      </div>
    </div>
  </footer>
</body>
</html>
