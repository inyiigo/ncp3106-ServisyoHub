<?php
// Home Services page for Modern Creative Professional Web App
session_start();

$pageTitle = "Browse Services";
$bodyClass = "services-page";
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
    <!-- Hero Section -->
    <section class="hero hero-services">
        <div class="container">
            <div class="hero-content">
                <h1>Professional Services</h1>
                <p>Discover talented professionals ready to bring your projects to life</p>
                
                <!-- Search Bar -->
                <div class="search-container">
                    <form class="search-form" id="services-search">
                        <div class="search-input-group">
                            <input type="search" class="search-input" placeholder="Search for services..." id="search-input">
                            <button type="submit" class="search-btn">
                                <span class="search-icon">🔍</span>
                            </button>
                        </div>
                        
                        <div class="search-filters">
                            <select class="filter-select" id="category-filter">
                                <option value="all">All Categories</option>
                                <option value="design">Design</option>
                                <option value="development">Development</option>
                                <option value="writing">Writing</option>
                                <option value="marketing">Marketing</option>
                                <option value="business">Business</option>
                            </select>
                            
                            <select class="filter-select" id="price-filter">
                                <option value="all">Any Price</option>
                                <option value="0-100">$0 - $100</option>
                                <option value="100-500">$100 - $500</option>
                                <option value="500-1000">$500 - $1,000</option>
                                <option value="1000+">$1,000+</option>
                            </select>
                            
                            <select class="filter-select" id="delivery-filter">
                                <option value="all">Any Delivery Time</option>
                                <option value="1">1 day</option>
                                <option value="3">3 days</option>
                                <option value="7">1 week</option>
                                <option value="14">2 weeks</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="section">
        <div class="container">
            <div class="services-header">
                <h2>Available Services</h2>
                <div class="services-stats">
                    <span class="stat-item">
                        <span class="stat-number" id="total-services">247</span>
                        <span class="stat-label">Services Available</span>
                    </span>
                    <span class="stat-item">
                        <span class="stat-number" id="active-providers">89</span>
                        <span class="stat-label">Active Providers</span>
                    </span>
                </div>
            </div>
            
            <div class="services-grid" id="services-grid">
                <!-- Service Cards will be populated by JavaScript -->
                <div class="service-card" data-category="design" data-price="150" data-delivery="3">
                    <div class="service-image">
                        <div class="service-placeholder">🎨</div>
                        <div class="service-badge">Popular</div>
                    </div>
                    
                    <div class="service-content">
                        <div class="service-header">
                            <h3 class="service-title">Custom Logo Design</h3>
                            <div class="service-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-text">(127)</span>
                            </div>
                        </div>
                        
                        <p class="service-description">Professional logo design with unlimited revisions and multiple file formats included.</p>
                        
                        <div class="service-provider">
                            <div class="provider-avatar">👤</div>
                            <div class="provider-info">
                                <span class="provider-name">Sarah Design Studio</span>
                                <span class="provider-location">📍 New York, NY</span>
                            </div>
                        </div>
                        
                        <div class="service-footer">
                            <div class="service-price">
                                <span class="price-amount">$150</span>
                                <span class="price-label">starting at</span>
                            </div>
                            <button class="btn btn-primary">View Service</button>
                        </div>
                    </div>
                </div>
                
                <div class="service-card" data-category="development" data-price="800" data-delivery="7">
                    <div class="service-image">
                        <div class="service-placeholder">💻</div>
                        <div class="service-badge">Featured</div>
                    </div>
                    
                    <div class="service-content">
                        <div class="service-header">
                            <h3 class="service-title">Custom Website Development</h3>
                            <div class="service-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-text">(89)</span>
                            </div>
                        </div>
                        
                        <p class="service-description">Full-stack web development with modern technologies and responsive design.</p>
                        
                        <div class="service-provider">
                            <div class="provider-avatar">👨‍💻</div>
                            <div class="provider-info">
                                <span class="provider-name">Tech Solutions Pro</span>
                                <span class="provider-location">📍 San Francisco, CA</span>
                            </div>
                        </div>
                        
                        <div class="service-footer">
                            <div class="service-price">
                                <span class="price-amount">$800</span>
                                <span class="price-label">starting at</span>
                            </div>
                            <button class="btn btn-primary">View Service</button>
                        </div>
                    </div>
                </div>
                
                <div class="service-card" data-category="writing" data-price="75" data-delivery="3">
                    <div class="service-image">
                        <div class="service-placeholder">📝</div>
                    </div>
                    
                    <div class="service-content">
                        <div class="service-header">
                            <h3 class="service-title">Blog Post Writing</h3>
                            <div class="service-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-text">(156)</span>
                            </div>
                        </div>
                        
                        <p class="service-description">High-quality blog posts optimized for SEO with engaging content.</p>
                        
                        <div class="service-provider">
                            <div class="provider-avatar">✍️</div>
                            <div class="provider-info">
                                <span class="provider-name">Content Creator</span>
                                <span class="provider-location">📍 Austin, TX</span>
                            </div>
                        </div>
                        
                        <div class="service-footer">
                            <div class="service-price">
                                <span class="price-amount">$75</span>
                                <span class="price-label">per post</span>
                            </div>
                            <button class="btn btn-primary">View Service</button>
                        </div>
                    </div>
                </div>
                
                <div class="service-card" data-category="marketing" data-price="300" data-delivery="5">
                    <div class="service-image">
                        <div class="service-placeholder">📱</div>
                        <div class="service-badge">New</div>
                    </div>
                    
                    <div class="service-content">
                        <div class="service-header">
                            <h3 class="service-title">Social Media Marketing</h3>
                            <div class="service-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-text">(43)</span>
                            </div>
                        </div>
                        
                        <p class="service-description">Complete social media strategy and content creation for your brand.</p>
                        
                        <div class="service-provider">
                            <div class="provider-avatar">📊</div>
                            <div class="provider-info">
                                <span class="provider-name">Digital Marketing Co.</span>
                                <span class="provider-location">📍 Miami, FL</span>
                            </div>
                        </div>
                        
                        <div class="service-footer">
                            <div class="service-price">
                                <span class="price-amount">$300</span>
                                <span class="price-label">per month</span>
                            </div>
                            <button class="btn btn-primary">View Service</button>
                        </div>
                    </div>
                </div>
                
                <div class="service-card" data-category="business" data-price="500" data-delivery="14">
                    <div class="service-image">
                        <div class="service-placeholder">📈</div>
                    </div>
                    
                    <div class="service-content">
                        <div class="service-header">
                            <h3 class="service-title">Business Plan Writing</h3>
                            <div class="service-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-text">(67)</span>
                            </div>
                        </div>
                        
                        <p class="service-description">Professional business plan with market analysis and financial projections.</p>
                        
                        <div class="service-provider">
                            <div class="provider-avatar">💼</div>
                            <div class="provider-info">
                                <span class="provider-name">Business Consultant</span>
                                <span class="provider-location">📍 Chicago, IL</span>
                            </div>
                        </div>
                        
                        <div class="service-footer">
                            <div class="service-price">
                                <span class="price-amount">$500</span>
                                <span class="price-label">complete plan</span>
                            </div>
                            <button class="btn btn-primary">View Service</button>
                        </div>
                    </div>
                </div>
                
                <div class="service-card" data-category="design" data-price="200" data-delivery="5">
                    <div class="service-image">
                        <div class="service-placeholder">🎭</div>
                    </div>
                    
                    <div class="service-content">
                        <div class="service-header">
                            <h3 class="service-title">Brand Identity Package</h3>
                            <div class="service-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-text">(94)</span>
                            </div>
                        </div>
                        
                        <p class="service-description">Complete brand identity including logo, colors, typography, and guidelines.</p>
                        
                        <div class="service-provider">
                            <div class="provider-avatar">🎨</div>
                            <div class="provider-info">
                                <span class="provider-name">Creative Agency</span>
                                <span class="provider-location">📍 Los Angeles, CA</span>
                            </div>
                        </div>
                        
                        <div class="service-footer">
                            <div class="service-price">
                                <span class="price-amount">$200</span>
                                <span class="price-label">complete package</span>
                            </div>
                            <button class="btn btn-primary">View Service</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Load More Button -->
            <div class="text-center mt-8">
                <button class="btn btn-secondary" id="load-more-btn">Load More Services</button>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>

