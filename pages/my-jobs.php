<?php
// My Jobs page for Modern Creative Professional Web App
session_start();

$pageTitle = "My Jobs";
$bodyClass = "my-jobs-page";
?>

<?php include '../components/header.php'; ?>
<?php include '../components/navbar.php'; ?>

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
            
            <div class="jobs-content">
                <div class="tab-content active" id="active-jobs">
                    <div class="job-item">
                        <div class="job-info">
                            <h3>Senior UI/UX Designer</h3>
                            <p>Looking for an experienced designer to lead our product team</p>
                            <div class="job-meta">
                                <span class="meta-item">📍 Remote</span>
                                <span class="meta-item">💰 $80k - $120k</span>
                                <span class="meta-item">📅 Posted 3 days ago</span>
                            </div>
                        </div>
                        <div class="job-actions">
                            <button class="btn btn-secondary">Edit</button>
                            <button class="btn btn-outline">View Applications</button>
                        </div>
                    </div>
                    
                    <div class="job-item">
                        <div class="job-info">
                            <h3>Content Writer</h3>
                            <p>Need a creative writer for blog posts and social media content</p>
                            <div class="job-meta">
                                <span class="meta-item">📍 New York, NY</span>
                                <span class="meta-item">💰 $25 - $50/hour</span>
                                <span class="meta-item">📅 Posted 1 week ago</span>
                            </div>
                        </div>
                        <div class="job-actions">
                            <button class="btn btn-secondary">Edit</button>
                            <button class="btn btn-outline">View Applications</button>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="completed-jobs">
                    <div class="empty-state">
                        <div class="empty-icon">✅</div>
                        <h3>No completed jobs yet</h3>
                        <p>Your completed jobs will appear here</p>
                    </div>
                </div>
                
                <div class="tab-content" id="drafts-jobs">
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <h3>No draft jobs</h3>
                        <p>Save jobs as drafts to finish them later</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../components/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>