<?php
// UI-only: Code of Conduct acknowledgment page
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0.0;
$from = isset($_GET['from']) ? $_GET['from'] : 'confirm';

// Back goes to confirm page by default
$backHref = './make-offer-confirm.php';
$q = [];
if ($id) { $q['id'] = $id; }
if ($amount) { $q['amount'] = number_format($amount, 2, '.', ''); }
if (!empty($q)) { $backHref .= '?' . http_build_query($q); }

// Edit offer -> compose
$editHref = './make-offer-compose.php';
$qe = [];
if ($id) { $qe['id'] = $id; }
if ($amount) { $qe['amount'] = number_format($amount, 2, '.', ''); }
if (!empty($qe)) { $editHref .= '?' . http_build_query($qe); }

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Code of Conduct</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <style>
    body { background:#fff; }
    .wrap { max-width: 640px; margin: 0 auto; min-height: 100dvh; display:flex; flex-direction:column; }
    .hdr { position: sticky; top: 0; background:#fff; z-index:2; }
    .top { display:flex; align-items:center; gap:12px; padding: 14px 16px; }
    .back-btn { display:inline-grid; place-items:center; width:38px; height:38px; border-radius:10px; border:2px solid #e2e8f0; color:#0f172a; text-decoration:none; }
    .back-btn:hover { background:#f8fafc; }
    .top svg { width:18px; height:18px; }
    .title { margin:0; font-weight:900; color:#0f172a; text-align:center; flex:1; }

    .main { flex:1; padding: 12px 16px 140px; }
    .lead { color:#334155; line-height:1.5; }
    .h2 { margin: 18px 0 8px; font-weight:900; color:#0f172a; }
    .list { margin: 0 0 12px 22px; color:#0f172a; }
    .list li { margin: 8px 0; }
    .muted { color:#64748b; }

    .check { display:flex; align-items:flex-start; gap:10px; margin: 16px 0; }
    .check input { width:20px; height:20px; margin-top:2px; }
    .check label { color:#0f172a; }
    .check a { color:#111827; font-weight:900; text-decoration:underline; }

    .footer { position: fixed; left:50%; transform:translateX(-50%); bottom: 14px; width: min(100% - 24px, 640px); display:grid; gap:10px; }
    .cta { appearance:none; border:0; border-radius:12px; padding:16px; font-weight:900; color:#fff; background:#cbd5e1; cursor:default; }
    .cta.enabled { background:#111827; cursor:pointer; }
    .edit { text-align:center; color:#0f172a; text-decoration:underline; font-weight:700; }
  </style>
</head>
<body>
  <div class="wrap">
    <header class="hdr">
      <div class="top">
        <a class="back-btn" href="<?php echo htmlspecialchars($backHref, ENT_QUOTES); ?>" aria-label="Back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <h1 class="title">Code of Conduct</h1>
        <span style="width:38px;height:38px;"></span>
      </div>
    </header>

    <main class="main">
      <p class="lead">At ServisyoHub, we seek to foster a trusted community where heroes monetise their skills and earn flexibly. With great opportunity comes greater responsibility so, by making an offer,</p>

      <h2 class="h2 muted">You agree to:</h2>
      <ul class="list">
        <li>Be professional, responsible, and use ServisyoHub ethically.</li>
        <li>Communicate via the app and respect privacy.</li>
        <li>Reply timely and remain contactable.</li>
        <li>Do an awesome job!</li>
      </ul>

      <h2 class="h2 muted">Cancellations:</h2>
      <ul class="list">
        <li>Canceling a quest may lower your priority for future offers.</li>
        <li>Causing a cancellation or being uncontactable may lead to fees or suspension.</li>
      </ul>

      <div class="check">
        <input type="checkbox" id="c1" />
        <label for="c1">I have read and agree to this code of conduct and ServisyoHub's <a href="./terms-and-conditions.php" target="_blank" rel="noopener">Terms of service</a>.</label>
      </div>
      <div class="check">
        <input type="checkbox" id="c2" />
        <label for="c2">I agree to being charged a <a href="#" onclick="alert('UI-only: fee policy'); return false;">cancellation fee</a> if I cause the quest to be cancelled after my offer's accepted.</label>
      </div>
    </main>

    <div class="footer">
      <button id="agreeBtn" class="cta" disabled>I agree to the terms</button>
      <a class="edit" href="<?php echo htmlspecialchars($editHref, ENT_QUOTES); ?>">Edit my offer</a>
    </div>
  </div>

  <script>
    (function(){
      const c1 = document.getElementById('c1');
      const c2 = document.getElementById('c2');
      const btn = document.getElementById('agreeBtn');

      function upd(){
        const ok = c1.checked && c2.checked;
        btn.disabled = !ok;
        btn.classList.toggle('enabled', ok);
      }

      c1.addEventListener('change', upd);
      c2.addEventListener('change', upd);
      upd();

      btn.addEventListener('click', ()=>{
        if (btn.disabled) return;
        // Navigate to Offer Sent success page, preserving id & amount
        const cur = new URL(window.location.href);
        const next = new URL(window.location.origin + window.location.pathname.replace('make-offer-code.php','make-offer-success.php'));
        if (cur.searchParams.has('id')) next.searchParams.set('id', cur.searchParams.get('id'));
        if (cur.searchParams.has('amount')) next.searchParams.set('amount', cur.searchParams.get('amount'));
        window.location.href = next.pathname + next.search;
      });
    })();
  </script>
</body>
</html>
