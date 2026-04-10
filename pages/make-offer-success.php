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

    .confetti-canvas{
      position: fixed;
      inset: 0;
      pointer-events: none;
      z-index: 0;
    }
    .wrap{ position: relative; z-index: 1; }
  </style>
</head>
<body>
  <canvas id="confettiCanvas" class="confetti-canvas" aria-hidden="true"></canvas>

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

  <script>
    (() => {
      const canvas = document.getElementById('confettiCanvas');
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      if (!ctx) return;

      let w = 0, h = 0, raf = 0;
      const start = performance.now();
      const duration = 4200; // was 2200

      const colors = ['#22c55e','#38bdf8','#f59e0b','#ef4444','#a78bfa'];
      const pieces = Array.from({ length: 90 }, () => ({
        x: Math.random(),
        y: -Math.random() * 0.5,
        vx: (Math.random() - 0.5) * 0.007, // slower horizontal drift
        vy: 0.006 + Math.random() * 0.008, // slower initial fall speed
        s: 4 + Math.random() * 5,
        a: Math.random() * Math.PI,
        va: (Math.random() - 0.5) * 0.18,  // slower spin
        c: colors[(Math.random() * colors.length) | 0]
      }));

      function resize(){
        w = canvas.width = window.innerWidth;
        h = canvas.height = window.innerHeight;
      }

      function draw(t){
        ctx.clearRect(0,0,w,h);
        const active = (t - start) < duration;

        for (const p of pieces){
          p.vy += 0.00012; // was 0.00025
          p.x += p.vx;
          p.y += p.vy;
          p.a += p.va;

          const px = p.x * w, py = p.y * h;
          ctx.save();
          ctx.translate(px, py);
          ctx.rotate(p.a);
          ctx.fillStyle = p.c;
          ctx.fillRect(-p.s/2, -p.s/2, p.s, p.s * 0.7);
          ctx.restore();

          if (active && (p.y > 1.1 || p.x < -0.1 || p.x > 1.1)){
            p.x = Math.random();
            p.y = -0.1;
            p.vx = (Math.random() - 0.5) * 0.007;
            p.vy = 0.006 + Math.random() * 0.008;
          }
        }

        if (active) raf = requestAnimationFrame(draw);
        else ctx.clearRect(0,0,w,h);
      }

      resize();
      window.addEventListener('resize', resize, { passive:true });
      raf = requestAnimationFrame(draw);
    })();
  </script>
</body>
</html>
