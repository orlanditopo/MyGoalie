<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friendship_id'])) {
    $friendship_id = (int)$_POST['friendship_id'];
    
    // Delete the friendship request
    $stmt = $conn->prepare("DELETE FROM friendships WHERE id = ? AND friend_id = ?");
    $stmt->bind_param("ii", $friendship_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Friend request rejected.";
    } else {
        $_SESSION['error_message'] = "Failed to reject friend request.";
    }
}

header("Location: ../pages/friends.php");
exit();
?> 