<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>You are here for • Servisyo Hub</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <style>
    /* Equal-sized buttons with in-button descriptions (no extra space) */
    .choice-buttons .choice-item { display: grid; justify-items: stretch; }
    .choice-buttons .choice-item .choice-btn {
      position: relative;
      display: grid;
      gap: 0;                 /* no gap between children */
      place-items: center;
      place-content: center;
      width: 100%;
      min-height: 64px;      /* smaller box (was 84px) */
      padding: 8px 12px;     /* tighter padding */
      text-align: center;
      overflow: hidden;       /* keep overlay clean */
    }

    /* Overlay both texts centered; show one at a time */
    .choice-btn .btn-title,
    .choice-btn .btn-desc {
      position: absolute;
      inset: 0;                          /* fill button */
      display: flex;
      align-items: center;
      justify-content: center;           /* center text perfectly */
      padding: 0 8px;                    /* guard against overflow */
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
      margin: 0;
      line-height: 1.2;                 /* bump line-height slightly for the larger font sizes */
    }

    /* Title visible by default */
    .choice-btn .btn-title {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
      transition: opacity .2s ease, transform .2s ease, visibility 0s linear 0s;
      font-size: 1.06rem;     /* slightly bigger title */
    }
    .choice-btn:hover .btn-title,
    .choice-btn:focus-visible .btn-title {
      opacity: 0;
      visibility: hidden;
      transform: translateY(-4px);
      transition: opacity .2s ease, transform .2s ease, visibility 0s linear .2s;
    }

    /* Description hidden by default; appears centered on hover */
    .choice-btn .btn-desc {
      opacity: 0;
      visibility: hidden;
      transform: translateY(4px);
      transition: opacity .2s ease, transform .2s ease, visibility 0s linear .2s;
      color: #fff; /* readable on both button variants */
      font-size: .92rem;      /* slightly bigger description */
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
<body class="choice-body theme-profile-bg">
  <main class="choice-container">
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
  </main>
</body>
</html>

