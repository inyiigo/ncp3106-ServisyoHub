<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Servisyo Hub</title>
  <link rel="preload" as="image" href="assets/images/logo.png">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="splash-body">
  <main class="splash" id="splash">
    <div class="splash-content">
      <img src="assets/images/job_logo.png" alt="Servisyo Hub Logo" class="splash-logo">
      <h1 class="splash-title">Servisyo Hub</h1>
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
      // Start slide-up animation after a 3s delay
      setTimeout(function () {
        splash.classList.add('slide-up');
      }, 3000);

      // Navigate to user-choice after animation completes
      const animationDurationMs = 900; // keep in sync with CSS
      setTimeout(function () {
        if (progress) { progress.setAttribute('aria-valuenow', '100'); }
        window.location.href = 'pages/user-choice.php';
      }, 3000 + animationDurationMs);
    });
  </script>
</body>
</html>