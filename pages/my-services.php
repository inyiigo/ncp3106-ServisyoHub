<?php
// My Services page for Modern Creative Professional Web App
session_start();

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
                <button class="btn btn-primary" id="add-service-btn">Add New Service</button>
            </div>
            
            <div class="services-overview">
                <div class="overview-card">
                    <div class="overview-icon">📊</div>
                    <div class="overview-content">
                        <h3>12</h3>
                        <p>Active Services</p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="overview-icon">💰</div>
                    <div class="overview-content">
                        <h3>$2,450</h3>
                        <p>This Month</p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="overview-icon">⭐</div>
                    <div class="overview-content">
                        <h3>4.9</h3>
                        <p>Average Rating</p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="overview-icon">👥</div>
                    <div class="overview-content">
                        <h3>47</h3>
                        <p>Completed Orders</p>
                    </div>
                </div>
            </div>
            
            <div class="services-list">
                <div class="service-item">
                </div>
                
                <div class="service-item">
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>