<?php
// UI-only: Offer Sent success page
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0.0;

// Back goes to Code page (in case user wants to re-check)
$backHref = './make-offer-code.php';
$q = [];
if ($id) { $q['id'] = $id; }
if ($amount) { $q['amount'] = number_format($amount, 2, '.', ''); }
if (!empty($q)) { $backHref .= '?' . http_build_query($q); }

// Next -> My Gawain, Offered tab
$nextHref = './my-gawain.php?tab=offered';

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Offer Sent</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <style>
    body { background:#fff; }
    .wrap { max-width: 640px; margin: 0 auto; min-height: 100dvh; display:flex; flex-direction:column; }
    .hdr { position: sticky; top: 0; background:#fff; z-index:2; }
    .top { display:flex; align-items:center; gap:12px; padding: 14px 16px; }
    .back-btn { display:inline-grid; place-items:center; width:38px; height:38px; border-radius:10px; border:2px solid #e2e8f0; color:#0f172a; text-decoration:none; }
    .back-btn:hover { background:#f8fafc; }

    .main { flex:1; display:grid; place-items:center; padding: 12px 16px 140px; text-align:center; }
    .title { font-weight:900; color:#0f172a; font-size: clamp(24px, 6vw, 36px); margin: 2px 0 16px; }
    .hero { margin: 12px 0 18px; }
    .hero svg { width: 160px; height: 160px; display:block; margin: 0 auto; }
    .lead { color:#334155; font-weight:700; margin: 8px 0 14px; }

    .steps { display:grid; gap:16px; text-align:left; max-width: 520px; margin: 0 auto; }
    .step { display:grid; grid-template-columns: 28px 1fr; gap:12px; align-items:start; }
    .bullet { display:grid; place-items:center; width:28px; height:28px; border-radius:999px; background:#0f172a; color:#fff; font-weight:900; }
    .text { color:#0f172a; }

    .tip { text-align:center; color:#f59e0b; font-weight:900; margin: 26px 0 8px; }
    .tip small { color:#0f172a; font-weight:500; display:block; margin-top:8px; }

    .footer { position: fixed; left:50%; transform:translateX(-50%); bottom: 14px; width: min(100% - 24px, 640px); display:grid; }
    .cta { appearance:none; border:0; border-radius:12px; padding:16px; font-weight:900; color:#fff; background:#111827; cursor:pointer; }
  </style>
</head>
<body>
  <div class="wrap">
    <header class="hdr">
      <div class="top">
        <a class="back-btn" href="<?php echo htmlspecialchars($backHref, ENT_QUOTES); ?>" aria-label="Back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <span style="flex:1"></span>
        <span style="width:38px;height:38px;"></span>
      </div>
    </header>

    <main class="main">
      <div>
        <h1 class="title">Offer Sent!</h1>
        <div class="hero" aria-hidden="true">
          <!-- Paper airplane -->
          <svg viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
            <path d="M11 61l104-39-48 84-9-30-30-15z" fill="#38bdf8"/>
            <path d="M115 22L58 66l9 10 48-54z" fill="#0ea5e9"/>
          </svg>
        </div>
        <p class="lead">Here's what's next:</p>
        <div class="steps">
          <div class="step">
            <div class="bullet">1</div>
            <div class="text">If hired, confirm your availability immediately.</div>
          </div>
          <div class="step">
            <div class="bullet">2</div>
            <div class="text">After confirmation, chat with the Citizen to begin the quest.</div>
          </div>
        </div>
        <div class="tip">
          ✨ Pro tip ✨
          <small>Upload a profile picture to stand out! 🖌️</small>
        </div>
      </div>
    </main>

    <div class="footer">
      <a class="cta" href="<?php echo htmlspecialchars($nextHref, ENT_QUOTES); ?>">Next</a>
    </div>
  </div>
</body>
</html>
