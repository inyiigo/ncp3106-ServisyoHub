<?php
// Signup page for Modern Creative Professional Web App
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$pageTitle = "Sign Up";
$bodyClass = "signup-page";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['user_id'] = time();
    $_SESSION['username'] = trim($_POST['username'] ?? 'ServiceUser');
    $_SESSION['user_choice'] = 'services';
    header('Location: home.php');
    exit;
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
    <section class="section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Join Our Community</h1>
                        <p>Create your account and start connecting with professionals</p>
                    </div>
                    
                    <form class="auth-form" id="signup-form" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" id="firstName" name="firstName" class="form-input" required 
                                       placeholder="Enter your first name">
                            </div>
                            
                            <div class="form-group">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" id="lastName" name="lastName" class="form-input" required 
                                       placeholder="Enter your last name">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-input" required 
                                   placeholder="Enter your email address">
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-input" required 
                                   placeholder="Create a strong password" minlength="8">
                            <div class="password-strength">
                                <div class="strength-meter">
                                    <div class="strength-fill" id="strength-fill"></div>
                                </div>
                                <span class="strength-text" id="strength-text">Password strength</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" required 
                                   placeholder="Confirm your password">
                        </div>
                        
                        <div class="form-group">
                            <label for="role" class="form-label">I want to</label>
                            <select id="role" name="role" class="form-input" required>
                                <option value="">Select your primary role</option>
                                <option value="client">Hire professionals for projects</option>
                                <option value="freelancer">Offer my services</option>
                                <option value="both">Both hire and offer services</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" class="checkbox-input" required>
                                <span class="checkbox-text">I agree to the <a href="#terms" class="terms-link">Terms of Service</a> and <a href="#privacy" class="terms-link">Privacy Policy</a></span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="newsletter" class="checkbox-input">
                                <span class="checkbox-text">Send me updates and opportunities via email</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            Create Account
                        </button>
                    </form>
                    
                    <div class="auth-divider">
                        <span>or</span>
                    </div>
                    
                    <div class="social-login">
                        <button class="btn btn-social btn-google">
                            <span class="social-icon">🔍</span>
                            Sign up with Google
                        </button>
                        <button class="btn btn-social btn-linkedin">
                            <span class="social-icon">💼</span>
                            Sign up with LinkedIn
                        </button>
                    </div>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php" class="auth-link">Sign in here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>


<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>