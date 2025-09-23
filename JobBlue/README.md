# Job Blue - Job Finding App

A responsive job finding app for clients and blue-collar professionals with login/signup and home dashboard.

## Features
- 🔐 Login/Signup with role selection (Client or Blue-collar professional)
- 📱 Fully responsive design (mobile-first with desktop layout)
- 🎨 Custom color palette with dark theme
- 🍔 Hamburger menu with slide-out drawer
- 🏠 Home dashboard with categories and nearby professionals
- ⚡ Fast loading with optimized assets

## Quick Start

### Local Development
```bash
# Navigate to project directory
cd JobBlue

# Start PHP development server
php -S localhost:8000 -t .

# Open http://localhost:8000 in your browser
```

### GitHub Pages Deployment
1. Push to GitHub repository
2. Enable GitHub Pages in repository settings
3. Set source to "Deploy from a branch" → "main"
4. Access at: `https://yourusername.github.io/your-repo-name`

**Note**: GitHub Pages serves static files only. For full PHP functionality, use:
- Netlify Functions
- Vercel with PHP runtime
- Traditional web hosting with PHP support

## File Structure
```
JobBlue/
├── index.html          # GitHub Pages entry point
├── index.php           # Login/Signup page
├── home.php            # Dashboard page
├── .htaccess           # URL rewriting & security
├── includes/           # Shared PHP components
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   └── config.php
└── assets/
    ├── css/style.css   # Responsive styles
    ├── js/script.js    # Interactive features
    └── images/         # Logo and assets
```

## Tech Stack
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 8.4+ (for local development)
- **Responsive**: Mobile-first design with desktop enhancements
- **Security**: Basic security headers via .htaccess

## Development Notes
- No database/auth integration yet - forms redirect to home.php temporarily
- Ready for backend integration with session management
- Optimized for both mobile and desktop experiences


