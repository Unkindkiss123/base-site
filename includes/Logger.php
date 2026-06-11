<?php
/**
 * includes/Logger.php
 * Application logging utility
 */

require_once __DIR__ . '/../config/constants.php';

class Logger {
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';

    private $logFile;
    private $levels = [
        self::LEVEL_DEBUG => 0,
        self::LEVEL_INFO => 1,
        self::LEVEL_WARNING => 2,
        self::LEVEL_ERROR => 3,
        self::LEVEL_CRITICAL => 4,
    ];

    /**
     * Constructor
     */
    public function __construct($filename = 'application.log') {
        $this->logFile = LOGS_PATH . '/' . $filename;
        $this->ensureLogDirectory();
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory() {
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }
    }

    /**
     * Log message
     */
    public function log($level, $message, $context = []) {
        $level = strtoupper($level);
        $configLevel = strtoupper((string)LOG_LEVEL);
        $levelValue = $this->levels[$level] ?? 0;
        $configLevelValue = $this->levels[$configLevel] ?? 1;

        if ($levelValue < $configLevelValue) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;

        error_log($logMessage, 3, $this->logFile);
    }

    /**
     * Log debug message
     */
    public function debug($message, $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    /**
     * Log info message
     */
    public function info($message, $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    /**
     * Log warning message
     */
    public function warning($message, $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * Log error message
     */
    public function error($message, $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    /**
     * Log critical message
     */
    public function critical($message, $context = []) {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }
}
