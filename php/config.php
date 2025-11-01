<?php
/**
 * Fabricon.shop Configuration File
 * 
 * This file contains all configuration settings for the PHP backend.
 * Update these settings according to your environment.
 * 
 * IMPORTANT: Keep this file secure and never commit sensitive data to version control!
 */

// Prevent direct access
if (!defined('FABRICON_APP')) {
    die('Direct access not permitted');
}

// Environment Configuration
define('ENVIRONMENT', 'development'); // Options: 'development', 'production'
define('DEBUG_MODE', ENVIRONMENT === 'development');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'fabricon_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Update with your password
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'Fabricon.shop');
define('APP_URL', 'http://localhost/Fabricon'); // Update for production
define('APP_VERSION', '1.0.0');

// Security Settings
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes in seconds

// Email Configuration (for future use)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'noreply@fabricon.shop');
define('SMTP_FROM_NAME', 'Fabricon.shop');

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// Pricing
define('TAX_RATE', 0.10); // 10%
define('FREE_SHIPPING_THRESHOLD', 50.00);
define('STANDARD_SHIPPING_COST', 5.99);

// API Keys (for future integrations)
define('GOOGLE_ANALYTICS_ID', 'G-XXXXXXXXXX');
define('STRIPE_PUBLIC_KEY', 'pk_test_XXXXXXXXXX');
define('STRIPE_SECRET_KEY', 'sk_test_XXXXXXXXXX');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOG_PATH', ROOT_PATH . '/logs');

// Timezone
date_default_timezone_set('America/New_York');

/**
 * Database Connection Class
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            if (DEBUG_MODE) {
                error_log("Database connection established");
            }
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
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

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Error Handler
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error = date('Y-m-d H:i:s') . " - Error [$errno]: $errstr in $errfile on line $errline\n";
    
    if (DEBUG_MODE) {
        echo "<pre>$error</pre>";
    }
    
    // Log error
    error_log($error, 3, LOG_PATH . '/error.log');
    
    return true;
}

set_error_handler('customErrorHandler');

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Format price
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Calculate shipping cost
 */
function calculateShipping($subtotal) {
    return $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : STANDARD_SHIPPING_COST;
}

/**
 * Calculate tax
 */
function calculateTax($subtotal) {
    return $subtotal * TAX_RATE;
}

/**
 * Calculate total
 */
function calculateTotal($subtotal, $shipping, $tax) {
    return $subtotal + $shipping + $tax;
}

/**
 * Log activity (for analytics)
 */
function logActivity($userId, $action, $details = []) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $userId,
            $action,
            json_encode($details),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > SESSION_LIFETIME) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Set error reporting based on environment
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
