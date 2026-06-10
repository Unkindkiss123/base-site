<?php
/**
 * includes/Auth.php
 * Authentication handler
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Logger.php';

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
     * Login user
     */
    public function login($email, $password, $rememberMe = false) {
        // Get user
        $this->db->prepare(
            'SELECT u.id, u.email, u.username, u.password, u.role_id, u.status, r.name as role_name 
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

        // Check status
        if ($user['status'] !== 'active') {
            $this->logger->warning('Login failed - account inactive', ['email' => $email]);
            return ['success' => false, 'message' => 'Account is inactive'];
        }

        // Verify password
        if (!Security::verifyPassword($password, $user['password'])) {
            $this->logger->warning('Login failed - invalid password', ['email' => $email]);
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Set session
        Security::regenerateSessionID();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['user_role_id'] = $user['role_id'];

        // Load user permissions
        $this->loadUserPermissions($user['role_id']);

        // Update last login
        $this->db->prepare(
            'UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id'
        );
        $this->db->bind(':ip', Security::getClientIP());
        $this->db->bind(':id', $user['id']);
        $this->db->execute();

        // Remember me
        if ($rememberMe) {
            $rememberToken = Security::generateToken();
            setcookie('remember_token', $rememberToken, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            $_SESSION['remember_token'] = $rememberToken;
        }

        $this->logger->info('User logged in', ['user_id' => $user['id'], 'email' => $email]);
        return ['success' => true, 'message' => 'Login successful'];
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
        $this->logger->info('User logged out', ['user_id' => $_SESSION['user_id'] ?? null]);
        
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        
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
        return ['success' => true, 'message' => 'If email exists, password reset link sent', 'token' => $token];
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
