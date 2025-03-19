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
$title = trim($_POST['title']);
$content = trim($_POST['content']);
$status = $_POST['status'];
$privacy = $_POST['privacy'];
$github_repo = isset($_POST['github_repo']) ? trim($_POST['github_repo']) : '';
$code_snippet = isset($_POST['code_snippet']) ? trim($_POST['code_snippet']) : '';
$image_path = '';

// Basic validation
if (empty($title) || empty($content)) {
    $_SESSION['error_message'] = "Title and content are required.";
    header("Location: ../pages/create_post.php");
    exit();
}

// Handle image upload if present
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validate image type
    if (!in_array($_FILES['image']['type'], $allowed_types)) {
        $_SESSION['error_message'] = "Invalid image format. Please upload JPG, PNG, GIF, or WEBP.";
        header("Location: ../pages/create_post.php");
        exit();
    }
    
    // Validate image size
    if ($_FILES['image']['size'] > $max_size) {
        $_SESSION['error_message'] = "Image size exceeds the 5MB limit.";
        header("Location: ../pages/create_post.php");
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
        header("Location: ../pages/create_post.php");
        exit();
    }
}

// Insert the post
$stmt = $conn->prepare("
    INSERT INTO posts (
        user_id, title, content, status, privacy, github_repo, code_snippet, image_path, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param(
    "isssssss", 
    $_SESSION['user_id'], 
    $title, 
    $content, 
    $status, 
    $privacy, 
    $github_repo, 
    $code_snippet, 
    $image_path
);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Goal created successfully!";
    header("Location: ../pages/dashboard.php");
} else {
    $_SESSION['error_message'] = "Failed to create goal. Please try again.";
    header("Location: ../pages/create_post.php");
}
exit();
?>
