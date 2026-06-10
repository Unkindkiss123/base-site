# Base Site - Professional Website Template

A modern, professional website template built with HTML5, CSS3, Bootstrap 5, and PHP 8.3+. Perfect for startups, agencies, and businesses looking for a solid foundation.

## Features

✅ **Responsive Design** - Mobile-first approach works on all devices
✅ **Modern Stack** - HTML5, CSS3, Bootstrap 5, PHP 8.3+
✅ **Database Ready** - MySQL/MariaDB integration
✅ **Admin Panel** - Complete CMS for managing content
✅ **Security** - CSRF protection, SQL injection prevention, secure authentication
✅ **SEO Optimized** - Clean URLs, meta tags, structured data
✅ **Fast Performance** - Optimized assets, lazy loading, caching
✅ **Easy to Customize** - Well-organized code, clear structure

## Quick Start

### Requirements

- PHP 8.3 or higher
- MySQL 5.7 or MariaDB 10.3+
- Composer
- Node.js (optional, for frontend tooling)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/base-site.git
   cd base-site
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install  # Optional
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Create database and run migrations**
   ```bash
   php migrate.php
   ```

5. **Start development server**
   ```bash
   php -S localhost:8000
   ```

6. **Access the site**
   - Frontend: http://localhost:8000
   - Admin: http://localhost:8000/admin
   - Default login: admin@example.com / password

## Project Structure

```
base-site/
├── admin/                 # Admin panel
│   ├── index.php         # Dashboard
│   ├── login.php         # Login page
│   ├── users.php         # User management
│   ├── blog.php          # Blog management
│   ├── pages.php         # Page management
│   ├── leads.php         # Lead management
│   └── settings.php      # Settings
├── pages/                # Frontend pages
│   ├── index.php         # Homepage
│   ├── about.php         # About page
│   ├── services.php      # Services page
│   ├── portfolio.php     # Portfolio
│   ├── blog.php          # Blog listing
│   ├── contact.php       # Contact form
│   └── privacy-policy.php # Privacy policy
├── assets/               # Static files
│   ├── css/             # Stylesheets
│   │   ├── style.css    # Main styles
│   │   └── custom.css   # Custom styles
│   ├── js/              # JavaScript
│   │   └── main.js      # Main script
│   └── images/          # Images
├── config/              # Configuration
│   ├── constants.php    # App constants
│   ├── database.php     # Database config
│   └── settings.php     # Site settings
├── includes/            # Reusable components
│   ├── head.php         # HTML head
│   ├── navigation.php   # Navigation
│   ├── footer.php       # Footer
│   ├── Helper.php       # Helper functions
│   ├── Auth.php         # Authentication
│   ├── Security.php     # Security functions
│   └── Validator.php    # Form validation
├── .env.example         # Environment template
├── .gitignore           # Git ignore rules
├── composer.json        # PHP dependencies
├── package.json         # Node dependencies
├── README.md            # This file
└── migrate.php          # Database migrations
```

## Configuration

### Environment Variables (.env)

```env
APP_NAME="Base Site"
APP_URL="http://localhost:8000"
APP_ENV="development"

DB_HOST="localhost"
DB_NAME="base_site"
DB_USER="root"
DB_PASSWORD=""
DB_PORT="3306"

MAIL_HOST="smtp.mailtrap.io"
MAIL_PORT="587"
MAIL_USER="your_email"
MAIL_PASSWORD="your_password"
```

## Usage

### Frontend

The frontend is fully responsive and includes:
- Homepage with hero section and features
- About page with team members
- Services page
- Portfolio/Projects showcase
- Blog with article management
- Contact form (saves leads to database)
- Privacy policy

### Admin Panel

Access admin at `/admin` with default credentials:
- **Email:** admin@example.com
- **Password:** password

**Features:**
- Dashboard with statistics
- User management
- Blog post management
- Page management
- Lead management
- Site settings

## Database Schema

The template includes tables for:
- `users` - Admin users
- `roles` - User roles (admin, editor, etc.)
- `blog_posts` - Blog articles
- `pages` - Static pages
- `leads` - Contact form submissions
- `categories` - Blog categories

## Security Features

✅ CSRF token protection on all forms
✅ SQL injection prevention with prepared statements
✅ XSS protection with output escaping
✅ Password hashing with bcrypt
✅ Session-based authentication
✅ Rate limiting on login attempts
✅ IP logging for auditing
✅ Input validation and sanitization

## API & Hooks

The template includes helper functions for common tasks:

```php
// Authentication
isAuthenticated()      // Check if user is logged in
getUser()             // Get current user data
hasPermission($perm)  // Check user permission

// Helpers
e($value)             // Escape output
url($path)            // Generate URL
asset($path)          // Asset URL
flash($type, $msg)    // Set flash message
redirect($url)        // Redirect

// Database
$db = Database::getInstance()  // Get DB instance
$db->prepare($sql)             // Prepare query
$db->execute()                 // Execute query
$db->fetch()                   // Fetch one row
$db->fetchAll()                // Fetch all rows
```

## Customization

### Colors

Edit CSS variables in `assets/css/style.css`:

```css
:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    /* ... more colors ... */
}
```

### Branding

1. Update `APP_NAME` in `config/constants.php`
2. Replace logo in `includes/navigation.php`
3. Update colors in CSS files
4. Add your company info in `config/settings.php`

## Deployment

### Hosting Requirements

- PHP 8.3+
- MySQL 5.7+
- mod_rewrite enabled (for clean URLs)
- HTTPS support

### Steps

1. Upload files to your hosting
2. Create database
3. Configure `.env` file
4. Run migrations
5. Set proper file permissions
6. Update DNS records

## Performance

- Lazy loading for images
- CSS/JS minification ready
- Database query optimization
- Caching headers configured
- CDN-ready asset structure

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Support

For support, email support@example.com or visit our contact page.

## License

This project is licensed under the MIT License - see LICENSE file for details.

## Changelog

### Version 1.0.0 (June 2026)
- Initial release
- Frontend pages
- Admin panel
- Database integration
- Security features

## Roadmap

- [ ] REST API
- [ ] User registration
- [ ] Email notifications
- [ ] Advanced analytics
- [ ] Multi-language support
- [ ] Dark mode

---

**Made with ❤️ by Base Site Team**
