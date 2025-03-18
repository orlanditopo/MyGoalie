<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/dashboard.php");
    exit();
}

// Get form data
$post_id = (int)$_POST['post_id'];
$content = trim($_POST['content']);
$github_commit = isset($_POST['github_commit']) ? trim($_POST['github_commit']) : '';
$code_snippet = isset($_POST['code_snippet']) ? trim($_POST['code_snippet']) : '';
$new_status = isset($_POST['status']) && !empty($_POST['status']) ? $_POST['status'] : null;

// Check if post exists and user owns it
$stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post || ($post['user_id'] !== $_SESSION['user_id'] && !is_admin())) {
    $_SESSION['error_message'] = "You don't have permission to update this goal.";
    header("Location: ../pages/dashboard.php");
    exit();
}

// Basic validation
if (empty($content)) {
    $_SESSION['error_message'] = "Update content is required.";
    header("Location: ../pages/view_post.php?id=" . $post_id);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert update
    $stmt = $conn->prepare("INSERT INTO post_updates (post_id, content, github_commit, code_snippet) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $post_id, $content, $github_commit, $code_snippet);
    $stmt->execute();
    
    // Update post status if provided
    if ($new_status !== null) {
        $stmt = $conn->prepare("UPDATE posts SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $post_id);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['success_message'] = "Update added successfully!";
    header("Location: ../pages/view_post.php?id=" . $post_id);
    exit();
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $_SESSION['error_message'] = "Failed to add update. Please try again.";
    header("Location: ../pages/view_post.php?id=" . $post_id);
    exit();
}
?> 