<?php
// UI-only: Compose offer page
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$backHref = './make-offer.php' . ($id ? ('?id='.(int)$id) : '');
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Make an offer • Compose</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <style>
    body { background:#fff; }
    .wrap { max-width: 640px; margin: 0 auto; min-height: 100dvh; display:flex; flex-direction:column; }
    .hdr { position: sticky; top: 0; background:#fff; z-index:2; }
    .top { display:flex; align-items:center; gap:12px; padding:14px 16px; }
    .back-btn { display:inline-grid; place-items:center; width:38px; height:38px; border-radius:10px; border:2px solid #e2e8f0; color:#0f172a; text-decoration:none; }
    .back-btn:hover { background:#f8fafc; }
    .top svg { width:18px; height:18px; }
    .title { margin:0; font-weight:900; color:#0f172a; text-align:center; flex:1; }

    .main { flex:1; padding: 10px 16px 120px; }
    .h1 { margin: 12px 0 4px; color:#0f172a; font-weight:900; font-size: 20px; }
    .sub { margin:0 0 14px; color:#475569; }

    .amount-wrap { position: relative; border:2px solid #e11d48; border-radius:14px; padding: 16px; display:flex; align-items:center; gap:10px; }
    .currency { font-weight:900; color:#ef4444; font-size: 20px; }
    .amount { appearance:none; border:0; outline:0; background:transparent; flex:1; font: inherit; font-weight:900; color:#94a3b8; font-size: 20px; }
    .amount::placeholder { color:#cbd5e1; }

    .error { color:#ef4444; font-size:.9rem; margin:8px 4px 0; }

    .card { margin-top:20px; background:#fff; border:2px solid #e2e8f0; border-radius:14px; padding:14px; box-shadow: 0 4px 12px rgba(0,0,0,.04); }
    .row { display:grid; grid-template-columns: 1fr auto; align-items:center; gap:8px; padding:8px 0; }
    .muted { color:#64748b; }
    .sep { border-top: 2px dashed #cbd5e1; margin: 8px 0; }
    .earn { color:#10b981; font-weight:900; }
    .info-ico { display:inline-grid; place-items:center; width:18px; height:18px; border-radius:999px; border:2px solid #cbd5e1; font-size: 12px; color:#64748b; }

    

    .footer { position: fixed; left:50%; transform:translateX(-50%); bottom: 14px; width: min(100% - 24px, 640px); display:grid; gap:10px; }
    .cta { appearance:none; border:0; border-radius:12px; padding:16px; font-weight:900; color:#fff; background:#cbd5e1; cursor:default; }
    .cta.enabled { background:#111827; cursor:pointer; }
  </style>
</head>
<body>
  <div class="wrap">
    <header class="hdr">
      <div class="top">
  <a class="back-btn" href="<?php echo htmlspecialchars($backHref, ENT_QUOTES); ?>" aria-label="Back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <h1 class="title">Make an offer</h1>
        <span style="width:38px;height:38px;"></span>
      </div>
    </header>

    <main class="main">
      <h2 class="h1">Your offer amount</h2>
      <p class="sub">Your offer amount should cover everything needed to get the quest done, including any purchases.</p>

      <div class="amount-wrap" id="amountWrap">
        <div class="currency">PHP</div>
        <input id="amount" class="amount" type="text" placeholder="100.00" inputmode="decimal" autocomplete="off" />
      </div>
      <p class="error" id="amountError" hidden>Offer must be at least PHP80 to meet the minimum offer requirement and meet the minimum quest fee.</p>

      <div class="card" aria-label="Offer breakdown">
        <div class="row"><span class="muted">Your offer</span><span id="rowOffer">PHP0.00</span></div>
        <div class="row"><span class="muted">Service fee <span class="info-ico">i</span></span><span id="rowFee">-PHP0.00</span></div>
        <div class="sep"></div>
        <div class="row"><span class="muted">You'll earn</span><span id="rowEarn" class="earn">PHP0.00</span></div>
      </div>

      
    </main>

    <div class="footer">
      <button id="continueBtn" class="cta" disabled>Continue</button>
    </div>
  </div>

  <script>
    (function(){
      const input = document.getElementById('amount');
      const wrap = document.getElementById('amountWrap');
      const err = document.getElementById('amountError');
      const btn = document.getElementById('continueBtn');
      const rowOffer = document.getElementById('rowOffer');
      const rowFee = document.getElementById('rowFee');
      const rowEarn = document.getElementById('rowEarn');
      const MIN = 80.00; // UI-only min
      const FEE_RATE = 0.10; // 10% service fee (demo)

      function format(n){ return 'PHP' + n.toFixed(2); }

      function parseVal(){
        const v = (input.value || '').replace(/[^0-9.]/g,'');
        const n = parseFloat(v);
        return isNaN(n) ? 0 : n;
      }

      function getParam(name){
        const usp = new URLSearchParams(window.location.search);
        return usp.get(name);
      }

      function setParam(url, key, value){
        const u = new URL(url, window.location.origin);
        if (value === null || value === undefined || value === '') u.searchParams.delete(key);
        else u.searchParams.set(key, value);
        return u.pathname + (u.search ? u.search : '');
      }

      function update(){
        const amt = parseVal();
        const fee = Math.max(0, amt * FEE_RATE);
        const earn = Math.max(0, amt - fee);

        rowOffer.textContent = format(amt || 0);
        rowFee.textContent = '-' + format(fee || 0);
        rowEarn.textContent = format(earn || 0);

        const valid = amt >= MIN;
        err.hidden = valid;
        wrap.style.borderColor = valid ? '#e2e8f0' : '#e11d48';
        input.style.color = valid ? '#0f172a' : '#ef4444';

        if (valid){ btn.classList.add('enabled'); btn.disabled = false; }
        else { btn.classList.remove('enabled'); btn.disabled = true; }
      }

      input.addEventListener('input', update);
      input.addEventListener('blur', ()=>{
        const n = parseVal();
        if (!isNaN(n)) input.value = n ? n.toFixed(2) : '';
        update();
      });

      // Initialize with optional prefill from query ?amount=
      const qAmt = parseFloat((getParam('amount') || '').replace(/[^0-9.]/g,''));
      if (!isNaN(qAmt) && qAmt > 0){
        input.value = qAmt.toFixed(2);
      }
      update();

      btn.addEventListener('click', ()=>{
        if (btn.disabled) return;
        const amt = parseVal();
        // Navigate to confirm page, preserving id and passing amount
        const next = new URL(window.location.origin + window.location.pathname.replace('make-offer-compose.php','make-offer-confirm.php'));
        const cur = new URL(window.location.href);
        if (cur.searchParams.has('id')) next.searchParams.set('id', cur.searchParams.get('id'));
        next.searchParams.set('amount', amt.toFixed(2));
        window.location.href = next.pathname + next.search;
      });
    })();
  </script>
</body>
</html>
