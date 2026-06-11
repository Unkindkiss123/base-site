<?php
/**
 * includes/Security.php
 * Security utilities for CSRF, XSS, SQL injection protection
 */

require_once __DIR__ . '/../config/constants.php';

class Security {
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get CSRF token for forms
     */
    public static function getCSRFField() {
        return '<input type="hidden" name="csrf_token" value="' . self::generateCSRFToken() . '" />';
    }

    /**
     * Escape output for HTML context
     */
    public static function escape($data, $flags = ENT_QUOTES, $encoding = 'UTF-8') {
        return htmlspecialchars($data, $flags, $encoding);
    }

    /**
     * Sanitize input - trim only. Output escaping is the XSS defense.
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return is_string($data) ? trim($data) : $data;
    }

    /**
     * Validate email address
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     */
    public static function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate IP address
     */
    public static function validateIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password against hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Get client IP address.
     * Only trusts proxy headers when TRUSTED_PROXIES=true is set.
     */
    public static function getClientIP() {
        $trustProxy = (getenv('TRUSTED_PROXIES') === 'true');
        $ip = '';
        if ($trustProxy && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ($trustProxy && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        return self::validateIP($ip) ? $ip : '0.0.0.0';
    }

    /**
     * Set secure session cookie
     */
    public static function setSessionCookie() {
        // Strip port from host; for localhost / no-host, leave domain empty
        $host = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            $host = '';
        }
        $cookieOptions = [
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => $host,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        session_set_cookie_params($cookieOptions);
    }

    /**
     * Regenerate session ID
     */
    public static function regenerateSessionID() {
        session_regenerate_id(true);
    }

    /**
     * Set security headers
     */
    public static function setSecurityHeaders() {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        // HSTS only over HTTPS to avoid pinning a broken state on dev
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        header(
            "Content-Security-Policy: default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tiny.cloud; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.tiny.cloud; " .
            "img-src 'self' data: https: blob:; " .
            "font-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.tiny.cloud data:; " .
            "connect-src 'self' https://cdn.tiny.cloud https://*.tinymce.com"
        );
    }
}
