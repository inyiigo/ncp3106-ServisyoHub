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
            
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> Modern Creative Professional. All rights reserved.</p>
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