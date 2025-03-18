<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$content = $_POST['content'];
$image_path = '';

if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the file is an image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        exit;
    }

    // Save the image in the 'uploads' folder
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = $target_file;
    } else {
        echo "Error uploading the image.";
        exit;
    }
}

$sql = "INSERT INTO posts (user_id, content, image_path) VALUES ('$user_id', '$content', '$image_path')";

if ($conn->query($sql) === TRUE) {
    echo "Post submitted successfully. <a href='dashboard.php'>Go back to dashboard</a>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
