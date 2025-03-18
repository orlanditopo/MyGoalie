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
$github_repo = isset($_POST['github_repo']) ? trim($_POST['github_repo']) : '';
$code_snippet = isset($_POST['code_snippet']) ? trim($_POST['code_snippet']) : '';

// Basic validation
if (empty($title) || empty($content)) {
    $_SESSION['error_message'] = "Title and content are required.";
    header("Location: ../pages/create_post.php");
    exit();
}

// Insert post into database
$stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, status, github_repo, code_snippet) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $_SESSION['user_id'], $title, $content, $status, $github_repo, $code_snippet);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Goal created successfully!";
    header("Location: ../pages/dashboard.php");
} else {
    $_SESSION['error_message'] = "Failed to create goal. Please try again.";
    header("Location: ../pages/create_post.php");
}
exit();
?>
