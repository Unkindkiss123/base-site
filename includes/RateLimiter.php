<?php
/**
 * includes/RateLimiter.php
 * Rate limiting to prevent brute force and abuse.
 *
 * Usage:
 *   $rl = new RateLimiter('login');
 *   if ($rl->isLimited(5, 15)) { reject }
 *   ... attempt action ...
 *   if (failed) $rl->hit();
 *   if (succeeded) $rl->reset();
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Security.php';

class RateLimiter {
    private $db;
    private $ipAddress;
    private $identifier;

    public function __construct($identifier = 'login') {
        $this->db = Database::getInstance();
        $this->ipAddress = Security::getClientIP();
        $this->identifier = $identifier;
    }

    /**
     * Check if action is rate limited. Does NOT record an attempt.
     */
    public function isLimited($maxAttempts = RATE_LIMIT_ATTEMPTS, $windowMinutes = RATE_LIMIT_MINUTES) {
        if (!RATE_LIMIT_ENABLED) {
            return false;
        }

        $cutoffTime = time() - ($windowMinutes * 60);

        $this->db->prepare(
            "SELECT COUNT(*) as attempts FROM activity_logs
             WHERE ip_address = :ip
             AND action = :action
             AND timestamp > FROM_UNIXTIME(:cutoff_time)"
        );
        $this->db->bind(':ip', $this->ipAddress);
        $this->db->bind(':action', 'rate_limit_' . $this->identifier);
        $this->db->bind(':cutoff_time', $cutoffTime, PDO::PARAM_INT);

        $result = $this->db->fetch();
        $attempts = $result['attempts'] ?? 0;

        return $attempts >= $maxAttempts;
    }

    /**
     * Record a failed attempt.
     */
    public function hit() {
        if (!RATE_LIMIT_ENABLED) {
            return;
        }
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
     * Reset attempts for current IP/identifier.
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
}
