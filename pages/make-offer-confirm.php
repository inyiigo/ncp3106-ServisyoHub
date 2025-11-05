<?php
// UI-only: Confirm offer details page (no screening answers)
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0.0;
if ($amount < 0) { $amount = 0; }
$feeRate = 0.10; // 10% (demo)
$fee = round($amount * $feeRate, 2);
$earn = max(0, round($amount - $fee, 2));

// Back navigates to compose, preserving params
$backHref = './make-offer-compose.php';
$q = [];
if ($id) { $q['id'] = $id; }
if ($amount) { $q['amount'] = number_format($amount, 2, '.', ''); }
if (!empty($q)) { $backHref .= '?' . http_build_query($q); }

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Confirm offer details</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <style>
    body { background:#fff; }
    .wrap { max-width: 640px; margin:0 auto; min-height:100dvh; display:flex; flex-direction:column; }
    .hdr { position:sticky; top:0; background:#fff; z-index:2; }
    .top { display:flex; align-items:center; gap:12px; padding:14px 16px; }
    .back-btn { display:inline-grid; place-items:center; width:38px; height:38px; border-radius:10px; border:2px solid #e2e8f0; color:#0f172a; text-decoration:none; }
    .back-btn:hover { background:#f8fafc; }
    .title { margin:0; font-weight:900; color:#0f172a; text-align:center; flex:1; }

    .main { flex:1; padding:16px 16px 120px; }
    .grid { display:grid; grid-template-columns: 1fr auto; gap:8px; align-items:center; }
    .label { color:#0f172a; }
    .value { color:#0f172a; font-weight:900; }
    .muted { color:#64748b; }
    .sep { height:12px; }

    .preview-note { color:#94a3b8; font-style:italic; margin: 18px 0 10px; }
    .preview { background:#f8fafc; border:2px solid #e2e8f0; border-radius:14px; padding:14px; display:grid; grid-template-columns:48px 1fr; gap:14px; align-items:center; }
    .avatar { width:48px; height:48px; border-radius:999px; background:#e2e8f0; overflow:hidden; }
    .avatar img { width:100%; height:100%; object-fit:cover; display:block; }
    .pv-lines { display:grid; gap:10px; }
    .pv-row { display:grid; grid-template-columns: 1fr auto; align-items:center; gap:8px; }
    .pv-label { color:#334155; }
    .pv-value { color:#0f172a; font-weight:600; }
    .info-ico { display:inline-grid; place-items:center; width:18px; height:18px; border-radius:999px; border:2px solid #cbd5e1; font-size:12px; color:#64748b; }

    .footer { position: fixed; left:50%; transform:translateX(-50%); bottom:14px; width:min(100% - 24px, 640px); display:grid; gap:8px; }
    .cta { appearance:none; border:0; border-radius:12px; padding:16px; font-weight:900; color:#fff; background:#ef4444; cursor:pointer; box-shadow: 0 8px 20px rgba(0,0,0,.12); }
    .link { text-align:center; color:#0f172a; font-weight:700; text-decoration:underline; }
  </style>
</head>
<body>
  <div class="wrap">
    <header class="hdr">
      <div class="top">
        <a class="back-btn" href="<?php echo htmlspecialchars($backHref, ENT_QUOTES); ?>" aria-label="Back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <h1 class="title">Confirm offer details</h1>
        <span style="width:38px;height:38px;"></span>
      </div>
    </header>

    <main class="main">
      <div class="grid">
        <div class="label">Asking amount:</div>
        <div class="value">PHP<?php echo number_format($amount, 2); ?></div>
      </div>
      <div class="grid" style="margin-top:10px;">
        <div class="label">Service fee: <span class="info-ico" title="Includes platform costs">i</span></div>
        <div class="value">-PHP<?php echo number_format($fee, 2); ?></div>
      </div>
      <div class="grid" style="margin-top:10px;">
        <div class="label">You’ll earn:</div>
        <div class="value">PHP<?php echo number_format($earn, 2); ?></div>
      </div>

      <div class="preview-note">This is how your offer will appear to the Citizen:</div>
      <div class="preview">
        <div class="avatar">
          <img src="../assets/images/services/default-user.jpg" alt="Your avatar" onerror="this.style.display='none'"/>
        </div>
        <div class="pv-lines">
          <div class="pv-row"><div class="pv-label">Overall rating</div><div class="pv-value">New user</div></div>
          <div class="pv-row"><div class="pv-label">Hero completion rate</div><div class="pv-value">New user</div></div>
        </div>
      </div>

      <div class="sep"></div>
    </main>

    <div class="footer">
      <button class="cta" id="confirmBtn">Make offer</button>
      <a class="link" id="editLink" href="#">Edit offer</a>
    </div>
  </div>

  <script>
    (function(){
      const confirmBtn = document.getElementById('confirmBtn');
      const editLink = document.getElementById('editLink');
      const cur = new URL(window.location.href);
      // Build compose URL to edit
      const composeUrl = new URL(window.location.origin + window.location.pathname.replace('make-offer-confirm.php','make-offer-compose.php'));
      if (cur.searchParams.has('id')) composeUrl.searchParams.set('id', cur.searchParams.get('id'));
      if (cur.searchParams.has('amount')) composeUrl.searchParams.set('amount', cur.searchParams.get('amount'));
      editLink.href = composeUrl.pathname + composeUrl.search;

      confirmBtn.addEventListener('click', ()=>{
        // UI-only: no backend calls. Show placeholder.
        alert('UI-only: Offer submitted.');
      });
    })();
  </script>
</body>
</html>
