<?php
// Home page for logged-in users in Servisyo Hub
session_start();

// Check if user is logged in, if not redirect to user choice
if (!isset($_SESSION['user_id'])) {
    header('Location: user-choice.php');
    exit();
}

$userChoice = $_SESSION['user_choice'] ?? 'services';
$pageTitle = "Dashboard";
$bodyClass = "dashboard-page";
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
  <section class="section">
    <div class="container">
      <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h1>
      <p><?php echo $userChoice === 'services' ? 'Manage your services' : 'Manage your jobs'; ?></p>

      <!-- Minimal dashboard actions -->
      <div class="actions">
        <?php if ($userChoice === 'services'): ?>
          <a href="my-services.php" class="btn btn-primary">My Services</a>
        <?php else: ?>
          <a href="my-jobs.php" class="btn btn-primary">My Jobs</a>
        <?php endif; ?>
        <a href="profile.php" class="btn">Profile</a>
      </div>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>
