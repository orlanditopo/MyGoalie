<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/dashboard.php");
    exit;
}

// Get form data
$post_id = intval($_POST['post_id']);
$title = trim($_POST['title']);
$content = trim($_POST['content']);
$status = $_POST['status'];
$privacy = $_POST['privacy'];
$github_repo = isset($_POST['github_repo']) ? trim($_POST['github_repo']) : '';
$code_snippet = isset($_POST['code_snippet']) ? trim($_POST['code_snippet']) : '';
$image_path = ''; // Will be set if user uploads a new image

// Validate data
if (empty($title) || empty($content)) {
    $_SESSION['error_message'] = "Title and content are required.";
    header("Location: ../pages/edit_post.php?id=$post_id");
    exit;
}

// Verify the post belongs to the current user
$stmt = $conn->prepare("SELECT image_path FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "You don't have permission to edit this post.";
    header("Location: ../pages/dashboard.php");
    exit;
}

$post = $result->fetch_assoc();
$current_image_path = $post['image_path'];

// Handle image upload if provided
if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
    // Validate image
    $file = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['size'] > $max_size) {
        $_SESSION['error_message'] = "Image size exceeds the 5MB limit.";
        header("Location: ../pages/edit_post.php?id=$post_id");
        exit;
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error_message'] = "Only JPEG, PNG, GIF, and WEBP images are allowed.";
        header("Location: ../pages/edit_post.php?id=$post_id");
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
        
        // Delete the old image if it exists
        if (!empty($current_image_path)) {
            $old_image_path = dirname(__DIR__) . '/' . $current_image_path;
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
    } else {
        $_SESSION['error_message'] = "Failed to upload image.";
        header("Location: ../pages/edit_post.php?id=$post_id");
        exit;
    }
} else {
    // Keep the existing image path
    $image_path = $current_image_path;
}

// Update the post
if (!empty($image_path)) {
    // If new image is uploaded
    $stmt = $conn->prepare("
        UPDATE posts SET 
        title = ?, 
        content = ?, 
        status = ?, 
        privacy = ?,
        github_repo = ?, 
        code_snippet = ?, 
        image_path = ?, 
        updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("sssssssi", $title, $content, $status, $privacy, $github_repo, $code_snippet, $image_path, $post_id);
} else {
    // If no new image
    $stmt = $conn->prepare("
        UPDATE posts SET 
        title = ?, 
        content = ?, 
        status = ?, 
        privacy = ?,
        github_repo = ?, 
        code_snippet = ?, 
        updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("ssssssi", $title, $content, $status, $privacy, $github_repo, $code_snippet, $post_id);
}

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Goal updated successfully!";
    header("Location: ../pages/view_post.php?id=$post_id");
} else {
    $_SESSION['error_message'] = "Error updating goal: " . $conn->error;
    header("Location: ../pages/edit_post.php?id=$post_id");
}
exit;
?> 