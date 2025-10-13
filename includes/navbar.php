<?php
// Minimal navbar: show links based on session + user_choice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isLoggedIn = isset($_SESSION['user_id']);
$userChoice = $_SESSION['user_choice'] ?? null;
$base = '/ncp3106-ServisyoHub/pages';
?>
<nav class="navbar" role="navigation" aria-label="Main navigation">
  <div class="container">
    <a href="/ncp3106-ServisyoHub/" class="navbar-brand">Servisyo Hub</a>

    <ul class="navbar-nav">
      <?php if ($isLoggedIn): ?>
        <li><a href="<?php echo "{$base}/home.php"; ?>" class="<?php echo $currentPage==='home' ? 'active' : ''; ?>">Home</a></li>
        <?php if ($userChoice === 'services'): ?>
          <li><a href="<?php echo "{$base}/my-services.php"; ?>" class="<?php echo $currentPage==='my-services' ? 'active' : ''; ?>">My Services</a></li>
        <?php else: ?>
          <li><a href="<?php echo "{$base}/my-jobs.php"; ?>" class="<?php echo $currentPage==='my-jobs' ? 'active' : ''; ?>">My Jobs</a></li>
        <?php endif; ?>
        <li><a href="<?php echo "{$base}/profile.php"; ?>" class="<?php echo $currentPage==='profile' ? 'active' : ''; ?>">Profile</a></li>
        <li><a href="/ncp3106-ServisyoHub/logout.php">Logout</a></li>
      <?php else: ?>
        <li><a href="/ncp3106-ServisyoHub/" class="<?php echo $currentPage==='index' ? 'active' : ''; ?>">Home</a></li>
        <li><a href="<?php echo "{$base}/user-choice.php"; ?>">Get Started</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>