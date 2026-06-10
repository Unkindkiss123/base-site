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

## Project Structure

```
base-site/
├── assets/                 # Static assets
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   ├── images/            # Images
│   └── uploads/           # User uploaded files
├── config/                # Configuration files
├── database/              # SQL schema and migrations
├── includes/              # Reusable PHP includes
├── templates/             # Reusable template components
├── pages/                 # Frontend pages
├── admin/                 # Admin panel
├── api/                   # API endpoints
├── logs/                  # Application logs
├── storage/               # File storage
├── vendor/                # Third-party libraries
├── .env.example           # Environment file template
├── .gitignore             # Git ignore rules
├── .htaccess              # Apache configuration
├── index.php              # Frontend entry point
├── robots.txt             # SEO robots file
├── sitemap.xml            # XML sitemap
└── README.md              # This file
```

## Installation

### Requirements
- PHP 8.3 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Apache with mod_rewrite enabled
- 50MB disk space minimum

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/base-site.git
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

## Configuration

Edit `.env` file for:
- Database connection
- SMTP/Email settings
- Security settings
- Upload limits
- Rate limiting

## Admin Panel

### Access
- URL: `/admin/login.php`
- Manage all content from one place

### Features
- Dashboard with statistics
- Content management (CRUD operations)
- Media management with validation
- User management with role-based access
- Settings configuration
- Activity logging
- Password reset functionality

### User Roles
- **Super Admin**: Full access to everything
- **Admin**: Content and user management
- **Editor**: Content creation and editing only

## API Endpoints

RESTful API endpoints for content:

- `POST /api/contact.php` - Submit contact form
- `GET /api/services.php` - Get all services
- `GET /api/blog.php` - Get blog posts
- `GET /api/gallery.php` - Get gallery images

## Security Features

✅ Prepared statements (PDO)  
✅ CSRF token validation  
✅ XSS protection (output escaping)  
✅ SQL injection prevention  
✅ Secure password hashing (password_hash)  
✅ Session regeneration  
✅ Secure session cookies  
✅ Content Security Policy  
✅ Security headers  
✅ Rate limiting  
✅ Brute force protection  
✅ File upload validation  
✅ MIME type checking  
✅ Activity logging  
✅ Error logging  

## SEO Optimization

✅ Meta titles and descriptions  
✅ Open Graph tags  
✅ Twitter cards  
✅ Canonical URLs  
✅ XML sitemap  
✅ Robots.txt  
✅ Structured data (Schema.org)  
✅ Breadcrumb navigation  
✅ Mobile-friendly design  

## Performance

- Lazy loading images
- Browser caching enabled
- GZIP compression
- Optimized database queries
- Asset versioning
- Target Lighthouse score: 90+

## Customization

### Branding
1. Edit site name in `.env`
2. Replace logo in `/assets/images/`
3. Update colors in `/assets/css/custom.css`
4. Update favicon in `/includes/head.php`

### Pages
Add new pages by:
1. Creating PHP file in `/pages/`
2. Using template includes from `/templates/`
3. Add navigation link in `/includes/navigation.php`

### Database
Extend database by:
1. Modifying schema in `/database/schema.sql`
2. Creating migration files
3. Updating ORM methods

## Maintenance

- Regular database backups
- Monitor error logs in `/logs/`
- Review activity logs in admin panel
- Keep PHP and dependencies updated
- Regular security audits

## Support & Documentation

Detailed documentation files:
- `docs/INSTALLATION.md` - Full installation guide
- `docs/DEPLOYMENT.md` - Production deployment
- `docs/SECURITY.md` - Security best practices
- `docs/API.md` - API documentation
- `docs/DATABASE.md` - Database schema

## License

MIT License - See LICENSE.md

## Version

**v1.0.0** - Initial Release

---

Built with ❤️ for modern web development
