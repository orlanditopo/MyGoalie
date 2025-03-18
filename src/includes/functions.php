<?php
require_once 'config.php';
require_once 'db.php';

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

/**
 * Get user data
 */
function get_user_data($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Format date
 */
function format_date($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Get profile image URL
 */
function get_profile_image($user_id) {
    $user = get_user_data($user_id);
    if ($user && $user['profile_image']) {
        return ASSETS_URL . '/images/' . $user['profile_image'];
    }
    return ASSETS_URL . '/images/default_profile.png';
}
?> 