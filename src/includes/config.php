<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mygoalie');

// Application paths
define('BASE_PATH', dirname(dirname(__DIR__)));
define('INCLUDES_PATH', dirname(__FILE__));
define('ASSETS_PATH', dirname(dirname(__FILE__)) . '/assets');

// URL paths
define('BASE_URL', 'http://localhost/MyGoalie');
define('ASSETS_URL', BASE_URL . '/src/assets');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 