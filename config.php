<?php
// config.php - Database Configuration and Connection

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quiz_management');

// Create database connection
class Database {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database Error: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // SELECT query with prepared statement
    public function select($query, $params = [], $types = "") {
        $stmt = $this->conn->prepare($query);
        
        if ($params && $types) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }
    
    // INSERT, UPDATE, DELETE with prepared statement
    public function execute($query, $params = [], $types = "") {
        $stmt = $this->conn->prepare($query);
        
        if ($params && $types) {
            $stmt->bind_param($types, ...$params);
        }
        
        $success = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $insert_id = $stmt->insert_id;
        
        $stmt->close();
        
        return [
            'success' => $success,
            'affected_rows' => $affected_rows,
            'insert_id' => $insert_id
        ];
    }
    
    // Transaction support
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    public function commit() {
        $this->conn->commit();
    }
    
    public function rollback() {
        $this->conn->rollback();
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Session management
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isInstructor() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'instructor';
}

function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireInstructor() {
    requireLogin();
    if (!isInstructor()) {
        header("Location: student_dashboard.php");
        exit();
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        header("Location: instructor_dashboard.php");
        exit();
    }
}

// Utility functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatDate($date) {
    return date('M d, Y h:i A', strtotime($date));
}

function isQuizExpired($expire_date) {
    return strtotime($expire_date) <= time();
}

function calculatePercentage($marks, $total) {
    if ($total == 0) return 0;
    return round(($marks / $total) * 100, 2);
}
?>