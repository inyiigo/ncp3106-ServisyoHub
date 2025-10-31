<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>You are here for • Servisyo Hub</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <style>
    /* Page bg */
    body.theme-profile-bg { background: #ffffff !important; }

    /* Background logo behind content */
    .bg-logo {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 25%;
      max-width: 350px;
      opacity: 0.15;
      z-index: 0;
      pointer-events: none;
    }
    .bg-logo img { width: 100%; height: auto; display: block; }

    /* Center everything */
    .choice-wrap {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 16px;
      box-sizing: border-box;
      position: relative;
      z-index: 1; /* ensure above bg-logo */
    }

    /* Blue glass card */
    .choice-card.form-card.glass-card {
      background: #0078a6 !important;
      color: #fff;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 8px 24px rgba(0,120,166,.24);
      border: 2px solid color-mix(in srgb, #0078a6 80%, #0000);
      width: 100%;
      max-width: 720px;
    }
    .choice-heading { margin: 0 0 12px; text-align: center; color: #fff; }

    /* Grid buttons */
    .choice-buttons { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    @media (max-width:640px){ .choice-buttons { grid-template-columns: 1fr; } }

    /* Buttons with overlay text behavior */
    .choice-buttons .choice-item { display: grid; justify-items: stretch; }
    .choice-buttons .choice-item .choice-btn {
      position: relative;
      display: grid;
      gap: 0;
      place-items: center;
      place-content: center;
      width: 100%;
      min-height: 72px;
      padding: 8px 12px;
      text-align: center;
      overflow: hidden;

      background: rgba(255,255,255,.15);
      color: #fff;
      border: 2px solid rgba(255,255,255,.5);
      border-radius: 12px;
      text-decoration: none;
      font-weight: 800;
      transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .choice-buttons .choice-item .choice-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0,0,0,.2);
      background: #0078a6;
    }

    /* Overlay texts */
    .choice-btn .btn-title,
    .choice-btn .btn-desc {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 8px;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
      margin: 0;
      line-height: 1.2;
    }

    .choice-btn .btn-title {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
      transition: opacity .2s ease, transform .2s ease, visibility 0s linear 0s;
      font-size: 1.06rem;
      color: #fff;
    }
    .choice-btn:hover .btn-title,
    .choice-btn:focus-visible .btn-title {
      opacity: 0;
      visibility: hidden;
      transform: translateY(-4px);
      transition: opacity .2s ease, transform .2s ease, visibility 0s linear .2s;
    }

    .choice-btn .btn-desc {
      opacity: 0;
      visibility: hidden;
      transform: translateY(4px);
      transition: opacity .2s ease, transform .2s ease, visibility 0s linear .2s;
      color: #fff;
      font-size: .92rem;
      pointer-events: none;
    }
    .choice-btn:hover .btn-desc,
    .choice-btn:focus-visible .btn-desc {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
      transition: opacity .2s ease, transform .2s ease, visibility 0s linear 0s;
    }
  </style>
</head>
<body class="theme-profile-bg">
  <!-- Background Logo -->
  <div class="bg-logo">
    <img src="../assets/images/job_logo.png" alt="" />
  </div>

  <main class="choice-wrap">
    <article class="choice-card form-card glass-card">
      <h2 class="choice-heading">You are here for</h2>
      <div class="choice-buttons">
        <div class="choice-item">
          <a href="./login.php" class="choice-btn">
            <span class="btn-title">A Service</span>
            <span class="btn-desc">Find a client and book services</span>
          </a>
        </div>
        <div class="choice-item">
          <a href="./job-login.php" class="choice-btn alt">
            <span class="btn-title">A Job</span>
            <span class="btn-desc">Find a job to apply</span>
          </a>
        </div>
      </div>
    </article>
  </main>
</body>
</html>

