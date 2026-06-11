<?php
/**
 * includes/Auth.php
 * Authentication handler
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/RateLimiter.php';
require_once __DIR__ . '/Mailer.php';
require_once __DIR__ . '/Totp.php';

class Auth {
    private $db;
    private $logger;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = new Logger('auth.log');
    }

    /**
     * Register new user
     */
    public function register($email, $username, $password, $firstName = '', $lastName = '') {
        // Validate inputs
        if (!Security::validateEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
            return ['success' => false, 'message' => 'Username must be 3-50 chars (letters, digits, _ . -)'];
        }
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        // Check if user exists
        $this->db->prepare('SELECT id FROM users WHERE email = :email OR username = :username');
        $this->db->bind(':email', $email);
        $this->db->bind(':username', $username);
        if ($this->db->fetch()) {
            return ['success' => false, 'message' => 'Email or username already exists'];
        }

        // Create user
        $passwordHash = Security::hashPassword($password);
        $this->db->prepare(
            'INSERT INTO users (email, username, password, first_name, last_name, role_id, status) 
             VALUES (:email, :username, :password, :first_name, :last_name, :role_id, :status)'
        );
        $this->db->bind(':email', $email);
        $this->db->bind(':username', $username);
        $this->db->bind(':password', $passwordHash);
        $this->db->bind(':first_name', $firstName);
        $this->db->bind(':last_name', $lastName);
        $this->db->bind(':role_id', 3); // Editor role
        $this->db->bind(':status', 'active');

        if ($this->db->execute()) {
            $this->logger->info('User registered', ['email' => $email, 'username' => $username]);
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $this->db->lastInsertId()];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    }

    /**
     * Login user. Returns one of:
     *   ['success'=>true]                                — fully logged in
     *   ['success'=>false, 'requires_mfa'=>true, 'user_id'=>X] — show 2FA prompt
     *   ['success'=>false, 'message'=>...]               — failure
     */
    public function login($email, $password, $rememberMe = false) {
        $this->db->prepare(
            'SELECT u.id, u.email, u.username, u.password, u.first_name, u.last_name, u.role_id, u.status,
                    u.mfa_enabled, r.name AS role_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.email = :email'
        );
        $this->db->bind(':email', $email);
        $user = $this->db->fetch();

        if (!$user) {
            $this->logger->warning('Login failed - user not found', ['email' => $email]);
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        if ($user['status'] !== 'active') {
            $this->logger->warning('Login failed - account inactive', ['email' => $email]);
            return ['success' => false, 'message' => 'Account is inactive'];
        }
        if (!Security::verifyPassword($password, $user['password'])) {
            $this->logger->warning('Login failed - invalid password', ['email' => $email]);
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // 2FA challenge required?
        if (!empty($user['mfa_enabled'])) {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            $_SESSION['mfa_pending_user'] = (int)$user['id'];
            $_SESSION['mfa_pending_remember'] = (bool)$rememberMe;
            return ['success' => false, 'requires_mfa' => true, 'user_id' => (int)$user['id']];
        }

        $this->establishSession($user, $rememberMe);
        return ['success' => true, 'message' => 'Login successful'];
    }

    /**
     * Complete 2FA challenge with a TOTP code.
     */
    public function verifyMfa($code) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $pendingId = (int)($_SESSION['mfa_pending_user'] ?? 0);
        if ($pendingId <= 0) {
            return ['success' => false, 'message' => 'No 2FA challenge in progress'];
        }
        $this->db->prepare(
            'SELECT u.id, u.email, u.username, u.password, u.first_name, u.last_name, u.role_id, u.status,
                    u.mfa_secret, r.name AS role_name
             FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :id'
        );
        $this->db->bind(':id', $pendingId);
        $user = $this->db->fetch();
        if (!$user || empty($user['mfa_secret']) || !Totp::verify($user['mfa_secret'], $code)) {
            $this->logger->warning('MFA verification failed', ['user_id' => $pendingId]);
            return ['success' => false, 'message' => 'Invalid 2FA code'];
        }
        $remember = (bool)($_SESSION['mfa_pending_remember'] ?? false);
        unset($_SESSION['mfa_pending_user'], $_SESSION['mfa_pending_remember']);
        $this->establishSession($user, $remember);
        return ['success' => true];
    }

    /**
     * Try to log the user in from a remember_token cookie. Idempotent / safe to call on every request.
     */
    public function tryRememberLogin() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!empty($_SESSION['user_id'])) { return; }
        $raw = $_COOKIE['remember_token'] ?? '';
        if (!preg_match('/^([a-f0-9]{32}):([a-f0-9]+)$/', $raw, $m)) { return; }
        [$_, $selector, $validator] = $m;
        $this->db->prepare(
            'SELECT t.user_id, t.validator_hash, t.expires_at, u.email, u.username, u.first_name, u.last_name, u.role_id, u.status, r.name AS role_name
             FROM remember_tokens t
             JOIN users u ON u.id = t.user_id
             JOIN roles r ON r.id = u.role_id
             WHERE t.selector = :selector AND t.expires_at > NOW() LIMIT 1'
        );
        $this->db->bind(':selector', $selector);
        $row = $this->db->fetch();
        if (!$row) { return; }
        $expected = hash('sha256', $validator);
        if (!hash_equals($row['validator_hash'], $expected)) {
            // suspicious — invalidate
            $this->db->prepare('DELETE FROM remember_tokens WHERE selector = :selector');
            $this->db->bind(':selector', $selector);
            $this->db->execute();
            return;
        }
        if ($row['status'] !== 'active') { return; }
        $user = [
            'id' => $row['user_id'], 'email' => $row['email'], 'username' => $row['username'],
            'first_name' => $row['first_name'], 'last_name' => $row['last_name'],
            'role_id' => $row['role_id'], 'role_name' => $row['role_name'],
        ];
        $this->establishSession($user, false); // don't issue a new remember cookie on auto-login
    }

    /**
     * Establish a logged-in session for a user record.
     */
    private function establishSession(array $user, $rememberMe) {
        Security::regenerateSessionID();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['user_role_name'] = $user['role_name'];
        $_SESSION['user_role_id'] = $user['role_id'];
        $_SESSION['user_data'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'username' => $user['username'],
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'role_name' => $user['role_name'],
        ];

        $this->loadUserPermissions($user['role_id']);

        $this->db->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id');
        $this->db->bind(':ip', Security::getClientIP());
        $this->db->bind(':id', $user['id']);
        $this->db->execute();

        $rl = new RateLimiter('login');
        $rl->reset();

        if ($rememberMe) {
            $this->issueRememberToken((int)$user['id']);
        }

        $this->logger->info('User logged in', ['user_id' => $user['id'], 'email' => $user['email']]);
    }

    /**
     * Issue a remember-me cookie + persist a hashed validator server-side.
     */
    private function issueRememberToken($userId) {
        $selector  = bin2hex(random_bytes(16));         // 32 hex chars
        $validator = bin2hex(random_bytes(32));         // 64 hex chars
        $hash      = hash('sha256', $validator);
        $days      = 30;
        $expiresAt = date('Y-m-d H:i:s', time() + $days * 86400);

        $this->db->prepare('INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at) VALUES (:user_id, :selector, :hash, :expires_at)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':selector', $selector);
        $this->db->bind(':hash', $hash);
        $this->db->bind(':expires_at', $expiresAt);
        $this->db->execute();

        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie('remember_token', $selector . ':' . $validator, [
            'expires'  => time() + $days * 86400,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Invalidate the remember-me cookie + DB row(s) for the current user.
     */
    private function clearRememberToken() {
        $raw = $_COOKIE['remember_token'] ?? '';
        if (preg_match('/^([a-f0-9]{32}):/', $raw, $m)) {
            $this->db->prepare('DELETE FROM remember_tokens WHERE selector = :selector');
            $this->db->bind(':selector', $m[1]);
            $this->db->execute();
        }
        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Send an email-verification link for a user.
     */
    public function sendEmailVerification($userId) {
        $this->db->prepare('SELECT id, email FROM users WHERE id = :id');
        $this->db->bind(':id', $userId);
        $user = $this->db->fetch();
        if (!$user) { return false; }

        // Clear previous unused tokens
        $this->db->prepare('DELETE FROM email_verifications WHERE user_id = :id AND used_at IS NULL');
        $this->db->bind(':id', $userId);
        $this->db->execute();

        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + 86400);
        $this->db->prepare('INSERT INTO email_verifications (user_id, token_hash, expires_at) VALUES (:uid, :hash, :exp)');
        $this->db->bind(':uid', $userId);
        $this->db->bind(':hash', $hash);
        $this->db->bind(':exp', $expires);
        $this->db->execute();

        $verifyUrl = rtrim(APP_URL, '/') . '/verify-email/' . $token;
        $subject = 'Verify your email — ' . APP_NAME;
        $html = '<p>Hi,</p>'
              . '<p>Please verify your email address by <a href="' . htmlspecialchars($verifyUrl) . '">clicking here</a>. '
              . 'This link expires in 24 hours.</p>';
        @Mailer::send($user['email'], $subject, $html);
        return true;
    }

    /**
     * Mark a user's email as verified given a token.
     */
    public function verifyEmail($token) {
        $hash = hash('sha256', (string)$token);
        $this->db->prepare('SELECT * FROM email_verifications WHERE token_hash = :h AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
        $this->db->bind(':h', $hash);
        $row = $this->db->fetch();
        if (!$row) { return false; }

        $this->db->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $row['user_id']); $this->db->execute();

        $this->db->prepare('UPDATE email_verifications SET used_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $row['id']); $this->db->execute();
        return true;
    }

    /**
     * Load user permissions into session
     */
    private function loadUserPermissions($roleId) {
        $this->db->prepare(
            'SELECT p.name FROM role_permissions rp 
             JOIN permissions p ON rp.permission_id = p.id 
             WHERE rp.role_id = :role_id'
        );
        $this->db->bind(':role_id', $roleId);
        $permissions = $this->db->fetchAll();
        
        $_SESSION['user_permissions'] = array_column($permissions, 'name');
    }

    /**
     * Logout user
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->logger->info('User logged out', ['user_id' => $_SESSION['user_id'] ?? null]);
        $this->clearRememberToken();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user
     */
    public function getUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $_SESSION['user_id']);
        return $this->db->fetch();
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        $this->db->prepare('SELECT id FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $user = $this->db->fetch();

        if (!$user) {
            // Return success anyway for security
            return ['success' => true, 'message' => 'If email exists, password reset link sent'];
        }

        $token = Security::generateToken();
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + PASSWORD_RESET_TIMEOUT);

        $this->db->prepare(
            'INSERT INTO password_resets (user_id, token, token_hash, expires_at) 
             VALUES (:user_id, :token, :token_hash, :expires_at)'
        );
        $this->db->bind(':user_id', $user['id']);
        $this->db->bind(':token', $token);
        $this->db->bind(':token_hash', $tokenHash);
        $this->db->bind(':expires_at', $expiresAt);
        $this->db->execute();

        $this->logger->info('Password reset requested', ['user_id' => $user['id']]);

        // Send the reset email. The token is delivered out-of-band only.
        $resetUrl = rtrim(APP_URL, '/') . '/reset-password/' . $token;
        $subject  = 'Password reset for ' . APP_NAME;
        $html = '<p>Hi,</p>'
              . '<p>We received a request to reset the password on your account.</p>'
              . '<p><a href="' . htmlspecialchars($resetUrl) . '">Click here to choose a new password</a>.</p>'
              . '<p>This link is valid for ' . (int)(PASSWORD_RESET_TIMEOUT / 60) . ' minutes. '
              . 'If you did not request a reset, you can ignore this email.</p>';
        @Mailer::send($email, $subject, $html);

        // SECURITY: do NOT return the token to the caller.
        return ['success' => true, 'message' => 'If email exists, password reset link sent'];
    }

    /**
     * Verify password reset token
     */
    public function verifyResetToken($token) {
        $tokenHash = hash('sha256', $token);
        
        $this->db->prepare(
            'SELECT user_id FROM password_resets 
             WHERE token_hash = :token_hash 
             AND used_at IS NULL 
             AND expires_at > NOW()'
        );
        $this->db->bind(':token_hash', $tokenHash);
        $reset = $this->db->fetch();

        return $reset ? $reset['user_id'] : false;
    }

    /**
     * Reset password
     */
    public function resetPassword($token, $newPassword) {
        $userId = $this->verifyResetToken($token);
        if (!$userId) {
            return ['success' => false, 'message' => 'Invalid or expired reset token'];
        }

        $passwordHash = Security::hashPassword($newPassword);
        $tokenHash = hash('sha256', $token);

        try {
            $this->db->beginTransaction();

            // Update password
            $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
            $this->db->bind(':password', $passwordHash);
            $this->db->bind(':id', $userId);
            $this->db->execute();

            // Mark reset as used
            $this->db->prepare(
                'UPDATE password_resets SET used_at = NOW() WHERE token_hash = :token_hash'
            );
            $this->db->bind(':token_hash', $tokenHash);
            $this->db->execute();

            $this->db->commit();
            $this->logger->info('Password reset completed', ['user_id' => $userId]);
            return ['success' => true, 'message' => 'Password reset successfully'];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Password reset failed'];
        }
    }
}
