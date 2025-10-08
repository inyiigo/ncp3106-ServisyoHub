<?php
// Registration page for Servisyo Hub - Job Seekers
session_start();

// Set user choice for jobs
$_SESSION['user_choice'] = 'job';

$pageTitle = "Job Registration";
$bodyClass = "registration-page";
?>

<?php include '../components/header.php'; ?>

<main id="main-content">
    <section class="section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Join as Job Seeker</h1>
                        <p>Create your account to start applying for jobs</p>
                    </div>
                    
                    <form class="auth-form" id="registration-form">
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
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" required 
                                   placeholder="Confirm your password">
                        </div>
                        
                        <div class="form-group">
                            <label for="skills" class="form-label">Your Skills</label>
                            <input type="text" id="skills" name="skills" class="form-input" required 
                                   placeholder="e.g., Web Development, Graphic Design, Writing">
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" class="checkbox-input" required>
                                <span class="checkbox-text">I agree to the <a href="#terms" class="terms-link">Terms of Service</a> and <a href="#privacy" class="terms-link">Privacy Policy</a></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            Create Job Seeker Account
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php" class="auth-link">Sign in here</a></p>
                    </div>
                </div>
                
                <!-- Registration Benefits -->
                <div class="auth-benefits">
                    <h3>Why join as a job seeker?</h3>
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <div class="benefit-icon">💼</div>
                            <div class="benefit-content">
                                <h4>Find Your Dream Job</h4>
                                <p>Browse thousands of job opportunities from local employers.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">📈</div>
                            <div class="benefit-content">
                                <h4>Build Your Career</h4>
                                <p>Track applications and grow your professional network.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">🎯</div>
                            <div class="benefit-content">
                                <h4>Smart Matching</h4>
                                <p>Get matched with jobs that fit your skills and interests.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../components/footer.php'; ?>

<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>