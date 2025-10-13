<?php
// simplified My Services - placeholder only
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$pageTitle = "My Services";
$bodyClass = "my-services-page";
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
  <section class="section">
    <div class="container">
      <div class="page-header">
        <h1>My Services</h1>
        <a href="add-service.php" class="btn btn-primary">Add New Service</a>
      </div>

      <!-- minimal placeholder (removed services-list details) -->
      <div class="services-placeholder">
        <p class="muted">No services to display yet.</p>
      </div>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>