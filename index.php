<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JobBlue – Login / Sign Up</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- App Styles -->
    <link href="styles.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Tailwind config to extend with our CSS variables
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: 'rgb(var(--primary-50))',
                            100: 'rgb(var(--primary-100))',
                            200: 'rgb(var(--primary-200))',
                            300: 'rgb(var(--primary-300))',
                            400: 'rgb(var(--primary-400))',
                            500: 'rgb(var(--primary-500))',
                            600: 'rgb(var(--primary-600))',
                            700: 'rgb(var(--primary-700))',
                            800: 'rgb(var(--primary-800))',
                            900: 'rgb(var(--primary-900))'
                        },
                        accent: 'rgb(var(--accent))',
                        ink: 'rgb(var(--ink))',
                        surface: 'rgb(var(--surface))'
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-surface text-ink">
    <nav class="navbar navbar-expand-lg navbar-light bg-white/80 backdrop-blur border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-semibold text-decoration-none" href="#">
                <span class="inline-flex items-center gap-2">
                    <span class="w-2.5 h-6 rounded bg-primary-600 inline-block"></span>
                    JobBlue
                </span>
            </a>
        </div>
    </nav>

    <main class="min-h-screen flex items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                    <div class="card shadow-sm border-0 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-12 col-lg-6 order-2 order-lg-1 p-4 p-md-5">
                                <ul class="nav nav-tabs mb-4" id="authTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab" aria-controls="login-pane" aria-selected="true">Log In</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="signup-tab" data-bs-toggle="tab" data-bs-target="#signup-pane" type="button" role="tab" aria-controls="signup-pane" aria-selected="false">Sign Up</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="authTabsContent">
                                    <!-- Login Form -->
                                    <div class="tab-pane fade show active" id="login-pane" role="tabpanel" aria-labelledby="login-tab" tabindex="0">
                                        <form id="loginForm" novalidate>
                                            <div class="mb-3">
                                                <label for="loginUsername" class="form-label">Username</label>
                                                <input type="text" class="form-control" id="loginUsername" placeholder="Enter your username" required>
                                                <div class="invalid-feedback">Username is required.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="loginPassword" class="form-label">Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="loginPassword" placeholder="Enter your password" required>
                                                    <button class="btn btn-outline-secondary" type="button" id="toggleLoginPw" aria-label="Toggle password visibility">Show</button>
                                                    <div class="invalid-feedback">Password is required.</div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                                </div>
                                                <a href="#" class="text-decoration-none small">Forgot password?</a>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">Log In</button>
                                        </form>
                                    </div>

                                    <!-- Sign Up Form -->
                                    <div class="tab-pane fade" id="signup-pane" role="tabpanel" aria-labelledby="signup-tab" tabindex="0">
                                        <form id="signupForm" novalidate>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="signupUsername" class="form-label">Username</label>
                                                    <input type="text" class="form-control" id="signupUsername" placeholder="Choose a username" required>
                                                    <div class="invalid-feedback">Username is required.</div>
                                                </div>
                                                <div class="col-12">
                                                    <label for="signupEmail" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="signupEmail" placeholder="you@example.com" required>
                                                    <div class="invalid-feedback">Valid email is required.</div>
                                                </div>
                                                <div class="col-12">
                                                    <label for="signupPassword" class="form-label">Password</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="signupPassword" placeholder="Create a password" minlength="6" required>
                                                        <button class="btn btn-outline-secondary" type="button" id="toggleSignupPw" aria-label="Toggle password visibility">Show</button>
                                                        <div class="invalid-feedback">Password must be at least 6 characters.</div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">I am a</label>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="role" id="roleClient" value="client" required>
                                                            <label class="form-check-label" for="roleClient">Client</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="role" id="rolePro" value="professional" required>
                                                            <label class="form-check-label" for="rolePro">Blue-collar Professional</label>
                                                        </div>
                                                    </div>
                                                    <div class="invalid-feedback d-block" id="roleFeedback" style="display:none">Please select a role.</div>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 order-1 order-lg-2 bg-primary-600 text-white p-4 p-md-5 d-flex align-items-end">
                                <div>
                                    <h1 class="h3 fw-semibold mb-2">Find blue-collar jobs and talent</h1>
                                    <p class="mb-0 opacity-90">JobBlue connects clients with skilled professionals across construction, maintenance, logistics, and more.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-center text-muted small mt-4">By continuing, you agree to our <a href="#" class="text-decoration-none">Terms</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>.</p>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</body>
</html>

