<?php
// Profile page for Modern Creative Professional Web App
session_start();

$pageTitle = "Profile";
$bodyClass = "profile-page";
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
    <section class="section">
        <div class="container">
            <div class="profile-container">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img src="https://via.placeholder.com/120" alt="Profile Picture" id="profile-picture">
                        <button class="avatar-edit-btn" id="change-avatar-btn">📷</button>
                    </div>
                    
                    <div class="profile-info">
                        <h1>John Doe</h1>
                        <p class="profile-title">Senior UI/UX Designer</p>
                        <p class="profile-location">📍 San Francisco, CA</p>
                        
                        <div class="profile-stats">
                            <div class="stat">
                                <span class="stat-number">4.9</span>
                                <span class="stat-label">Rating</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">127</span>
                                <span class="stat-label">Reviews</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">47</span>
                                <span class="stat-label">Projects</span>
                            </div>
                        </div>
                        
                        <div class="profile-actions">
                            <button class="btn btn-primary">Edit Profile</button>
                            <button class="btn btn-secondary">Share Profile</button>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Tabs -->
                <div class="profile-tabs">
                    <button class="tab-btn active" data-tab="about">About</button>
                    <button class="tab-btn" data-tab="services">Services</button>
                    <button class="tab-btn" data-tab="portfolio">Portfolio</button>
                    <button class="tab-btn" data-tab="reviews">Reviews</button>
                </div>
                
                <!-- Tab Content -->
                <div class="profile-content">
                    <div class="tab-content active" id="about-content">
                        <div class="content-grid">
                            <div class="content-card">
                                <h3>Bio</h3>
                                <p>Passionate UI/UX designer with 5+ years of experience creating beautiful and functional digital experiences. I specialize in user research, wireframing, prototyping, and visual design.</p>
                            </div>
                            
                            <div class="content-card">
                                <h3>Skills</h3>
                                <div class="skills-list">
                                    <span class="skill-tag">UI/UX Design</span>
                                    <span class="skill-tag">Figma</span>
                                    <span class="skill-tag">Adobe Creative Suite</span>
                                    <span class="skill-tag">User Research</span>
                                    <span class="skill-tag">Prototyping</span>
                                    <span class="skill-tag">Design Systems</span>
                                </div>
                            </div>
                            
                            <div class="content-card">
                                <h3>Experience</h3>
                                <div class="experience-list">
                                    <div class="experience-item">
                                        <h4>Senior UI/UX Designer</h4>
                                        <p class="company">TechCorp Inc.</p>
                                        <p class="duration">2020 - Present</p>
                                    </div>
                                    <div class="experience-item">
                                        <h4>UI/UX Designer</h4>
                                        <p class="company">StartupCo</p>
                                        <p class="duration">2018 - 2020</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="content-card">
                                <h3>Education</h3>
                                <div class="education-list">
                                    <div class="education-item">
                                        <h4>Bachelor of Design</h4>
                                        <p class="school">Art Institute of California</p>
                                        <p class="year">2016 - 2018</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="services-content">
                        <div class="services-grid">
                            <div class="service-card">
                                <h3>Custom Logo Design</h3>
                                <p>Professional logo design with unlimited revisions</p>
                                <div class="service-price">Starting at $150</div>
                            </div>
                            
                            <div class="service-card">
                                <h3>Website Design</h3>
                                <p>Complete website design and prototyping</p>
                                <div class="service-price">Starting at $500</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="portfolio-content">
                        <div class="portfolio-grid">
                            <div class="portfolio-item">
                                <img src="https://via.placeholder.com/300x200" alt="Portfolio Item">
                                <h4>E-commerce Website Design</h4>
                                <p>Complete UI/UX design for online store</p>
                            </div>
                            
                            <div class="portfolio-item">
                                <img src="https://via.placeholder.com/300x200" alt="Portfolio Item">
                                <h4>Mobile App Design</h4>
                                <p>iOS and Android app design</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="reviews-content">
                        <div class="reviews-list">
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <img src="https://via.placeholder.com/40" alt="Reviewer" class="reviewer-avatar">
                                        <div>
                                            <h4>Sarah Johnson</h4>
                                            <div class="review-rating">⭐⭐⭐⭐⭐</div>
                                        </div>
                                    </div>
                                    <span class="review-date">2 days ago</span>
                                </div>
                                <p class="review-text">Excellent work! John delivered exactly what I was looking for and was very responsive throughout the project.</p>
                            </div>
                            
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <img src="https://via.placeholder.com/40" alt="Reviewer" class="reviewer-avatar">
                                        <div>
                                            <h4>Mike Chen</h4>
                                            <div class="review-rating">⭐⭐⭐⭐⭐</div>
                                        </div>
                                    </div>
                                    <span class="review-date">1 week ago</span>
                                </div>
                                <p class="review-text">Great designer with excellent communication skills. Highly recommend!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>