// Modern Creative Professional Web App - Main JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all components
    initializeMobileMenu();
    initializeAnimations();
    initializeFormValidation();
    initializeCards();
    initializeSmoothScrolling();
    initializeLoadingStates();
    initializeUserChoice();
    initializeLoginForm();
    initializeSignupForm();
    initializeFooter();
    
    console.log('🚀 Servisyo Hub Web App loaded successfully!');
});

// Mobile Menu Toggle
function initializeMobileMenu() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navbarNav = document.querySelector('.navbar-nav');
    
    if (mobileMenuToggle && navbarNav) {
        mobileMenuToggle.addEventListener('click', function() {
            navbarNav.classList.toggle('active');
            
            // Animate hamburger icon
            const icon = this.querySelector('i') || this;
            if (navbarNav.classList.contains('active')) {
                icon.innerHTML = '✕';
                icon.style.transform = 'rotate(90deg)';
            } else {
                icon.innerHTML = '☰';
                icon.style.transform = 'rotate(0deg)';
            }
        });
        
        // Close menu when clicking on links
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navbarNav.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i') || mobileMenuToggle;
                icon.innerHTML = '☰';
                icon.style.transform = 'rotate(0deg)';
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenuToggle.contains(event.target) && !navbarNav.contains(event.target)) {
                navbarNav.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i') || mobileMenuToggle;
                icon.innerHTML = '☰';
                icon.style.transform = 'rotate(0deg)';
            }
        });
    }
}

// Smooth Animations and Intersection Observer
function initializeAnimations() {
    // Fade in animation for cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all cards and sections
    const animatedElements = document.querySelectorAll('.card, .section-title, .hero-content');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
    
    // Button hover effects
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
        
        // Ripple effect on click
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm(this)) {
                showLoading(this);
                // Simulate form submission
                setTimeout(() => {
                    hideLoading(this);
                    showSuccess('Form submitted successfully!');
                    this.reset();
                }, 2000);
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let errorMessage = '';
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        errorMessage = 'This field is required';
        isValid = false;
    }
    
    // Email validation
    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            errorMessage = 'Please enter a valid email address';
            isValid = false;
        }
    }
    
    // Password validation
    if (type === 'password' && value) {
        if (value.length < 8) {
            errorMessage = 'Password must be at least 8 characters long';
            isValid = false;
        }
    }
    
    // Phone validation
    if (type === 'tel' && value) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
            errorMessage = 'Please enter a valid phone number';
            isValid = false;
        }
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    } else {
        clearFieldError(field);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#991B1B';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Card Interactions
function initializeCards() {
    const cards = document.querySelectorAll('.card');
    
    cards.forEach(card => {
        // Add hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) rotateX(5deg)';
            this.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) rotateX(0deg)';
            this.style.boxShadow = '0 4px 6px var(--shadow-subtle)';
        });
        
        // Add click effects for interactive cards
        const cardButtons = card.querySelectorAll('.btn');
        if (cardButtons.length > 0) {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('.btn')) {
                    // If clicking on the card but not on a button, add a subtle effect
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-8px) rotateX(5deg)';
                    }, 150);
                }
            });
        }
    });
}

