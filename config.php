<?php

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Session configuration - only set if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    // Security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    
    // Start the session
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'barber_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('BASE_URL', 'http://localhost/barber'); // adjust to your local URL

// Email configuration (for PHP mail() or SMTP relay)
// For real projects, use something like PHPMailer with SMTP.
define('EMAIL_FROM', 'blessb0243@gmail.com');
define('EMAIL_FROM_NAME', 'Barber Shop');

// Verification code settings
define('VERIFICATION_CODE_LENGTH', 6);
define('VERIFICATION_CODE_EXPIRY_MINUTES', 10);

// Security settings
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_OPTIONS', ['cost' => 12]);

// Timezone
date_default_timezone_set('UTC');

// Error pages
function show404() {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

function show500($message = 'Internal Server Error') {
    header('HTTP/1.1 500 Internal Server Error');
    echo "<h1>500 - $message</h1>";
    if (ini_get('display_errors')) {
        echo "<pre>";
        debug_print_backtrace();
        echo "</pre>";
    }
    exit;
}
