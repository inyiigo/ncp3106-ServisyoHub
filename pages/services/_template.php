<?php
$title = $title ?? 'Service';
$subtitle = $subtitle ?? '';
$ctaHref = $ctaHref ?? './home-services.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($title); ?> • Servisyo Hub</title>
  <link rel="stylesheet" href="../../assets/css/styles.css" />
  <style>
    :root{ --bg:#f6f8fb; --text:#0f172a; --muted:#475569; --card:#ffffff; --line:#e2e8f0; --brand:#111827; --shadow:0 10px 25px rgba(2,6,23,.08); }
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);color:var(--text)}
    .topbar{position:sticky;top:0;z-index:10;display:flex;align-items:center;gap:12px;background:#fff;border-bottom:1px solid var(--line);padding:14px 20px}
    .brand{font-weight:800}
    .shell{display:grid;grid-template-columns:1fr 280px;min-height:calc(100vh - 56px)}
    .content{padding:24px}
    .card{background:var(--card);border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:18px}
    .muted{color:var(--muted)}
    .aside{border-left:1px solid var(--line);background:var(--card)}
    .nav{position:sticky;top:56px;padding:18px 16px;display:grid;gap:8px}
    .nav a{display:block;padding:10px 12px;border-radius:10px;color:var(--text);text-decoration:none;font-weight:700}
    .nav a:hover,.nav a.active{background:var(--brand);color:#fff}
    @media (max-width: 900px){.shell{grid-template-columns:1fr}.aside{order:-1;border-left:none;border-bottom:1px solid var(--line)}}
  </style>
</head>
<body>
  <div class="topbar"><div class="brand">Servisyo Hub</div></div>
  <div class="shell">
    <main class="content">
      <h1><?php echo htmlspecialchars($title); ?></h1>
      <?php if ($subtitle): ?><p class="muted"><?php echo htmlspecialchars($subtitle); ?></p><?php endif; ?>
      <section class="card">
        <p class="muted">This is a placeholder for the <?php echo htmlspecialchars($title); ?> service page. Put service-specific content here (pricing, FAQs, booking button, etc.).</p>
        <p><a href="<?php echo htmlspecialchars($ctaHref); ?>" style="background:#111827;color:#fff;text-decoration:none;padding:10px 12px;border-radius:10px;font-weight:700">Back to Services</a></p>
      </section>
    </main>
    <aside class="aside">
      <nav class="nav">
        <a href="../home-services.php">Home</a>
        <a href="../my-services.php">My Services</a>
        <a href="../profile.php">Profile</a>
      </nav>
    </aside>
  </div>
</body>
</html>
