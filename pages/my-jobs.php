<?php
// simplified My Jobs - placeholder only
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$pageTitle = "My Jobs";
$bodyClass = "my-jobs-page";
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
  <section class="section">
    <div class="container">
      <div class="page-header">
        <h1>My Jobs</h1>
      </div>

      <div class="jobs-placeholder">
        <p class="muted">No job applications yet.</p>
      </div>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>