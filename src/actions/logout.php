<?php
session_start();
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Check if user is logged in before logging out
if (is_logged_in()) {
    // Unset all session variables
    $_SESSION = array();

    // If session is using cookies, delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();
}

// Preserve dark mode setting from cookies if needed (it's handled separately from the session)

// Redirect to login page
header("Location: " . BASE_URL . "/src/auth/login.php");
exit();
?> 