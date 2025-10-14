<?php
$title = $title ?? 'Service';
$subtitle = $subtitle ?? '';
$ctaHref = $ctaHref ?? '../home-services.php';
session_start();
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'there');
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($title); ?> • Servisyo Hub</title>
  <link rel="stylesheet" href="../../assets/css/styles.css" />
  <script defer src="../../assets/js/script.js"></script>
</head>
<body>
  <div class="dash-topbar center">
    <div class="dash-brand">Servisyo Hub</div>
  </div>
  <div class="dash-overlay"></div>
  <div class="dash-shell">
    <main class="dash-content">
      <h1><?php echo htmlspecialchars($title); ?></h1>
      <?php if ($subtitle): ?><p class="dash-muted"><?php echo htmlspecialchars($subtitle); ?></p><?php endif; ?>
      <section class="card">
        <p class="dash-muted">This is a placeholder for the <?php echo htmlspecialchars($title); ?> service page. Put service-specific content here (pricing, FAQs, booking button, etc.).</p>
        <p><a class="btn" href="<?php echo htmlspecialchars($ctaHref); ?>">Back to Services</a></p>
      </section>
    </main>
    <aside class="dash-aside">
      <nav class="dash-nav">
        <a href="../home-services.php">Home</a>
        <a href="../my-services.php">My Services</a>
        <a href="../profile.php">Profile</a>
      </nav>
    </aside>
  </div>
  <!-- Floating bottom navigation -->
  <nav class="dash-bottom-nav">
    <a href="../home-services.php" class="active" aria-label="Home">
      <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
      <span>Home</span>
    </a>
    <a href="../my-services.php" aria-label="My Services">
      <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
      <span>My Services</span>
    </a>
    <a href="../profile.php" aria-label="Profile">
      <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
      <span>Profile</span>
    </a>
  </nav>
</body>
</html>
