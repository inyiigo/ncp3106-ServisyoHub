<?php
// Footer component for Modern Creative Professional Web App
?>

<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer-content">
            <!-- Company Information -->
            <div class="footer-section">
                <h3>Modern Creative Professional</h3>
                <p>Connecting creative professionals worldwide to build amazing projects together. Your success is our mission.</p>
                <div class="social-links">
                    <a href="#" class="social-link" aria-label="Follow us on Twitter">
                        <span class="social-icon">🐦</span>
                        Twitter
                    </a>
                    <a href="#" class="social-link" aria-label="Follow us on LinkedIn">
                        <span class="social-icon">💼</span>
                        LinkedIn
                    </a>
                    <a href="#" class="social-link" aria-label="Follow us on Instagram">
                        <span class="social-icon">📷</span>
                        Instagram
                    </a>
                    <a href="#" class="social-link" aria-label="Follow us on GitHub">
                        <span class="social-icon">💻</span>
                        GitHub
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="pages/home-services.php">Browse Services</a></li>
                    <li><a href="pages/home-jobs.php">Find Jobs</a></li>
                    <li><a href="pages/registration.php">Get Started</a></li>
                    <li><a href="pages/continue.php">Continue Learning</a></li>
                </ul>
            </div>
            
            <!-- Account Links -->
            <div class="footer-section">
                <h3>Account</h3>
                <ul class="footer-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="pages/profile.php">My Profile</a></li>
                        <li><a href="pages/my-services.php">My Services</a></li>
                        <li><a href="pages/my-jobs.php">My Jobs</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="pages/login.php">Login</a></li>
                        <li><a href="pages/signup.php">Sign Up</a></li>
                        <li><a href="pages/registration.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Support & Legal -->
            <div class="footer-section">
                <h3>Support & Legal</h3>
                <ul class="footer-links">
                    <li><a href="#help">Help Center</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                    <li><a href="#privacy">Privacy Policy</a></li>
                    <li><a href="#terms">Terms of Service</a></li>
                    <li><a href="#cookies">Cookie Policy</a></li>
                    <li><a href="#accessibility">Accessibility</a></li>
                </ul>
            </div>
            
            <!-- Newsletter Signup -->
            <div class="footer-section">
                <h3>Stay Updated</h3>
                <p>Get the latest updates on new features and opportunities.</p>
                <form class="newsletter-form" id="newsletter-form">
                    <div class="form-group">
                        <input type="email" class="form-input" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </div>
                    <small class="newsletter-disclaimer">We respect your privacy. Unsubscribe at any time.</small>
                </form>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> Modern Creative Professional. All rights reserved.</p>
                </div>
                <div class="footer-languages">
                    <select class="language-selector" aria-label="Select language">
                        <option value="en">English</option>
                        <option value="es">Español</option>
                        <option value="fr">Français</option>
                        <option value="de">Deutsch</option>
                        <option value="it">Italiano</option>
                        <option value="pt">Português</option>
                    </select>
                </div>
            </div>
            
            <!-- Back to Top Button -->
            <button class="back-to-top" aria-label="Back to top" onclick="scrollToTop()">
                <span class="back-to-top-icon">↑</span>
            </button>
        </div>
    </div>
</footer>

<!-- Back to Top Script -->