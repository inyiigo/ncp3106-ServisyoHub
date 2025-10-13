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
</head>
<body>
  <div class="dash-topbar">
    <div class="dash-brand">Servisyo Hub</div>
    <div class="dash-top-spacer"></div>
    <div class="dash-avatar" title="<?php echo htmlspecialchars($display); ?>"><?php echo htmlspecialchars($avatar); ?></div>
  </div>
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
</body>
</html>
