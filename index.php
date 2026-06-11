<?php
/**
 * index.php — Front controller.
 *
 * Supports both Apache (with .htaccess rewriting) and the PHP built-in server
 * (`php -S 0.0.0.0:8000 index.php`).
 */

// Built-in server: serve real static files directly
if (php_sapi_name() === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $uri;
    if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
        return false;
    }
}

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/Helper.php';
require_once __DIR__ . '/includes/Security.php';

// Attempt remember-me auto-login (only fires if cookie is present and session isn't already authed)
if (!empty($_COOKIE['remember_token']) && empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/includes/Auth.php';
    (new Auth())->tryRememberLogin();
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = '/' . trim($path, '/');
if ($path === '/') { $path = '/'; }

/* ---------- Static page map ---------- */
$staticPages = [
    '/'               => 'pages/index.php',
    '/about'          => 'pages/about.php',
    '/services'       => 'pages/services.php',
    '/portfolio'      => 'pages/portfolio.php',
    '/blog'           => 'pages/blog.php',
    '/contact'        => 'pages/contact.php',
    '/privacy-policy' => 'pages/privacy-policy.php',
    '/sitemap.xml'    => 'sitemap.php',
];

/* ---------- Auth routes ---------- */
$authRoutes = [
    '/login'            => 'admin/login.php',
    '/logout'           => 'admin/logout.php',
    '/forgot-password'  => 'pages/forgot-password.php',
    '/reset-password'   => 'pages/reset-password.php',
    '/2fa'              => 'pages/two-factor.php',
];

/* ---------- Admin routes ---------- */
$adminRoutes = [
    '/admin'              => 'admin/index.php',
    '/admin/users'        => 'admin/users.php',
    '/admin/pages'        => 'admin/pages.php',
    '/admin/blog'         => 'admin/blog.php',
    '/admin/services'     => 'admin/services.php',
    '/admin/gallery'      => 'admin/gallery.php',
    '/admin/leads'        => 'admin/leads.php',
    '/admin/settings'     => 'admin/settings.php',
    '/admin/profile'      => 'admin/profile.php',
    '/admin/uploads'      => 'admin/uploads.php',
];

/* ---------- Resolve ---------- */
$routes = $staticPages + $authRoutes + $adminRoutes;

if (isset($routes[$path])) {
    require __DIR__ . '/' . $routes[$path];
    return;
}

// Blog single post: /blog/{slug}
if (preg_match('#^/blog/([a-z0-9\-]+)$#i', $path, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/pages/blog-post.php';
    return;
}

// Reset password with token: /reset-password/{token}
if (preg_match('#^/reset-password/([a-f0-9]+)$#i', $path, $m)) {
    $_GET['token'] = $m[1];
    require __DIR__ . '/pages/reset-password.php';
    return;
}

// Verify email with token: /verify-email/{token}
if (preg_match('#^/verify-email/([a-f0-9]+)$#i', $path, $m)) {
    $_GET['token'] = $m[1];
    require __DIR__ . '/pages/verify-email.php';
    return;
}

// 404
http_response_code(404);
require __DIR__ . '/pages/404.php';
