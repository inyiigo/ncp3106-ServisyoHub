<?php
session_start();
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'there');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chats • Servisyo Hub</title>
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <script defer src="../assets/js/script.js"></script>
</head>
<body class="theme-profile-bg">
    <!-- Background Logo -->
    <div class="bg-logo">
        <img src="../assets/images/job_logo.png" alt="" />
    </div>

    <div class="dash-topbar center">
        <div class="dash-brand">
            <img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" />
        </div>
    </div>

    <div class="dash-shell">
        <main class="dash-content">
            <div class="dash-tagline-wrap"><p class="dash-tagline">Conversations with your Kasangga.</p></div>

            <section class="form-card glass-card">
                <h2 style="margin-top:0">Chats</h2>
                <p class="small-muted">Hi <?php echo htmlspecialchars($display); ?>, your recent conversations will appear here.</p>

                <div class="empty-wrap" style="padding:0">
                    <div class="empty-card" style="box-shadow:none;border:1px dashed var(--line)">
                        <p class="empty-title">No messages yet</p>
                        <p class="empty-text">Start by browsing gawain and messaging a Kasangga.</p>
                        <div class="actions" style="justify-content:center">
                            <a class="btn" href="./home-gawain.php">Browse Gawain</a>
                            <a class="btn secondary" href="./clients-post.php">Post a Quest</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Floating bottom navigation -->
    <nav class="dash-bottom-nav">
        <a href="./home-gawain.php" aria-label="Browse">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <span>Browse</span>
        </a>
        <a href="./clients-post.php" aria-label="Post">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/><circle cx="12" cy="12" r="11"/></svg>
            <span>Post</span>
        </a>
        <a href="./my-gawain.php" aria-label="My Gawain">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
            <span>My Gawain</span>
        </a>
        <a href="./chats.php" class="active" aria-label="Chats">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a4 4 0 0 1-4 4H8l-4 4v-4H5a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4z"/>
            </svg>
            <span>Chats</span>
        </a>
        <a href="./clients-profile.php" aria-label="Profile">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
            <span>Profile</span>
        </a>
    </nav>
</body>
</html>
