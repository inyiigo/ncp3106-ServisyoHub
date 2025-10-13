<?php
// My Jobs page for Modern Creative Professional Web App
session_start();

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
                <button class="btn btn-primary" id="post-job-btn">Post New Job</button>
            </div>
            
            <div class="jobs-tabs">
                <button class="tab-btn active" data-tab="active">Active Jobs</button>
                <button class="tab-btn" data-tab="completed">Completed</button>
                <button class="tab-btn" data-tab="drafts">Drafts</button>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>