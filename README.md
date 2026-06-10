# Base Site - Production Ready Website Starter Framework

A complete, production-ready website starter framework built with HTML5, CSS3, Bootstrap 5, PHP 8.3+, and MySQL/MariaDB.

## Features

✅ **Frontend**
- Mobile-first responsive design
- 11+ professional pages
- Reusable components (header, footer, navigation, hero, CTA, cards, etc.)
- Bootstrap 5 integration
- SEO optimized
- WCAG accessibility compliant
- Lazy loading images

✅ **Backend**
- Secure admin panel with role-based access control
- Complete authentication system (login, logout, password reset)
- Content management (pages, services, blog, gallery, testimonials, FAQ)
- Media management with validation
- Settings management
- Activity logging

✅ **Security**
- CSRF protection
- XSS prevention
- SQL injection protection (prepared statements)
- Brute force protection
- Secure password hashing
- Session management
- Security headers (CSP, X-Frame-Options, etc.)
- Rate limiting

✅ **Database**
- Complete normalized schema
- 14+ tables with proper relationships
- Audit fields (created_at, updated_at)
- Indexes and constraints

✅ **Performance**
- Optimized queries
- Caching headers
- Asset versioning
- Minified assets ready
- Lazy loading

## Tech Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, Vanilla JavaScript
- **Backend**: PHP 8.3+
- **Database**: MySQL 5.7+ / MariaDB 10.4+
- **No external frameworks** - Clean, maintainable code

## Installation

### Requirements
- PHP 8.3 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Apache with mod_rewrite enabled
- 50MB disk space minimum

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/Unkindkiss123/base-site.git
   cd base-site
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database and SMTP credentials
   ```

3. **Create database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

4. **Set permissions**
   ```bash
   chmod 755 logs/
   chmod 755 storage/
   chmod 755 assets/uploads/
   ```

5. **Access the site**
   - Frontend: `http://localhost/base-site`
   - Admin: `http://localhost/base-site/admin`
   - Default credentials: `admin@example.com` / `Password123!`

## Project Structure

```
base-site/
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   ├── images/           # Images
│   └── uploads/          # User uploads
├── config/               # Configuration
├── database/             # SQL schema
├── includes/             # Reusable includes
├── templates/            # Template components
├── pages/                # Frontend pages
├── admin/                # Admin panel
├── api/                  # API endpoints
├── logs/                 # Application logs
├── storage/              # File storage
├── vendor/               # Libraries
├── .env.example          # Environment template
├── .htaccess             # Apache config
├── index.php             # Frontend entry
├── robots.txt            # SEO robots
└── sitemap.xml           # XML sitemap
```

## Admin Panel

- **Access**: `/admin/login.php`
- **Manage**: Content, users, media, settings
- **Roles**: Super Admin, Admin, Editor

## Security Features

✅ Prepared statements (PDO)
✅ CSRF protection
✅ XSS prevention
✅ SQL injection prevention
✅ Secure password hashing
✅ Session management
✅ Security headers
✅ Rate limiting
✅ Activity logging

## Version

**v1.0.0** - Initial Release

Built with ❤️ for modern web development
