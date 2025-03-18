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
$image_path = '';

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

// Handle image upload if present
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validate image type
    if (!in_array($_FILES['image']['type'], $allowed_types)) {
        $_SESSION['error_message'] = "Invalid image format. Please upload JPG, PNG, GIF, or WEBP.";
        header("Location: ../pages/view_post.php?id=" . $post_id);
        exit();
    }
    
    // Validate image size
    if ($_FILES['image']['size'] > $max_size) {
        $_SESSION['error_message'] = "Image size exceeds the 5MB limit.";
        header("Location: ../pages/view_post.php?id=" . $post_id);
        exit();
    }
    
    // Generate unique filename
    $upload_dir = dirname(__DIR__) . '/uploads/';
    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
    $target_file = $upload_dir . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_path = 'uploads/' . $filename;
    } else {
        $_SESSION['error_message'] = "Failed to upload image. Please try again.";
        header("Location: ../pages/view_post.php?id=" . $post_id);
        exit();
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert update
    $stmt = $conn->prepare("INSERT INTO post_updates (post_id, content, github_commit, code_snippet, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $post_id, $content, $github_commit, $code_snippet, $image_path);
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