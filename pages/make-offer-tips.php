<?php
// UI-only tips page after confirming conditions
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$backHref = './make-offer.php' . ($id ? ('?id='.(int)$id) : '');
$continueHref = '#'; // Placeholder for next step (e.g., compose offer)
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Make an offer • Tips</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <style>
    body { background:#fff; }
    .wrap { max-width: 640px; margin: 0 auto; min-height: 100dvh; display:flex; flex-direction:column; }
    .hdr { position: sticky; top: 0; background:#fff; z-index:2; }
    .top { display:flex; align-items:center; gap:12px; padding: 14px 16px; }
    .back-btn { display:inline-grid; place-items:center; width:38px; height:38px; border-radius:10px; border:2px solid #e2e8f0; color:#0f172a; text-decoration:none; }
    .back-btn:hover { background:#f8fafc; }
    .title { margin:0; font-weight:900; color:#0f172a; text-align:center; flex:1; }

    .main { flex:1; padding: 16px; }
    .h1 { text-align:center; font-weight:900; font-size: clamp(22px, 5.2vw, 32px); color:#0f172a; margin: 18px 0 10px; }
    .hero { display:grid; place-items:center; margin: 12px 0 10px; }
    .hero svg { width: 160px; height: 160px; display:block; }

  .tips { display:grid; gap:22px; margin-top: 12px; }
  .tip { display:block; }
    .tip .t1 { margin:0 0 6px; font-weight:900; color:#0f172a; font-size: 20px; }
    .tip .t2 { margin:0; color:#475569; }

    .footer { position: fixed; left:50%; transform:translateX(-50%); bottom: 14px; width: min(100% - 24px, 640px); }
    .cta { appearance:none; border:0; width:100%; border-radius:12px; padding:16px; font-weight:900; color:#fff; background:#111827; cursor:pointer; box-shadow: 0 8px 20px rgba(0,0,0,.12); }
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
      <h2 class="h1">How to make a great offer?</h2>
      <div class="hero" aria-hidden="true">
        <!-- Simple inline illustration -->
        <svg viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stop-color="#e0f2fe"/>
              <stop offset="1" stop-color="#f0f9ff"/>
            </linearGradient>
          </defs>
          <circle cx="64" cy="64" r="44" fill="url(#g1)"/>
          <rect x="44" y="32" width="40" height="48" rx="6" fill="#60a5fa"/>
          <rect x="48" y="40" width="24" height="6" rx="3" fill="#fcd34d"/>
          <rect x="48" y="52" width="28" height="4" rx="2" fill="#dbeafe"/>
          <rect x="48" y="60" width="28" height="4" rx="2" fill="#dbeafe"/>
          <rect x="48" y="68" width="20" height="4" rx="2" fill="#dbeafe"/>
          <circle cx="40" cy="44" r="4" fill="#f59e0b"/>
          <circle cx="86" cy="30" r="3" fill="#f59e0b"/>
        </svg>
      </div>

      <section class="tips" aria-label="Tips">
        <div class="tip">
          <h3 class="t1">Get clarity first</h3>
          <p class="t2">Not sure about the details? Ask the Citizen a question before you make your offer.</p>
        </div>
        <div class="tip">
          <h3 class="t1">Make your offer stand out</h3>
          <p class="t2">Set your price and explain why you're the best fit for the quest. <em>(Tip: you can always edit your offer later)</em></p>
        </div>
      </section>
    </main>

    <div class="footer">
      <button class="cta" id="tipsCta">I understand, continue</button>
    </div>
  </div>

  <script>
    (function(){
      const btn = document.getElementById('tipsCta');
      btn.addEventListener('click', ()=>{
        // UI-only: placeholder for next step (e.g., compose offer)
        alert('UI-only: Next step coming soon.');
      });
    })();
  </script>
</body>
</html>
