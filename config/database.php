<?php
/**
 * Database Configuration
 * Environment-aware database connection
 */

// Include constants for environment detection
require_once __DIR__ . '/constants.php';

// Environment-based database configuration
function getDatabaseConfig() {
    $env = getEnvironment();
    
    switch ($env) {
        case 'production':
            return [
                'host' => 'localhost',
                'name' => 'u444388293_karvy_project', // Update with your actual production DB name
                'user' => 'u444388293_karvy_project', // Update with your actual production DB user
                'pass' => 'AVav@@2025', // Update with your actual production DB password
                'charset' => 'utf8mb4'
            ];
            
        case 'testing':
            return [
                'host' => 'localhost',
                'name' => 'u444388293_karvy_project',
                'user' => 'reporting',
                'pass' => 'reporting',
                'charset' => 'utf8mb4'
            ];
            
        case 'development':
        default:
            return [
                'host' => 'localhost',
                'name' => 'u444388293_karvy_project',
                'user' => 'reporting',
                'pass' => 'reporting',
                'charset' => 'utf8mb4'
            ];
    }
}

// Get database configuration for current environment
$dbConfig = getDatabaseConfig();

// Define database constants
define('DB_HOST', $dbConfig['host']);
define('DB_NAME', $dbConfig['name']);
define('DB_USER', $dbConfig['user']);
define('DB_PASS', $dbConfig['pass']);
define('DB_CHARSET', $dbConfig['charset']);

class Database {
    private static $instance = null;
    private $connection;
    private $environment;
    
    private function __construct() {
        $this->environment = getEnvironment();
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            // Add SSL options for production if needed
            if ($this->environment === 'production') {
                // Uncomment and configure if your production server requires SSL
                // $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Log successful connection (only in development)
            if ($this->environment === 'development') {
                error_log("Database connected successfully to " . DB_NAME . " (" . $this->environment . ")");
            }
            
        } catch (PDOException $e) {
            $errorMsg = "Database connection failed in " . $this->environment . " environment: " . $e->getMessage();
            error_log($errorMsg);
            
            // In production, don't expose database details
            if ($this->environment === 'production') {
                throw new Exception("Database connection failed. Please contact administrator.");
            } else {
                throw new Exception($errorMsg);
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function getEnvironment() {
        return $this->environment;
    }
    
    public function getDatabaseName() {
        return DB_NAME;
    }
    
    public function testConnection() {
        try {
            $stmt = $this->connection->query("SELECT 1");
            return $stmt !== false;
        } catch (PDOException $e) {
            error_log("Database test failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get database configuration info (for debugging)
     * Note: Passwords are masked for security
     */
    public function getConnectionInfo() {
        return [
            'environment' => $this->environment,
            'host' => DB_HOST,
            'database' => DB_NAME,
            'user' => DB_USER,
            'password' => str_repeat('*', strlen(DB_PASS)), // Masked for security
            'charset' => DB_CHARSET
        ];
    }
}

// Test database connection on include (only in development)
if (getEnvironment() === 'development') {
    try {
        $db = Database::getInstance();
        if (!$db->testConnection()) {
            error_log("Warning: Database connection test failed");
        }
    } catch (Exception $e) {
        error_log("Database initialization error: " . $e->getMessage());
    }
}
?>