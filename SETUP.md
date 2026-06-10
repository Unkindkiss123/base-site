# Base Site - Setup Guide

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.3 or higher**
  - Check: `php -v`
  - Install: https://www.php.net/downloads

- **MySQL 5.7 or MariaDB 10.3+**
  - Check: `mysql --version`
  - Install: https://www.mysql.com/downloads/ or https://mariadb.org/download/

- **Composer**
  - Check: `composer --version`
  - Install: https://getcomposer.org/download/

- **Git**
  - Check: `git --version`
  - Install: https://git-scm.com/download

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/base-site.git
cd base-site
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Create Environment File

```bash
cp .env.example .env
```

Then edit `.env` with your database credentials:

```env
DB_HOST="localhost"
DB_NAME="base_site"
DB_USER="your_db_user"
DB_PASSWORD="your_db_password"
DB_PORT="3306"
```

### 4. Create Database

Using MySQL command line:

```bash
mysql -u root -p
```

Then in MySQL:

```sql
CREATE DATABASE base_site CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE base_site;
```

### 5. Run Migrations

```bash
php migrate.php
```

This will create all necessary tables and populate initial data.

### 6. Start Development Server

```bash
php -S localhost:8000
```

Or use the npm script:

```bash
npm run dev:server
```

### 7. Access the Application

- **Frontend:** http://localhost:8000
- **Admin Panel:** http://localhost:8000/admin

## Initial Login

Default admin credentials:
- **Email:** admin@example.com
- **Password:** password

**⚠️ Important:** Change these credentials immediately after first login!

## Configuration

### Database Configuration

Edit `config/database.php` or use `.env` file:

```php
// Database connection details
DB_HOST = 'localhost'
DB_NAME = 'base_site'
DB_USER = 'root'
DB_PASSWORD = ''
DB_PORT = 3306
```

### Application Settings

Edit `config/settings.php`:

```php
// Site information
APP_NAME = 'Your Site Name'
APP_DESCRIPTION = 'Your site description'
APP_EMAIL = 'contact@yoursite.com'
APP_PHONE = '+1 (555) 000-0000'
APP_ADDRESS = '123 Main St, City, State 12345'
```

### Email Configuration (Optional)

For contact form emails, configure SMTP in `.env`:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

## Directory Permissions

On Linux/Mac, set proper permissions:

```bash
chmod -R 755 .
chmod -R 777 uploads/
chmod -R 777 logs/
```

## Troubleshooting

### Database Connection Error

**Issue:** "SQLSTATE[HY000]: General error: connection refused"

**Solution:**
1. Check MySQL is running: `mysql -u root -p`
2. Verify credentials in `.env`
3. Ensure database exists: `SHOW DATABASES;`

### PHP Version Error

**Issue:** "PHP version must be 8.3 or higher"

**Solution:**
1. Check current PHP version: `php -v`
2. Update PHP: https://www.php.net/downloads
3. On macOS, use Homebrew: `brew install php@8.3`

### Missing Dependencies

**Issue:** "Class not found" or "Cannot find module"

**Solution:**
```bash
composer install
composer update
```

### File Permissions

**Issue:** "Permission denied" when accessing site

**Solution:**
```bash
chmod -R 755 .
chmod -R 777 uploads/
chmod -R 777 logs/
```

### .htaccess Not Working

**Issue:** Rewrite module not enabled

**Solution:**
1. Enable mod_rewrite:
   ```bash
   a2enmod rewrite
   systemctl restart apache2
   ```

## Updating the Site

### Pull Latest Changes

```bash
git pull origin main
composer update
```

### Database Migrations

```bash
php migrate.php
```

## Production Deployment

### Pre-Deployment Checklist

- [ ] Update `.env` with production values
- [ ] Set `APP_ENV=production`
- [ ] Set `DEBUG=false`
- [ ] Change admin password
- [ ] Update database credentials
- [ ] Enable HTTPS
- [ ] Set proper file permissions
- [ ] Test all functionality

### Deployment Steps

1. **Upload files to server**
   ```bash
   scp -r base-site/ user@server:/var/www/
   ```

2. **Install dependencies**
   ```bash
   cd /var/www/base-site
   composer install --no-dev
   ```

3. **Set environment**
   ```bash
   cp .env.example .env
   # Edit .env with production values
   ```

4. **Set permissions**
   ```bash
   chmod -R 755 .
   chmod -R 777 uploads/
   chmod -R 777 logs/
   ```

5. **Run migrations**
   ```bash
   php migrate.php
   ```

## Getting Help

- Check documentation: [docs/](docs/)
- Report issues: [GitHub Issues](https://github.com/yourusername/base-site/issues)
- Email support: support@example.com

## Next Steps

1. Customize branding and colors
2. Add your content and pages
3. Configure email notifications
4. Set up analytics
5. Deploy to production

---

**Happy coding! 🚀**
