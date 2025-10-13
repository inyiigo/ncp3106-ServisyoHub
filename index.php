<?php
// Title page for Servisyo Hub
session_start();

$pageTitle = "Welcome to Servisyo Hub";
$bodyClass = "title-page";
?>

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