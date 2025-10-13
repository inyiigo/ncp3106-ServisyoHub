<?php
// Home Jobs page for Modern Creative Professional Web App
session_start();

$pageTitle = "Browse Jobs";
$bodyClass = "jobs-page";
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
    <!-- Hero Section -->
    <section class="hero hero-jobs">
        <div class="container">
            <div class="hero-content">
                <h1>Find Your Next Project</h1>
                <p>Discover exciting opportunities from top companies and startups</p>
                
                <!-- Search Bar -->
                <div class="search-container">
                    <form class="search-form" id="jobs-search">
                        <div class="search-input-group">
                            <input type="search" class="search-input" placeholder="Search for jobs..." id="search-input">
                            <button type="submit" class="search-btn">
                                <span class="search-icon">🔍</span>
                            </button>
                        </div>
                        
                        <div class="search-filters">
                            <select class="filter-select" id="type-filter">
                                <option value="all">All Types</option>
                                <option value="full-time">Full-time</option>
                                <option value="part-time">Part-time</option>
                                <option value="contract">Contract</option>
                                <option value="freelance">Freelance</option>
                            </select>
                            
                            <select class="filter-select" id="location-filter">
                                <option value="all">All Locations</option>
                                <option value="remote">Remote</option>
                                <option value="new-york">New York</option>
                                <option value="san-francisco">San Francisco</option>
                                <option value="los-angeles">Los Angeles</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Jobs Grid -->
    <section class="section">
        <div class="container">
            <div class="jobs-header">
                <h2>Available Positions</h2>
                <div class="jobs-stats">
                    <span class="stat-item">
                        <span class="stat-number">156</span>
                        <span class="stat-label">Active Jobs</span>
                    </span>
                </div>
            </div>
            
            <div class="jobs-grid" id="jobs-grid">
                <div class="job-card" data-type="full-time" data-location="remote">
                    <div class="job-header">
                        <h3 class="job-title">Senior UI/UX Designer</h3>
                        <span class="job-type">Full-time</span>
                    </div>
                    <div class="job-company">
                        <div class="company-logo">🏢</div>
                        <div class="company-info">
                            <span class="company-name">TechCorp Inc.</span>
                            <span class="company-location">📍 Remote</span>
                        </div>
                    </div>
                    <div class="job-details">
                        <p class="job-description">We're looking for an experienced UI/UX designer to lead our product design team and create amazing user experiences.</p>
                        <div class="job-tags">
                            <span class="job-tag">UI/UX</span>
                            <span class="job-tag">Figma</span>
                            <span class="job-tag">Design Systems</span>
                        </div>
                    </div>
                    <div class="job-footer">
                        <span class="job-salary">$80k - $120k</span>
                        <button class="btn btn-primary">Apply Now</button>
                    </div>
                </div>
                
                <div class="job-card" data-type="contract" data-location="new-york">
                    <div class="job-header">
                        <h3 class="job-title">Frontend Developer</h3>
                        <span class="job-type">Contract</span>
                    </div>
                    <div class="job-company">
                        <div class="company-logo">🚀</div>
                        <div class="company-info">
                            <span class="company-name">StartupCo</span>
                            <span class="company-location">📍 New York, NY</span>
                        </div>
                    </div>
                    <div class="job-details">
                        <p class="job-description">Join our fast-growing startup and help build the next generation of web applications using React and modern tools.</p>
                        <div class="job-tags">
                            <span class="job-tag">React</span>
                            <span class="job-tag">JavaScript</span>
                            <span class="job-tag">TypeScript</span>
                        </div>
                    </div>
                    <div class="job-footer">
                        <span class="job-salary">$60 - $80/hour</span>
                        <button class="btn btn-primary">Apply Now</button>
                    </div>
                </div>
                
                <div class="job-card" data-type="freelance" data-location="remote">
                    <div class="job-header">
                        <h3 class="job-title">Content Writer</h3>
                        <span class="job-type">Freelance</span>
                    </div>
                    <div class="job-company">
                        <div class="company-logo">📝</div>
                        <div class="company-info">
                            <span class="company-name">Creative Agency</span>
                            <span class="company-location">📍 Remote</span>
                        </div>
                    </div>
                    <div class="job-details">
                        <p class="job-description">Looking for a creative writer to create engaging blog posts, social media content, and marketing copy.</p>
                        <div class="job-tags">
                            <span class="job-tag">Content Writing</span>
                            <span class="job-tag">SEO</span>
                            <span class="job-tag">Social Media</span>
                        </div>
                    </div>
                    <div class="job-footer">
                        <span class="job-salary">$25 - $50/hour</span>
                        <button class="btn btn-primary">Apply Now</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>