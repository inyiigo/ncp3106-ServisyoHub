<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Servisyo Hub</title>
  <link rel="preload" as="image" href="assets/images/bluefont.png">
  <!-- Warm up next page assets for a smoother transition -->
  <link rel="prefetch" href="pages/login.php" />
  <link rel="preload" as="image" href="assets/images/login background.png" />
  <link rel="preload" as="image" href="assets/images/newlogo.png" />
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    /* Subtle fade during splash slide-up so navigation feels smooth */
    body.fade-out { opacity: 0; transition: opacity 320ms ease; }
  </style>
</head>
<body class="splash-body">
  <main class="splash" id="splash">
    <div class="splash-content">
  <img src="assets/images/bluefont.png" alt="Servisyo Hub" class="splash-logo">
  <div class="progress-wrap" role="progressbar" aria-label="Loading" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <div class="progress-track">
          <div class="progress-bar"></div>
        </div>
      </div>
    </div>
  </main>

  <script src="assets/js/script.js"></script>
  <script>
    window.addEventListener('load', function() {
      const splash = document.getElementById('splash');
      const progress = document.querySelector('.progress-wrap');
      if (progress) { progress.setAttribute('aria-valuenow', '0'); }
      // Start slide-up animation after a 3s delay, and fade page slightly
      setTimeout(function () {
        splash.classList.add('slide-up');
        // Start a brief fade to avoid a hard cut on navigation
        document.body.classList.add('fade-out');
      }, 3000);

      // Navigate to user-choice after animation completes
      const animationDurationMs = 900; // keep in sync with CSS
      setTimeout(function () {
        if (progress) { progress.setAttribute('aria-valuenow', '100'); }
        try { sessionStorage.setItem('fromSplash', '1'); } catch(e) { /* ignore */ }
        window.location.href = 'pages/login.php';
      }, 3000 + animationDurationMs);
    });
  </script>
</body>
</html>