// Smooth Scrolling
function initializeSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80; // Account for navbar
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Loading States
function initializeLoadingStates() {
    // Add loading styles
    const style = document.createElement('style');
    style.textContent = `
        .btn.loading {
            position: relative;
            color: transparent;
        }
        
        .btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .field-error {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .form-input.error {
            border-color: #991B1B;
            box-shadow: 0 0 0 3px rgba(153, 27, 27, 0.1);
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
}

// Utility Functions
function showLoading(element) {
    if (element.tagName === 'FORM') {
        const submitBtn = element.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
    } else {
        element.classList.add('loading');
        element.disabled = true;
    }
}

function hideLoading(element) {
    if (element.tagName === 'FORM') {
        const submitBtn = element.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    } else {
        element.classList.remove('loading');
        element.disabled = false;
    }
}

function showSuccess(message) {
    showAlert(message, 'success');
}

function showError(message) {
    showAlert(message, 'error');
}

function showInfo(message) {
    showAlert(message, 'info');
}

function showAlert(message, type) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-toast');
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-toast`;
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    `;
    alert.textContent = message;
    
    document.body.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
    
    // Add slide animations
    if (!document.querySelector('#alert-animations')) {
        const style = document.createElement('style');
        style.id = 'alert-animations';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}

// Search functionality (if needed)
function initializeSearch() {
    const searchInputs = document.querySelectorAll('input[type="search"]');
    
    searchInputs.forEach(input => {
        let searchTimeout;
        
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    });
}

function performSearch(query) {
    // Implement search logic here
    console.log('Searching for:', query);
}

// Dark mode toggle (bonus feature)
function initializeDarkMode() {
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });
        
        // Load saved preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    }
}

// Performance monitoring
function initializePerformanceMonitoring() {
    // Monitor page load performance
    window.addEventListener('load', function() {
        setTimeout(() => {
            const perfData = performance.getEntriesByType('navigation')[0];
            console.log('Page load time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
        }, 0);
    });
}

// Initialize additional features
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeDarkMode();
    initializePerformanceMonitoring();
});

// User Choice Functionality
function initializeUserChoice() {
    const choiceCards = document.querySelectorAll('.choice-card');
    
    choiceCards.forEach(card => {
        card.addEventListener('click', function() {
            const choice = this.dataset.choice;
            
            // Store user choice in session storage
            sessionStorage.setItem('userChoice', choice);
            
            // Add visual feedback
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
            
            // Redirect based on choice
            setTimeout(() => {
                if (choice === 'services') {
                    window.location.href = 'signup.php';
                } else if (choice === 'job') {
                    window.location.href = 'registration.php';
                }
            }, 300);
        });
    });
}

// Login Form Functionality
function initializeLoginForm() {
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.querySelector('input[name="remember"]').checked;
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Signing In...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // For demo purposes, accept any email/password
                if (email && password) {
                    // Simulate successful login
                    WebApp.showSuccess('Login successful! Redirecting...');
                    
                    setTimeout(() => {
                        // In a real app, you would set session variables here
                        // $_SESSION['user_id'] = user_id;
                        // $_SESSION['email'] = email;
                        window.location.href = '../pages/home.php';
                    }, 1500);
                } else {
                    WebApp.showError('Please enter both email and password');
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            }, 2000);
        });
        
        // Social login buttons
        document.querySelectorAll('.btn-social').forEach(btn => {
            btn.addEventListener('click', function() {
                WebApp.showInfo('Social login integration coming soon!');
            });
        });
        
        // Forgot password link
        const forgotLink = document.querySelector('.forgot-link');
        if (forgotLink) {
            forgotLink.addEventListener('click', function(e) {
                e.preventDefault();
                WebApp.showInfo('Password reset functionality coming soon!');
            });
        }
    }
}

// Signup Form Functionality
function initializeSignupForm() {
    const signupForm = document.getElementById('signup-form');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    if (signupForm && passwordInput) {
        // Password strength checker
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            const strengthText = document.getElementById('strength-text');
            
            // Remove all strength classes
            this.parentElement.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');
            
            // Add appropriate class
            if (strength.score > 0) {
                this.parentElement.classList.add(strength.class);
            }
            
            if (strengthText) {
                strengthText.textContent = strength.text;
            }
        });
        
        // Password confirmation checker
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    this.setCustomValidity('Passwords do not match');
                    this.classList.add('error');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('error');
                }
            });
        }
        
        // Form submission
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const password = formData.get('password');
            const confirmPassword = formData.get('confirmPassword');
            
            // Validate password match
            if (password !== confirmPassword) {
                WebApp.showError('Passwords do not match');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                WebApp.showSuccess('Account created successfully! Redirecting to login...');
                
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            }, 2000);
        });
        
        // Social signup buttons
        document.querySelectorAll('.btn-social').forEach(btn => {
            btn.addEventListener('click', function() {
                WebApp.showInfo('Social signup integration coming soon!');
            });
        });
    }
}

// Password Strength Checker
function checkPasswordStrength(password) {
    let score = 0;
    let feedback = [];
    
    // Length check
    if (password.length >= 8) score++;
    else feedback.push('at least 8 characters');
    
    // Lowercase check
    if (/[a-z]/.test(password)) score++;
    else feedback.push('lowercase letters');
    
    // Uppercase check
    if (/[A-Z]/.test(password)) score++;
    else feedback.push('uppercase letters');
    
    // Number check
    if (/\d/.test(password)) score++;
    else feedback.push('numbers');
    
    // Special character check
    if (/[^A-Za-z0-9]/.test(password)) score++;
    else feedback.push('special characters');
    
    let strength = {
        score: score,
        class: '',
        text: ''
    };
    
    if (score < 2) {
        strength.class = 'strength-weak';
        strength.text = 'Weak password';
    } else if (score < 3) {
        strength.class = 'strength-fair';
        strength.text = 'Fair password';
    } else if (score < 4) {
        strength.class = 'strength-good';
        strength.text = 'Good password';
    } else {
        strength.class = 'strength-strong';
        strength.text = 'Strong password';
    }
    
    if (feedback.length > 0) {
        strength.text += ' - Add ' + feedback.slice(0, 2).join(', ');
    }
    
    return strength;
}

// Footer Functionality
function initializeFooter() {
    // Scroll to top functionality
    window.scrollToTop = function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    // Show/hide back to top button
    window.addEventListener('scroll', function() {
        const backToTop = document.querySelector('.back-to-top');
        if (backToTop) {
            if (window.scrollY > 300) {
                backToTop.style.opacity = '1';
                backToTop.style.visibility = 'visible';
            } else {
                backToTop.style.opacity = '0';
                backToTop.style.visibility = 'hidden';
            }
        }
    });

    // Newsletter form submission
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            // Show loading state
            const button = this.querySelector('button');
            const originalText = button.textContent;
            button.textContent = 'Subscribing...';
            button.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                button.textContent = 'Subscribed!';
                button.style.backgroundColor = '#10B981';
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                    button.style.backgroundColor = '';
                    this.reset();
                }, 2000);
            }, 1000);
        });
    }
}

// Export functions for global use
window.WebApp = {
    showSuccess,
    showError,
    showInfo,
    showLoading,
    hideLoading
};