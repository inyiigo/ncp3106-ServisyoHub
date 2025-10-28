<?php
session_start();
// Ensure greeting uses first name after login
if (empty($_SESSION['display_name']) && !empty($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $stmtName = $pdo->prepare('SELECT first_name FROM users WHERE id = ? LIMIT 1');
        $stmtName->execute([$_SESSION['user_id']]);
        $row = $stmtName->fetch();
        if ($row && !empty($row['first_name'])) {
            $_SESSION['display_name'] = $row['first_name'];
        }
    } catch (Throwable $e) {
        // Silently ignore name fetch errors; fallback will be used below
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/../config/db.php';

        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $gender = trim($_POST['gender'] ?? '');
        $profession = trim($_POST['profession'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($first === '' || $last === '' || $phone === '' || $email === '' || $password === '' || $gender === '' || $profession === '' || $address === '') {
            throw new RuntimeException('Please fill in all required fields.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email address.');
        }

        // Normalize phone to a consistent local format: digits-only, convert +63xxxxxxxxxx to 0xxxxxxxxxx
        $phone = preg_replace('/\D+/', '', $phone);
        if (strpos($phone, '63') === 0 && strlen($phone) === 12) { // e.g., 63917xxxxxxx
            $phone = '0' . substr($phone, 2); // 0917xxxxxxx
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new RuntimeException('An account with this email already exists.');
        }

        // Also prevent duplicate phone numbers for login consistency
        $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            throw new RuntimeException('An account with this mobile number already exists.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, phone, email, password_hash, gender, profession, address) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$first, $last, $phone, $email, $hash, $gender, $profession, $address]);
        $userId = (int)$pdo->lastInsertId();

        if (!empty($_FILES['application_files']) && is_array($_FILES['application_files']['name'])) {
            $uploadDir = dirname(__DIR__) . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
            $maxBytes = 5 * 1024 * 1024; // 5MB
            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $names = $_FILES['application_files']['name'];
            $tmpNames = $_FILES['application_files']['tmp_name'];
            $sizes = $_FILES['application_files']['size'];
            $errors = $_FILES['application_files']['error'];

            for ($i = 0, $n = count($names); $i < $n; $i++) {
                if ($errors[$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if ($sizes[$i] > $maxBytes) {
                    continue;
                }
                $mime = $finfo->file($tmpNames[$i]) ?: 'application/octet-stream';
                if (!in_array($mime, $allowed, true)) {
                    continue;
                }
                $ext = pathinfo($names[$i], PATHINFO_EXTENSION);
                $safeExt = preg_replace('/[^A-Za-z0-9]+/', '', $ext);
                $stored = bin2hex(random_bytes(8)) . '_' . time() . ($safeExt ? ('.' . strtolower($safeExt)) : '');
                $dest = $uploadDir . '/' . $stored;

                if (move_uploaded_file($tmpNames[$i], $dest)) {
                    $stmtF = $pdo->prepare('INSERT INTO user_files (user_id, original_name, stored_name, mime_type, size_bytes) VALUES (?,?,?,?,?)');
                    $stmtF->execute([$userId, $names[$i], $stored, $mime, $sizes[$i]]);
                }
            }
        }

        $_SESSION['display_name'] = $first;
        $_SESSION['flash_success'] = 'Registration completed successfully.';
        header('Location: ./home-jobs.php');
        exit;
    }
} catch (Throwable $e) {
    $_SESSION['flash_error'] = $e->getMessage();
}

$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : 'there';
$avatar = strtoupper(substr(preg_replace('/\s+/', '', $display), 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Home • Jobs • Servisyo Hub</title>
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <script defer src="../assets/js/script.js"></script>
    <style>
        /* Centered floating bottom navigation, matching home-services behavior */
        .dash-bottom-nav {
            position: fixed;
            left: 50%;
            bottom: 16px;
            z-index: 1000;
            width: max-content;
            transform: translateX(-50%) scale(0.92);
            transform-origin: bottom center;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }
        .dash-bottom-nav:hover {
            transform: translateX(-50%) scale(1);
            box-shadow: 0 12px 28px rgba(2,6,23,.12);
        }

        /* Search UI (rounded box with bottom strip) - make smaller */
        :root { --jobs-blue: #0078a6; }

        .jobs-search { max-width: 800px; margin: 12px auto 18px; }
        .jobs-box {
            border: 2px solid color-mix(in srgb, var(--jobs-blue) 70%, #0000);
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 10px 28px rgba(2,6,23,.08);
        }
        .jobs-row {
            display: grid;
            grid-template-columns: 28px 1fr 28px;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
        }
        .jobs-ico, .jobs-filter { width: 18px; height: 18px; color: var(--jobs-blue); opacity: .95; }
        .jobs-input {
            appearance: none; border: none; outline: none; background: transparent;
            font: inherit; color: var(--text, #0f172a); padding: 6px 0;
        }
        .jobs-row:focus-within { box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--jobs-blue) 35%, #0000); border-radius: 12px; }

        .jobs-strip {
            background: var(--jobs-blue);
            padding: 8px 10px;
            border-bottom-left-radius: 14px;
            border-bottom-right-radius: 14px;
        }
        .jobs-filters { display: flex; gap: 8px; align-items: center; overflow-x: auto; }
        .jobs-pill {
            appearance: none; border: 0; background: #fff; color: #0f172a;
            border-radius: 999px; padding: 6px 12px; font-weight: 800; font-size: .9rem;
            box-shadow: 0 4px 14px rgba(2,6,23,.12); cursor: pointer;
        }
        .jobs-pill:focus-visible { outline: 3px solid color-mix(in srgb, var(--jobs-blue) 30%, #0000); outline-offset: 2px; }

        /* enhance: sticky, entrance, clear button and suggestions */
        .jobs-search.is-sticky { position: sticky; top: 12px; z-index: 5; }
        @media (prefers-reduced-motion: no-preference){
            .jobs-search { animation: fadeUp .35s ease both; }
            @keyframes fadeUp { from { opacity:.0; transform: translateY(6px);} to { opacity:1; transform:none; } }
        }
        .jobs-row { position: relative; } /* for clear button focus ring containment */
        .jobs-input-wrap { position: relative; }
        .jobs-clear {
            position: absolute; right: 4px; top: 50%; transform: translateY(-50%);
            display: none; /* toggled when input has value */
            border: 0; background: transparent; color: var(--jobs-blue);
            width: 28px; height: 28px; border-radius: 999px; cursor: pointer;
        }
        .jobs-clear:hover { background: rgba(0,0,0,.05); }
        .jobs-clear:focus-visible { outline: 3px solid color-mix(in srgb, var(--jobs-blue) 30%, #0000); outline-offset: 2px; }

        /* suggestions dropdown */
        .jobs-suggest {
            position: absolute; left: 0; right: 0; top: calc(100% + 8px);
            margin: 0 auto; max-width: 800px; list-style: none; padding: 6px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            box-shadow: 0 14px 32px rgba(2,6,23,.18);
        }
        .jobs-suggest li {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 10px; border-radius: 10px; cursor: pointer;
        }
        .jobs-suggest li[aria-selected="true"],
        .jobs-suggest li:hover { background: rgba(0,120,166,.08); }
        .jobs-suggest .s-ico { width: 16px; height: 16px; color: var(--jobs-blue); opacity: .95; }

        /* page override: white background */
        body.theme-profile-bg { background: #ffffff !important; background-attachment: initial !important; }

        /* Blue bottom border on topbar */
        .dash-topbar { border-bottom: 3px solid #0078a6; }

        /* Job results section - make cards bigger */
        .jobs-results { max-width: 960px; margin: 0 auto 80px; }
        .results-header { display: flex; align-items: center; gap: 8px; margin: 10px 8px 12px; font-size: .9rem; color: #64748b; }
        .results-dot { width: 12px; height: 12px; border-radius: 50%; background: var(--jobs-blue); }

        .jobs-list { display: grid; gap: 12px; padding: 0 8px; }
        .job-card {
            background: #0078a6;
            color: #fff;
            border-radius: 16px;
            padding: 20px 22px;
            box-shadow: 0 8px 24px rgba(0,120,166,.24);
            transition: transform .15s ease, box-shadow .15s ease;
            position: relative;
        }
        .job-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,120,166,.32); }

        .job-title { font-weight: 800; font-size: 1.1rem; margin: 0 0 14px; color: #fff; }

        .job-meta { display: flex; flex-wrap: wrap; gap: 14px 18px; font-size: .9rem; opacity: .95; }
        .job-meta-item { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
        .job-meta-item svg { width: 16px; height: 16px; flex-shrink: 0; }

        .job-heart {
            position: absolute;
            top: 20px;
            right: 22px;
            width: 22px;
            height: 22px;
            color: #fff;
            opacity: .9;
            cursor: pointer;
            transition: transform .12s ease, opacity .12s ease;
        }
        .job-heart:hover { transform: scale(1.1); opacity: 1; }

        @media (prefers-reduced-motion: no-preference){
            .jobs-results { animation: fadeUp .4s ease both .15s; }
        }

        @media (max-width:640px){
            .jobs-row { grid-template-columns: 24px 1fr 24px; }
            .jobs-search { margin-inline: 8px; }
        }
    </style>
</head>
<body class="theme-profile-bg">

    <div class="dash-topbar center">
        <div class="dash-brand">
            <img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" />
        </div>
    </div>

    <div class="dash-overlay"></div>

    <div class="dash-shell">
        <main class="dash-content">
            <h1 class="dash-greet">Hi <?php echo htmlspecialchars($display); ?>!</h1>
            <p class="dash-muted">Welcome to your job application dashboard.</p>

            <!-- Search bar -->
            <section class="jobs-search is-sticky" aria-label="Search jobs">
                <div class="jobs-box">
                    <div class="jobs-row">
                        <svg class="jobs-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <div class="jobs-input-wrap">
                            <input class="jobs-input" type="search" name="q" placeholder="Search for a Job" aria-label="Search for a Job" autocomplete="off" />
                            <button type="button" class="jobs-clear" aria-label="Clear search">×</button>
                        </div>
                        <svg class="jobs-filter" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" role="img" aria-label="Filters"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                    </div>
                    <div class="jobs-strip">
                        <div class="jobs-filters">
                            <select class="jobs-pill" aria-label="Category">
                                <option>Category</option>
                                <option>Home Service</option>
                                <option>Personal Care</option>
                                <option>Electrical</option>
                            </select>
                            <select class="jobs-pill" aria-label="Date Posted">
                                <option>Date Posted</option>
                                <option>Last 24 hours</option>
                                <option>Last 7 days</option>
                                <option>Last 30 days</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- suggestions (auto-shown on focus/type) -->
                <ul class="jobs-suggest" role="listbox" id="jobsSuggest" hidden></ul>
            </section>

            <!-- Job Results -->
            <section class="jobs-results" aria-label="Job listings">
                <div class="results-header">
                    <span class="results-dot" aria-hidden="true"></span>
                    <span>543 results</span>
                </div>

                <div class="jobs-list">
                    <!-- Job Card 1 -->
                    <article class="job-card">
                        <h3 class="job-title">I'm looking for a part-time job</h3>
                        <div class="job-meta">
                            <span class="job-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10A8 8 0 1 0 4 12c0 6 8 10 8 10Z"/><circle cx="12" cy="12" r="3"/></svg>
                                Brgy. 442 Zone 44
                            </span>
                            <span class="job-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                On Thu, October 30
                            </span>
                            <span class="job-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
                                Posted 25 minutes ago by Kelly P.
                            </span>
                        </div>
                        <svg class="job-heart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-label="Save job"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-8.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                    </article>

                    <!-- Job Card 2 -->
                    <article class="job-card">
                        <h3 class="job-title">I'm looking for someone who can make my project</h3>
                        <div class="job-meta">
                            <span class="job-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10A8 8 0 1 0 4 12c0 6 8 10 8 10Z"/><circle cx="12" cy="12" r="3"/></svg>
                                Brgy. 442 Zone 44
                            </span>
                            <span class="job-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                On Thu, October 30
                            </span>
                            <span class="job-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
                                Posted 25 minutes ago by Frank Q.
                            </span>
                        </div>
                        <svg class="job-heart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-label="Save job"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-8.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                    </article>

                    <!-- Job Card 3 -->
                    <article class="job-card">
                        <h3 class="job-title">I'm looking for a part-time job</h3>
                        <div class="job-meta">
                            <span class="job-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10A8 8 0 1 0 4 12c0 6 8 10 8 10Z"/><circle cx="12" cy="12" r="3"/></svg>
                                Brgy. 442 Zone 44
                            </span>
                            <span class="job-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                On Thu, October 30
                            </span>
                            <span class="job-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
                                Posted 25 minutes ago by Kelly P.
                            </span>
                        </div>
                        <svg class="job-heart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-label="Save job"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-8.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                    </article>
                </div>
            </section>
        </main>

        <aside class="dash-aside">
            <nav class="dash-nav">
                <h3>Navigation</h3>
                <a href="./home-jobs.php" class="active">
                    <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/>
                    </svg>
                    Home
                </a>
                <a href="./my-jobs.php">
                    <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 7h16M4 12h10M4 17h7"/>
                    </svg>
                    My Jobs <span class="dash-badge">0</span>
                </a>
                <a href="./jobs-profile.php">
                    <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
                    </svg>
                    Profile
                </a>
            </nav>
        </aside>
    </div>

    <!-- Floating bottom navigation -->
    <nav class="dash-bottom-nav">
        <a href="./home-jobs.php" class="active" aria-label="Home">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/>
            </svg>
            <span>Home</span>
        </a>
        <a href="./my-jobs.php" aria-label="My Jobs">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 7h16M4 12h10M4 17h7"/>
            </svg>
            <span>My Jobs</span>
        </a>
        <a href="./jobs-profile.php" aria-label="Profile">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/>
            </svg>
            <span>Profile</span>
        </a>
    </nav>

    <script>
    (function(){
        const q = document.querySelector('.jobs-input');
        const clearBtn = document.querySelector('.jobs-clear');
        const list = document.getElementById('jobsSuggest');
        const container = document.querySelector('.jobs-search');
        const suggestions = [
            'Part-time cleaner in Quezon City',
            'Aircon technician (split-type)',
            'Upholstery deep cleaning',
            'Electrical wiring check-up',
            'Plumbing and handyman help',
            'Pest control general service'
        ];

        let active = -1; // active index for keyboard nav

        function render(items){
            list.innerHTML = items.map((t,i)=>(
                '<li role="option" data-val="'+t.replace(/"/g,'&quot;')+'" aria-selected="'+(i===active)+'">' +
                    '<svg class="s-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>' +
                    '<span>'+t+'</span>' +
                '</li>'
            )).join('');
        }
        function openSuggest(){
            const v = (q.value || '').trim().toLowerCase();
            const filtered = v ? suggestions.filter(s=>s.toLowerCase().includes(v)) : suggestions.slice(0,5);
            active = -1;
            render(filtered);
            list.hidden = filtered.length === 0;
        }
        function closeSuggest(){ list.hidden = true; active = -1; }

        q.addEventListener('input', () => {
            clearBtn.style.display = q.value ? 'inline-flex' : 'none';
            openSuggest();
        });
        q.addEventListener('focus', openSuggest);
        q.addEventListener('blur', () => setTimeout(closeSuggest, 100)); // allow click on item

        clearBtn.addEventListener('click', () => {
            q.value = '';
            clearBtn.style.display = 'none';
            q.focus();
            openSuggest();
        });

        list.addEventListener('mousedown', (e) => {
            const li = e.target.closest('li[role="option"]');
            if (!li) return;
            q.value = li.dataset.val || '';
            clearBtn.style.display = q.value ? 'inline-flex' : 'none';
            closeSuggest();
        });

        q.addEventListener('keydown', (e) => {
            if (list.hidden) return;
            const items = Array.from(list.querySelectorAll('li[role="option"]'));
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                active = (active + 1) % items.length;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                active = (active - 1 + items.length) % items.length;
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (active >= 0) {
                    q.value = items[active].dataset.val || '';
                    clearBtn.style.display = q.value ? 'inline-flex' : 'none';
                }
                closeSuggest();
                return;
            } else if (e.key === 'Escape') {
                closeSuggest();
                return;
            } else {
                return;
            }
            items.forEach((el,i)=>el.setAttribute('aria-selected', String(i===active)));
        });

        // click outside closes
        document.addEventListener('click', (e)=>{
            if (!container.contains(e.target)) closeSuggest();
        });
    })();
    </script>
</body>
</html>
