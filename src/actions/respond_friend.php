<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

// Check if friendship ID and action are provided
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: ../pages/friends.php");
    exit;
}

$friendship_id = intval($_GET['id']);
$action = $_GET['action'];

// Verify the friendship request exists and belongs to the current user
$stmt = $conn->prepare("SELECT * FROM friendships WHERE id = ? AND friend_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $friendship_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Friend request not found or already processed.";
    header("Location: ../pages/friends.php");
    exit;
}

if ($action === 'accept') {
    // Accept the friend request
    $stmt = $conn->prepare("UPDATE friendships SET status = 'accepted' WHERE id = ?");
    $stmt->bind_param("i", $friendship_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Friend request accepted!";
    } else {
        $_SESSION['error_message'] = "Error accepting friend request: " . $conn->error;
    }
} elseif ($action === 'reject') {
    // Reject the friend request
    $stmt = $conn->prepare("DELETE FROM friendships WHERE id = ?");
    $stmt->bind_param("i", $friendship_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Friend request rejected.";
    } else {
        $_SESSION['error_message'] = "Error rejecting friend request: " . $conn->error;
    }
} else {
    $_SESSION['error_message'] = "Invalid action.";
}

header("Location: ../pages/friends.php");
exit;
?> 