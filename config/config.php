<?php
// Environment
define('ENVIRONMENT', 'development'); // Change to 'production' for live site

// Konfigurasi Database MongoDB
define('MONGODB_HOST', 'localhost');
define('MONGODB_PORT', '27017');
define('MONGODB_DATABASE', 'ud_rahayu');

// Konfigurasi Path
define('BASE_PATH', __DIR__ . '/..');
define('BASE_URL', '/ud_rahayu');

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 minutes
define('MIN_PASSWORD_LENGTH', 8);
define('MAX_USERNAME_LENGTH', 50);
define('CSRF_TOKEN_TIME', 3600); // 1 hour

// Inisialisasi session dengan pengaturan keamanan
function initSession() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', ENVIRONMENT === 'production' ? 1 : 0);
    
    session_start();
    
    // Set session timeout
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || 
        time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_TIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || 
        time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_TIME || 
        $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Fungsi koneksi database
function connectDB() {
    try {
        // Enable MongoDB driver debugging only in development
        if (!defined('MONGODB_DEBUG')) {
            define('MONGODB_DEBUG', ENVIRONMENT === 'development');
        }

        // Set connection options
        $options = [
            'serverSelectionTimeoutMS' => 5000,
            'connectTimeoutMS' => 10000,
            'retryWrites' => true,
            'w' => 1
        ];

        // Create MongoDB client with options
        $client = new MongoDB\Client(
            "mongodb://" . MONGODB_HOST . ":" . MONGODB_PORT,
            [],
            $options
        );

        // Test connection
        $client->listDatabases();

        // Get database
        $database = $client->{MONGODB_DATABASE};

        // Log successful connection only in development
        if (ENVIRONMENT === 'development') {
            error_log("MongoDB connection successful");
        }
        
        return $database;
    } catch (Exception $e) {
        error_log("MongoDB connection error: " . $e->getMessage());
        if (ENVIRONMENT === 'development') {
            error_log("Stack trace: " . $e->getTraceAsString());
            die("Error koneksi database: " . $e->getMessage());
        } else {
            die("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
        }
    }
}

// Fungsi untuk mengecek login
function checkLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
    
    // Cek session timeout
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "/login.php?msg=session_expired");
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Fungsi untuk mengecek role admin
function checkAdmin() {
    checkLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: " . BASE_URL . "/403.php");
        exit();
    }
}

// Fungsi validasi password
function validatePassword($password) {
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        return false;
    }
    // Minimal 1 huruf besar, 1 huruf kecil, 1 angka
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
        return false;
    }
    return true;
}

// Fungsi validasi username
function validateUsername($username) {
    if (strlen($username) > MAX_USERNAME_LENGTH) {
        return false;
    }
    // Hanya boleh huruf, angka, underscore, dan titik
    if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
        return false;
    }
    return true;
} 