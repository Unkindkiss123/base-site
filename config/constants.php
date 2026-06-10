<?php
/**
 * config/constants.php
 * Global application constants
 */

// Application
define('APP_NAME', getenv('APP_NAME') ?: 'Base Site');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// Directories
define('BASE_PATH', dirname(dirname(__FILE__)));
define('CONFIG_PATH', BASE_PATH . '/config');
define('DATABASE_PATH', BASE_PATH . '/database');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('TEMPLATES_PATH', BASE_PATH . '/templates');
define('PAGES_PATH', BASE_PATH . '/pages');
define('ADMIN_PATH', BASE_PATH . '/admin');
define('API_PATH', BASE_PATH . '/api');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('LOGS_PATH', BASE_PATH . '/logs');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VENDOR_PATH', BASE_PATH . '/vendor');

// URLs
define('ASSETS_URL', APP_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');
define('UPLOADS_URL', ASSETS_URL . '/uploads');

// Database
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'base_site');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
define('DB_DSN', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// Security
define('SESSION_NAME', getenv('SESSION_NAME') ?: 'PHPSESSID');
define('SESSION_LIFETIME', (int)getenv('SESSION_LIFETIME') ?: 3600);
define('CSRF_TOKEN_LENGTH', (int)getenv('CSRF_TOKEN_LENGTH') ?: 32);
define('PASSWORD_RESET_TIMEOUT', (int)getenv('PASSWORD_RESET_TIMEOUT') ?: 3600);

// File Upload
define('MAX_UPLOAD_SIZE', (int)getenv('MAX_UPLOAD_SIZE') ?: 5242880);
define('ALLOWED_IMAGES', explode(',', getenv('ALLOWED_IMAGES') ?: 'jpg,jpeg,png,gif,webp'));
define('ALLOWED_FILES', explode(',', getenv('ALLOWED_FILES') ?: 'pdf,doc,docx,xls,xlsx,zip'));

// Rate Limiting
define('RATE_LIMIT_ENABLED', getenv('RATE_LIMIT_ENABLED') === 'true');
define('RATE_LIMIT_ATTEMPTS', (int)getenv('RATE_LIMIT_ATTEMPTS') ?: 5);
define('RATE_LIMIT_MINUTES', (int)getenv('RATE_LIMIT_MINUTES') ?: 15);

// Logging
define('LOG_CHANNEL', getenv('LOG_CHANNEL') ?: 'file');
define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'info');

// User Roles
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_EDITOR', 'editor');
define('ROLE_GUEST', 'guest');

// HTTP Status Codes
define('STATUS_OK', 200);
define('STATUS_CREATED', 201);
define('STATUS_BAD_REQUEST', 400);
define('STATUS_UNAUTHORIZED', 401);
define('STATUS_FORBIDDEN', 403);
define('STATUS_NOT_FOUND', 404);
define('STATUS_SERVER_ERROR', 500);

// Pagination
define('ITEMS_PER_PAGE', 20);
define('MAX_ITEMS_PER_PAGE', 100);

// Cache
define('CACHE_ENABLED', APP_ENV === 'production');
define('CACHE_DURATION', 3600); // 1 hour

// API Rate Limiting
define('API_RATE_LIMIT_REQUESTS', 100);
define('API_RATE_LIMIT_WINDOW', 3600); // 1 hour
