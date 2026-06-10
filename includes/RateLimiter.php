<?php
/**
 * includes/RateLimiter.php
 * Rate limiting to prevent brute force and abuse
 */

require_once __DIR__ . '/../config/database.php';

class RateLimiter {
    private $db;
    private $ipAddress;
    private $identifier;

    /**
     * Constructor
     */
    public function __construct($identifier = 'login') {
        $this->db = Database::getInstance();
        $this->ipAddress = $this->getClientIP();
        $this->identifier = $identifier;
    }

    /**
     * Check if action is rate limited
     */
    public function isLimited($maxAttempts = RATE_LIMIT_ATTEMPTS, $windowMinutes = RATE_LIMIT_MINUTES) {
        if (!RATE_LIMIT_ENABLED) {
            return false;
        }

        $windowSeconds = $windowMinutes * 60;
        $cutoffTime = time() - $windowSeconds;

        $this->db->prepare(
            "SELECT COUNT(*) as attempts FROM activity_logs 
             WHERE ip_address = :ip 
             AND action = :action 
             AND timestamp > FROM_UNIXTIME(:cutoff_time)"
        );
        $this->db->bind(':ip', $this->ipAddress);
        $this->db->bind(':action', 'rate_limit_' . $this->identifier);
        $this->db->bind(':cutoff_time', $cutoffTime);

        $result = $this->db->fetch();
        $attempts = $result['attempts'] ?? 0;

        if ($attempts >= $maxAttempts) {
            return true;
        }

        $this->logAttempt();
        return false;
    }

    /**
     * Log an attempt
     */
    private function logAttempt() {
        $this->db->prepare(
            "INSERT INTO activity_logs (ip_address, action, user_agent, timestamp) 
             VALUES (:ip, :action, :user_agent, NOW())"
        );
        $this->db->bind(':ip', $this->ipAddress);
        $this->db->bind(':action', 'rate_limit_' . $this->identifier);
        $this->db->bind(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->db->execute();
    }

    /**
     * Reset attempts for IP
     */
    public function reset() {
        $this->db->prepare(
            "DELETE FROM activity_logs 
             WHERE ip_address = :ip 
             AND action = :action"
        );
        $this->db->bind(':ip', $this->ipAddress);
        $this->db->bind(':action', 'rate_limit_' . $this->identifier);
        return $this->db->execute();
    }

    /**
     * Get client IP address
     */
    private function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        return $ip;
    }
}
