<?php
// Home page for logged-in users in Servisyo Hub
session_start();

// Check if user is logged in, if not redirect to user choice
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$userChoice = $_SESSION['user_choice'] ?? 'services';
$pageTitle = "Dashboard";
$bodyClass = "dashboard-page";
?>

<?php include '../components/header.php'; ?>
<?php include '../components/navbar.php'; ?>

<main id="main-content">
    <!-- Dashboard Header -->
    <section class="dashboard-hero">
        <div class="container">
            <div class="dashboard-header">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</h1>
                <p><?php echo $userChoice === 'services' ? 'Manage your services and grow your business' : 'Find your next opportunity'; ?></p>
            </div>
        </div>
    </section>

    <!-- Dashboard Content -->
    <section class="section">
        <div class="container">
            <?php if ($userChoice === 'services'): ?>
                <!-- Services User Dashboard -->
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-icon">📊</div>
                        <h3>Service Analytics</h3>
                        <div class="stats">
                            <div class="stat">
                                <span class="stat-number">12</span>
                                <span class="stat-label">Active Services</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">47</span>
                                <span class="stat-label">Completed Orders</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">4.9</span>
                                <span class="stat-label">Average Rating</span>
                            </div>
                        </div>
                        <a href="my-services.php" class="btn btn-primary">Manage Services</a>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">💰</div>
                        <h3>Earnings</h3>
                        <div class="earnings">
                            <div class="earning-item">
                                <span class="earning-label">This Month</span>
                                <span class="earning-amount">$2,450</span>
                            </div>
                            <div class="earning-item">
                                <span class="earning-label">Last Month</span>
                                <span class="earning-amount">$1,890</span>
                            </div>
                        </div>
                        <a href="profile.php" class="btn btn-secondary">View Profile</a>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">📈</div>
                        <h3>Recent Activity</h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <span class="activity-text">New order for Logo Design</span>
                                <span class="activity-time">2 hours ago</span>
                            </div>
                            <div class="activity-item">
                                <span class="activity-text">Review received - 5 stars</span>
                                <span class="activity-time">1 day ago</span>
                            </div>
                            <div class="activity-item">
                                <span class="activity-text">Service published: Web Development</span>
                                <span class="activity-time">3 days ago</span>
                            </div>
                        </div>
                        <a href="home-services.php" class="btn btn-outline">Browse All Services</a>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Job Seeker Dashboard -->
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-icon">📋</div>
                        <h3>Job Applications</h3>
                        <div class="stats">
                            <div class="stat">
                                <span class="stat-number">8</span>
                                <span class="stat-label">Applications Sent</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">3</span>
                                <span class="stat-label">Interviews Scheduled</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">2</span>
                                <span class="stat-label">Offers Received</span>
                            </div>
                        </div>
                        <a href="my-jobs.php" class="btn btn-primary">Manage Applications</a>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">💼</div>
                        <h3>Profile Views</h3>
                        <div class="earnings">
                            <div class="earning-item">
                                <span class="earning-label">This Week</span>
                                <span class="earning-amount">24 views</span>
                            </div>
                            <div class="earning-item">
                                <span class="earning-label">Last Week</span>
                                <span class="earning-amount">18 views</span>
                            </div>
                        </div>
                        <a href="profile.php" class="btn btn-secondary">Edit Profile</a>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-icon">🎯</div>
                        <h3>Recommended Jobs</h3>
                        <div class="job-recommendations">
                            <div class="job-item">
                                <span class="job-title">Senior Web Developer</span>
                                <span class="job-match">95% match</span>
                            </div>
                            <div class="job-item">
                                <span class="job-title">Frontend Designer</span>
                                <span class="job-match">87% match</span>
                            </div>
                            <div class="job-item">
                                <span class="job-title">UI/UX Specialist</span>
                                <span class="job-match">92% match</span>
                            </div>
                        </div>
                        <a href="home-jobs.php" class="btn btn-outline">Browse All Jobs</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include '../components/footer.php'; ?>

<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>
