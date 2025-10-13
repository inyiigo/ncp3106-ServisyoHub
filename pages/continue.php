<?php
// Continue page for Modern Creative Professional Web App
session_start();

$pageTitle = "Continue Learning";
$bodyClass = "continue-page";
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main id="main-content">
    <section class="section">
        <div class="container">
            <div class="learning-header">
                <h1>Continue Your Learning Journey</h1>
                <p>Expand your skills with our curated courses and resources</p>
            </div>
            
            <div class="learning-categories">
                <div class="category-card">
                    <div class="category-icon">🎨</div>
                    <h3>Design</h3>
                    <p>UI/UX, Graphic Design, Branding</p>
                    <button class="btn btn-primary">Explore Courses</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">💻</div>
                    <h3>Development</h3>
                    <p>Web Development, Mobile Apps, Backend</p>
                    <button class="btn btn-primary">Explore Courses</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">📝</div>
                    <h3>Writing</h3>
                    <p>Content Writing, Copywriting, SEO</p>
                    <button class="btn btn-primary">Explore Courses</button>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

<!-- Main JavaScript -->
<script src="../assets/js/main.js"></script>