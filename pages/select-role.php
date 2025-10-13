<?php
// Select Role page for Modern Creative Professional Web App
session_start();

$pageTitle = "Select Role";
$bodyClass = "select-role-page";
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
    <section class="section">
        <div class="container">
            <div class="role-selection-container">
                <div class="role-header">
                    <h1>What describes you best?</h1>
                    <p>Choose your primary role to get personalized recommendations</p>
                </div>
                
                <div class="role-options">
                    <div class="role-card" data-role="client">
                        <div class="role-icon">🎯</div>
                        <h3>I'm looking to hire</h3>
                        <p>Find talented professionals for my projects</p>
                        <ul class="role-features">
                            <li>Browse services</li>
                            <li>Post projects</li>
                            <li>Manage contracts</li>
                        </ul>
                        <button class="btn btn-primary">Continue as Client</button>
                    </div>
                    
                    <div class="role-card" data-role="freelancer">
                        <div class="role-icon">💼</div>
                        <h3>I want to offer services</h3>
                        <p>Showcase my skills and find clients</p>
                        <ul class="role-features">
                            <li>Create portfolio</li>
                            <li>Offer services</li>
                            <li>Build reputation</li>
                        </ul>
                        <button class="btn btn-primary">Continue as Freelancer</button>
                    </div>
                    
                    <div class="role-card" data-role="both">
                        <div class="role-icon">🔄</div>
                        <h3>I do both</h3>
                        <p>Hire professionals and offer my services</p>
                        <ul class="role-features">
                            <li>Full platform access</li>
                            <li>Switch between roles</li>
                            <li>Maximum flexibility</li>
                        </ul>
                        <button class="btn btn-primary">Continue with Both</button>
                    </div>
                </div>
                
                <div class="role-footer">
                    <p>You can change this later in your profile settings</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>

