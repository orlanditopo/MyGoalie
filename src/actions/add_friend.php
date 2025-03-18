<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_id'])) {
    $friend_id = (int)$_POST['friend_id'];
    
    // Check if friend request already exists
    $stmt = $conn->prepare("SELECT * FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $stmt->bind_param("iiii", $_SESSION['user_id'], $friend_id, $friend_id, $_SESSION['user_id']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        // Create new friend request
        $stmt = $conn->prepare("INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $_SESSION['user_id'], $friend_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Friend request sent!";
        } else {
            $_SESSION['error_message'] = "Failed to send friend request.";
        }
    } else {
        $_SESSION['error_message'] = "Friend request already exists.";
    }
}

header("Location: ../pages/profile.php?id=" . $friend_id);
exit();
?> 