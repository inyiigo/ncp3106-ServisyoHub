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
                    <div class="service-info">
                        <h3>Custom Logo Design</h3>
                        <p>Professional logo design with unlimited revisions</p>
                        <div class="service-stats">
                            <span class="stat">$150 starting</span>
                            <span class="stat">3 days delivery</span>
                            <span class="stat">⭐ 4.9 (127 reviews)</span>
                        </div>
                    </div>
                    <div class="service-actions">
                        <button class="btn btn-secondary">Edit</button>
                        <button class="btn btn-outline">View</button>
                        <button class="btn btn-outline">Pause</button>
                    </div>
                </div>
                
                <div class="service-item">
                    <div class="service-info">
                        <h3>Website Development</h3>
                        <p>Full-stack web development with modern technologies</p>
                        <div class="service-stats">
                            <span class="stat">$800 starting</span>
                            <span class="stat">7 days delivery</span>
                            <span class="stat">⭐ 4.8 (89 reviews)</span>
                        </div>
                    </div>
                    <div class="service-actions">
                        <button class="btn btn-secondary">Edit</button>
                        <button class="btn btn-outline">View</button>
                        <button class="btn btn-outline">Pause</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>