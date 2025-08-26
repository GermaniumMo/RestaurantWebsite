<?php
require_once __DIR__ . '/../config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                die("Database connection error: " . $e->getMessage());
            } else {
                die("Database connection error. Please try again later.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = [], $types = '') {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            if (DEBUG_MODE) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            } else {
                throw new Exception("Database query error");
            }
        }
        
        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat('s', count($params));
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types = substr_replace($types, 'i', 0, 1);
                    } elseif (is_float($param)) {
                        $types = substr_replace($types, 'd', 0, 1);
                    }
                }
            }
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            if (DEBUG_MODE) {
                throw new Exception("Execute failed: " . $stmt->error);
            } else {
                throw new Exception("Database operation failed");
            }
        }
        
        return $stmt;
    }

    public function fetchOne($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function fetchAll($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        return $this->connection->insert_id;
    }
    public function execute($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        return $stmt->affected_rows;
    }

    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollback();
    }

    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
}

function db() {
    return Database::getInstance();
}

function db_query($sql, $params = [], $types = '') {
    return db()->query($sql, $params, $types);
}

function db_fetch_one($sql, $params = [], $types = '') {
    return db()->fetchOne($sql, $params, $types);
}

function db_fetch_all($sql, $params = [], $types = '') {
    return db()->fetchAll($sql, $params, $types);
}

function db_insert($sql, $params = [], $types = '') {
    return db()->insert($sql, $params, $types);
}

function db_execute($sql, $params = [], $types = '') {
    return db()->execute($sql, $params, $types);
}
?>
