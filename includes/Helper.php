<?php
/**
 * includes/Helper.php
 * Global helper functions
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/Security.php';

// Always ensure a session is started before any code reads $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    Security::setSessionCookie();
    session_start();
}

/**
 * Escape output for HTML
 */
if (!function_exists('e')) {
    function e($data) {
        return Security::escape($data);
    }
}

/**
 * Get configuration value
 */
if (!function_exists('config')) {
    function config($key, $default = null) {
        $keys = explode('.', $key);
        $value = $GLOBALS;
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

/**
 * Redirect to URL
 */
if (!function_exists('redirect')) {
    function redirect($url, $code = 302) {
        header('Location: ' . $url, true, $code);
        exit();
    }
}

/**
 * Check if request is POST
 */
if (!function_exists('isPost')) {
    function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}

/**
 * Check if request is GET
 */
if (!function_exists('isGet')) {
    function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}

/**
 * Get POST data
 */
if (!function_exists('post')) {
    function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
}

/**
 * Get GET data
 */
if (!function_exists('get')) {
    function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
}

/**
 * Set flash message
 */
if (!function_exists('flash')) {
    function flash($type = null, $message = null) {
        if ($type !== null && $message !== null) {
            $_SESSION['flash'] = [
                'type' => $type,
                'message' => $message,
            ];
        }
        return $_SESSION['flash'] ?? null;
    }
}

/**
 * Get and clear flash message
 */
if (!function_exists('getFlash')) {
    function getFlash() {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}

/**
 * Check if user is authenticated
 */
if (!function_exists('isAuthenticated')) {
    function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

/**
 * Check if user has role (matches role slug, not display name)
 */
if (!function_exists('hasRole')) {
    function hasRole($role) {
        return isset($_SESSION['user_role_name']) && $_SESSION['user_role_name'] === $role;
    }
}

/**
 * Check if user has permission
 */
if (!function_exists('hasPermission')) {
    function hasPermission($permission) {
        return isset($_SESSION['user_permissions']) && in_array($permission, $_SESSION['user_permissions']);
    }
}

/**
 * Get authenticated user data (set during login)
 */
if (!function_exists('getUser')) {
    function getUser() {
        return $_SESSION['user_data'] ?? null;
    }
}

/**
 * Get a setting value from the DB-backed settings table (cached per request)
 */
if (!function_exists('setting')) {
    function setting($key, $default = '') {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            try {
                $db = Database::getInstance();
                $db->prepare('SELECT `key`, `value` FROM settings');
                foreach ($db->fetchAll() as $row) {
                    $cache[$row['key']] = $row['value'];
                }
            } catch (Throwable $e) {
                // Table may not exist during install — fail soft
                $cache = [];
            }
        }
        return $cache[$key] ?? $default;
    }
}

/**
 * Render admin-authored HTML safely. Allow a sensible subset of tags;
 * strip <script>, on* attributes, javascript: URIs, <iframe>, <object>, etc.
 */
if (!function_exists('safe_html')) {
    function safe_html($html) {
        if ($html === null || $html === '') return '';
        // 1. Drop script/style/iframe/object/embed/form/svg-as-script wholesale
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|form|link|meta)\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html);
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|link|meta)\b[^>]*/?>#i', '', $html);
        // 2. Strip on* event attributes
        $html = preg_replace('#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
        // 3. Neutralize javascript: / data: (non-image) URIs in href/src
        $html = preg_replace_callback(
            '#(\s(?:href|src|action|formaction)\s*=\s*)("|\')(\s*(?:javascript|vbscript|data)(?!:image/[a-z]+;)[^"\']*)\2#i',
            fn($m) => $m[1] . $m[2] . '#' . $m[2],
            $html
        );
        // 4. Whitelist tags (allow only this list)
        $allowed = '<p><br><strong><b><em><i><u><s><a><ul><ol><li><blockquote><code><pre>'
                 . '<h1><h2><h3><h4><h5><h6><hr><img><figure><figcaption>'
                 . '<table><thead><tbody><tr><th><td><span><div>';
        return strip_tags($html, $allowed);
    }
}

/**
 * URL helper
 */
if (!function_exists('url')) {
    function url($path = '') {
        return APP_URL . '/' . ltrim($path, '/');
    }
}

/**
 * Asset URL helper
 */
if (!function_exists('asset')) {
    function asset($path) {
        return ASSETS_URL . '/' . ltrim($path, '/');
    }
}

/**
 * Format date
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'Y-m-d H:i:s') {
        if (is_string($date)) {
            $date = strtotime($date);
        }
        return date($format, $date);
    }
}

/**
 * Format currency
 */
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = 'USD') {
        return number_format($amount, 2) . ' ' . $currency;
    }
}

/**
 * Truncate text
 */
if (!function_exists('truncate')) {
    function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length - strlen($suffix)) . $suffix;
        }
        return $text;
    }
}

/**
 * Generate slug from text
 */
if (!function_exists('slug')) {
    function slug($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim(preg_replace('~-+~', '-', $text), '-');
        return strtolower($text);
    }
}
