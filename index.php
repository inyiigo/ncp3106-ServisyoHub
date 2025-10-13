<?php
// Title page for Servisyo Hub
session_start();

$pageTitle = "Welcome to Servisyo Hub";
$bodyClass = "title-page";
?>
<style src="assets/css/styles.css"></style>

<?php include 'includes/header.php'; ?>

<main id="main-content">
    <!-- Title Page Hero Section -->
    <section class="title-hero">
        <div class="container">
            <div class="title-content">
                <div class="title-logo">
                    <div class="logo-icon">🔧</div>
                    <h1 class="main-title">Welcome to Servisyo Hub</h1>
                </div>
                
                <p class="subtitle">Where skilled hands meet local demand.</p>
                
                <div class="title-actions">
                    <a href="pages/user-choice.php" class="btn btn-accent btn-large">Continue</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="assets/js/main.js"></script>
<?php
// Landing splash -> goes to user-choice
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Servisyo Hub</title>
<style>
  html,body{height:100%;margin:0;font-family:system-ui,Segoe UI,Roboto,Arial;}
  .splash{height:100vh;display:flex;align-items:center;justify-content:center;background:#fbfbf9;transition:transform .8s ease,opacity .6s ease}
  .splash.hidden{transform:translateY(-120%);opacity:0}
  .brand{text-align:center}
  .brand h1{font-size:4rem;margin:0}
  .brand p{margin-top:1rem;color:#666}
</style>
</head>
<body>
  <div id="splash" class="splash">
    <div class="brand">
      <h1>Servisyo Hub</h1>
      <p>Where skilled hands meet local demand.</p>
    </div>
  </div>

<script>
(function(){
  var splash = document.getElementById('splash');
  setTimeout(function(){
    splash.classList.add('hidden');
    setTimeout(function(){ window.location.href = 'pages/user-choice.php'; }, 800);
  }, 1400);
})();
</script>
</body>
</html>