<?php
// Login page for Modern Creative Professional Web App
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Set user choice for services
$_SESSION['user_choice'] = 'services';

$pageTitle = "Login";
$bodyClass = "login-page";
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
    <section class="section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Welcome Back</h1>
                        <p>Sign in to your account to continue</p>
                    </div>
                    
                    <form class="auth-form" id="login-form" method="post">
                        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-input" required 
                                   placeholder="Enter your email address">
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-input" required 
                                   placeholder="Enter your password">
                        </div>
                        
                        <div class="form-group">
                            <div class="form-options">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remember" class="checkbox-input">
                                    <span class="checkbox-text">Remember me</span>
                                </label>
                                <a href="#forgot-password" class="forgot-link">Forgot password?</a>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            Sign In
                        </button>
                    </form>
                    
                    <div class="auth-divider">
                        <span>or</span>
                    </div>
                    
                    <div class="social-login">
                        <button class="btn btn-social btn-google">
                            <span class="social-icon">🔍</span>
                            Continue with Google
                        </button>
                        <button class="btn btn-social btn-linkedin">
                            <span class="social-icon">💼</span>
                            Continue with LinkedIn
                        </button>
                    </div>
                    
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="signup.php" class="auth-link">Sign up here</a></p>
                    </div>
                </div>
                
                <!-- Login Benefits -->
                <div class="auth-benefits">
                    <h3>Why join our community?</h3>
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <div class="benefit-icon">🚀</div>
                            <div class="benefit-content">
                                <h4>Access Premium Services</h4>
                                <p>Connect with top-tier professionals and exclusive opportunities.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">🎯</div>
                            <div class="benefit-content">
                                <h4>Smart Matching</h4>
                                <p>Our AI matches you with the perfect projects and collaborators.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">💼</div>
                            <div class="benefit-content">
                                <h4>Professional Network</h4>
                                <p>Build lasting relationships with verified professionals worldwide.</p>
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