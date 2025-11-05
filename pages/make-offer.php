<?php
// UI-only Make Offer page
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/db_connect.php';

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fallback_date_label($raw){ return $raw ? $raw : 'Anytime'; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$job = null;
if ($id > 0 && isset($conn) && $conn) {
  if ($stmt = @mysqli_prepare($conn, "SELECT id, COALESCE(location,'') AS location, COALESCE(date_needed,'') AS date_needed FROM jobs WHERE id=? LIMIT 1")){
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (@mysqli_stmt_execute($stmt)){
      $res = @mysqli_stmt_get_result($stmt);
      $job = @mysqli_fetch_assoc($res) ?: null;
    }
    @mysqli_stmt_close($stmt);
  }
}
if (!$job) {
  $job = [ 'id' => 0, 'location' => 'Online', 'date_needed' => '' ];
}
$loc = trim((string)$job['location']) !== '' ? $job['location'] : 'Online';
$dateLabel = fallback_date_label($job['date_needed']);
$backHref = './gawain-detail.php' . ($id ? ('?id='.(int)$id) : '');
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Make an offer • Servisyo Hub</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <style>
    body { background:#fff; }
    .offer-wrap { max-width: 640px; margin: 0 auto; min-height: 100dvh; display:flex; flex-direction:column; }
    .offer-header { position: sticky; top: 0; background:#fff; border-bottom: 0; z-index: 2; }
    .offer-top { display:flex; align-items:center; gap:12px; padding: 14px 16px; }
    .back-btn { display:inline-grid; place-items:center; width:38px; height:38px; border-radius:10px; border:2px solid #e2e8f0; color:#0f172a; text-decoration:none; }
    .back-btn:hover { background:#f8fafc; }
    .offer-title { margin:0; font-weight:900; color:#0f172a; text-align:center; flex:1; }

    .offer-main { flex:1; padding: 14px 16px 120px; }
    .offer-h1 { font-size: clamp(20px, 4.5vw, 28px); font-weight:900; color:#0f172a; margin: 12px 0 14px; }
    .offer-cards { display:grid; gap:14px; }
    .confirm-card { display:grid; grid-template-columns: 1fr auto; gap:12px; align-items:center; background:#fff; border:2px solid #e2e8f0; border-radius:14px; padding:14px; box-shadow: 0 4px 12px rgba(0,0,0,.04); cursor:pointer; }
    .confirm-title { color:#64748b; font-weight:700; margin:0 0 6px; }
    .confirm-value { margin:0; font-weight:900; color:#0f172a; font-size: 1.05rem; }
    .checkbox { width:22px; height:22px; border-radius:6px; border:2px solid #94a3b8; background:#fff; display:grid; place-items:center; color:#fff; transition: all .15s ease; }
    .checkbox svg { width:16px; height:16px; display:block; opacity:0; transform: scale(.8); transition: opacity .12s ease, transform .12s ease; }
    .confirm-card[aria-checked="true"] .checkbox { background:#111827; border-color:#111827; color:#fff; }
    .confirm-card[aria-checked="true"] .checkbox svg { opacity:1; transform: scale(1); }

    .policy { display:flex; gap:12px; align-items:flex-start; padding:12px 14px; border-radius:12px; background:#f8fafc; border:2px solid #e2e8f0; color:#0f172a; }
    .policy .ico { width:18px; height:18px; color:#f59e0b; margin-top:2px; }
    .policy b { display:block; margin-bottom:2px; }
    .policy a { color:#ef4444; font-weight:800; text-decoration:underline; }

    .offer-footer { position: fixed; left:50%; transform:translateX(-50%); bottom: 14px; width: min(100% - 24px, 640px); display:grid; gap:12px; background:transparent; }
    .cta { appearance:none; border:0; border-radius:12px; padding:14px 16px; font-weight:900; color:#fff; background:#111827; cursor:pointer; box-shadow: 0 8px 20px rgba(0,0,0,.12); }
    .cta[disabled] { background:#cbd5e1; color:#fff; cursor:default; box-shadow:none; }
  </style>
</head>
<body>
  <div class="offer-wrap">
    <header class="offer-header">
      <div class="offer-top">
        <a class="back-btn" href="<?php echo e($backHref); ?>" aria-label="Back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <h1 class="offer-title">Make an offer</h1>
        <span style="width:38px; height:38px;"></span>
      </div>
    </header>

    <main class="offer-main">
      <h2 class="offer-h1">Before offering, please confirm the following:</h2>

      <div class="offer-cards">
        <div class="confirm-card" id="c1" role="checkbox" aria-checked="false" tabindex="0">
          <div>
            <p class="confirm-title">I've checked the quest location, and have no issues</p>
            <p class="confirm-value"><?php echo e($loc); ?></p>
          </div>
          <div class="checkbox" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
          </div>
        </div>

        <div class="confirm-card" id="c2" role="checkbox" aria-checked="false" tabindex="0">
          <div>
            <p class="confirm-title">I'm available to complete the quest</p>
            <p class="confirm-value">On <?php echo e($dateLabel); ?></p>
          </div>
          <div class="checkbox" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
          </div>
        </div>
      </div>

      <div style="height:16px"></div>
      <div class="policy">
        <svg class="ico" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14h-2v-2h2v2zm0-4h-2V6h2v6z"/></svg>
        <div>
          <b>Cancellation policy</b>
          <div>We review cancellations carefully and charge a cancellation fee if a party is found responsible. <a href="#">Learn more</a></div>
        </div>
      </div>
    </main>

    <div class="offer-footer">
      <button class="cta" id="offerCta" disabled>I meet the necessary conditions</button>
    </div>
  </div>

  <script>
    (function(){
      const c1 = document.getElementById('c1');
      const c2 = document.getElementById('c2');
      const btn = document.getElementById('offerCta');
      const nextUrl = './make-offer-tips.php' + <?php echo json_encode($id ? ('?id='.(int)$id) : ''); ?>;
      function toggle(card){
        const v = card.getAttribute('aria-checked') === 'true';
        card.setAttribute('aria-checked', (!v).toString());
        update();
      }
      function update(){
        const ok = c1.getAttribute('aria-checked') === 'true' && c2.getAttribute('aria-checked') === 'true';
        btn.disabled = !ok;
      }
      [c1,c2].forEach(card => {
        card.addEventListener('click', ()=> toggle(card));
        card.addEventListener('keydown', (e)=>{ if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); toggle(card); } });
      });
      btn.addEventListener('click', ()=>{ window.location.href = nextUrl; });
    })();
  </script>
</body>
</html>
