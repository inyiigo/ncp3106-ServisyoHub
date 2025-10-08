<?php
// Header component for Modern Creative Professional Web App
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern Creative Professional Web Application - Connect, Create, and Collaborate">
    <meta name="keywords" content="professional, creative, services, jobs, collaboration">
    <meta name="author" content="Modern Creative Professional">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Modern Creative Professional">
    <meta property="og:description" content="Connect, Create, and Collaborate with professionals worldwide">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Modern Creative Professional">
    <meta name="twitter:description" content="Connect, Create, and Collaborate with professionals worldwide">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Page Title -->
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Modern Creative Professional</title>
    
    <!-- Additional CSS for specific pages -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "Modern Creative Professional",
        "description": "Connect, Create, and Collaborate with professionals worldwide",
        "url": "<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Any"
    }
    </script>
</head>
<body class="<?php echo isset($bodyClass) ? $bodyClass : ''; ?>">
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="sr-only">Skip to main content</a>
    
    <!-- Loading indicator -->
    <div id="page-loader" style="display: none;">
        <div class="loading"></div>
    </div>