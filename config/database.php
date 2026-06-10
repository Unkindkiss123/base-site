<?php
/**
 * config/database.php
 * Database connection handler using PDO
 */

require_once __DIR__ . '/constants.php';

class Database {
    private static $instance = null;
    private $connection;
    private $statement;
    private $error;

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Initialize database connection
     */
    private function __construct() {
        try {
            $this->connection = new PDO(
                DB_DSN,
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (APP_DEBUG) {
                die('Database Connection Error: ' . $e->getMessage());
            } else {
                die('Database connection failed. Please try again later.');
            }
        }
    }

    /**
     * Prepare a statement
     */
    public function prepare($query) {
        $this->statement = $this->connection->prepare($query);
        return $this;
    }

    /**
     * Bind values to statement
     */
    public function bind($param, $value, $type = PDO::PARAM_STR) {
        if (is_int($value)) {
            $type = PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            $type = PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            $type = PDO::PARAM_NULL;
        }
        $this->statement->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Execute prepared statement
     */
    public function execute() {
        try {
            $this->statement->execute();
            return true;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (APP_DEBUG) {
                error_log('SQL Error: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Get single result
     */
    public function fetch() {
        $this->execute();
        return $this->statement->fetch();
    }

    /**
     * Get all results
     */
    public function fetchAll() {
        $this->execute();
        return $this->statement->fetchAll();
    }

    /**
     * Get row count
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * Get error message
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserializing
     */
    private function __wakeup() {}
}
