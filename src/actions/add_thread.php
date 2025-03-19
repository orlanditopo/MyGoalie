<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/dashboard.php");
    exit;
}

// Get form data
$parent_id = intval($_POST['parent_id']);
$content = trim($_POST['content']);
$thread_type = trim($_POST['thread_type']);
$github_repo = trim($_POST['github_repo'] ?? '');
$code_snippet = trim($_POST['code_snippet'] ?? '');
$image_path = '';

// Validate data
if (empty($content)) {
    $_SESSION['error_message'] = "Content is required.";
    header("Location: ../pages/view_post.php?id=$parent_id");
    exit;
}

// Validate thread type
$valid_types = ['update', 'milestone', 'commit'];
if (!in_array($thread_type, $valid_types)) {
    $_SESSION['error_message'] = "Invalid update type.";
    header("Location: ../pages/view_post.php?id=$parent_id");
    exit;
}

// Verify the parent post exists
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND deleted_at IS NULL");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$parent_post = $stmt->get_result()->fetch_assoc();

if (!$parent_post) {
    $_SESSION['error_message'] = "Parent post not found.";
    header("Location: ../pages/dashboard.php");
    exit;
}

// Verify the user owns the parent post
if ($parent_post['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error_message'] = "You don't have permission to add updates to this post.";
    header("Location: ../pages/view_post.php?id=$parent_id");
    exit;
}

// Handle image upload if provided
if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['size'] > $max_size) {
        $_SESSION['error_message'] = "Image size exceeds the 5MB limit.";
        header("Location: ../pages/view_post.php?id=$parent_id");
        exit;
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error_message'] = "Only JPEG, PNG, GIF, and WEBP images are allowed.";
        header("Location: ../pages/view_post.php?id=$parent_id");
        exit;
    }
    
    // Generate a unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $target_dir = dirname(__DIR__) . '/uploads/';
    $target_file = $target_dir . $filename;
    
    // Ensure the uploads directory exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $image_path = 'uploads/' . $filename;
    } else {
        $_SESSION['error_message'] = "Failed to upload image.";
        header("Location: ../pages/view_post.php?id=$parent_id");
        exit;
    }
}

// Insert the thread update
$stmt = $conn->prepare("
    INSERT INTO posts (
        user_id, 
        parent_id, 
        thread_type, 
        title, 
        content, 
        status, 
        github_repo, 
        code_snippet, 
        image_path, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

// Use the parent post's title with a prefix based on the thread type
$title = $parent_post['title'] . ' - ' . ucfirst($thread_type);
$status = $parent_post['status']; // Inherit parent's status

// Correct the binding parameters - need 9 parameters for 9 placeholders
// The format string has i=integer, s=string
$stmt->bind_param(
    "iisssssss", 
    $_SESSION['user_id'], 
    $parent_id, 
    $thread_type, 
    $title, 
    $content, 
    $status, 
    $github_repo, 
    $code_snippet, 
    $image_path
);

if ($stmt->execute()) {
    $new_thread_id = $conn->insert_id;
    $_SESSION['success_message'] = "Update added successfully!";
    
    // If this is a milestone or commit, also update the parent's status?
    if ($thread_type == 'milestone' && $_POST['update_status'] == 'completed') {
        $update_stmt = $conn->prepare("UPDATE posts SET status = 'completed' WHERE id = ?");
        $update_stmt->bind_param("i", $parent_id);
        $update_stmt->execute();
    }
    
    header("Location: ../pages/view_post.php?id=$parent_id");
} else {
    $_SESSION['error_message'] = "Error adding update: " . $conn->error;
    header("Location: ../pages/view_post.php?id=$parent_id");
}
exit;
?> 