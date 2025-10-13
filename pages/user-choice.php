<?php
// User choice page for Servisyo Hub
session_start();

$pageTitle = "You are here for";
$bodyClass = "user-choice-page";
?>

<?php include '../includes/header.php'; ?>

<main id="main-content">
    <section class="section">
        <div class="container">
            <div class="choice-container">
                <div class="choice-header">
                    <h1>You are here for</h1>
                </div>
                
                <div class="choice-options">
                    <div class="choice-card" data-choice="services">
                        <div class="choice-icon">🔧</div>
                        <h3>Services</h3>
                        <p>I want to look for skills and services.</p>
                        <div class="choice-features">
                            <span class="feature">✓ Create service listings</span>
                            <span class="feature">✓ Manage bookings</span>
                            <span class="feature">✓ Build reputation</span>
                        </div>
                        <button class="btn btn-primary">Choose Services</button>
                    </div>
                    
                    <div class="choice-card" data-choice="job">
                        <div class="choice-icon">💼</div>
                        <h3>a Job</h3>
                        <p>I want to find work opportunities and employment</p>
                        <div class="choice-features">
                            <span class="feature">✓ Browse job listings</span>
                            <span class="feature">✓ Apply for positions</span>
                            <span class="feature">✓ Track applications</span>
                        </div>
                        <button class="btn btn-primary">Choose Job</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>
