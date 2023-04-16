<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'db.php';

    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];

    if ($_FILES['profile_picture']['name']) {
        // If a new profile picture is uploaded, move the file to the uploads directory
        $target_dir = "uploads/";
        $profile_picture = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture);
    } else if (isset($_POST['remove_picture'])) {
        // If the "Remove Picture" checkbox is checked, set the profile_picture field to NULL
        $profile_picture = NULL;
    } else {
        // Otherwise, keep the existing profile picture
        $result = $conn->query("SELECT profile_picture FROM users WHERE id='$user_id'");
        $user = $result->fetch_assoc();
        $profile_picture = $user['profile_picture'];
    }

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, bio=?, profile_picture=? WHERE id=?");
    $stmt->bind_param("ssssi", $username, $email, $bio, $profile_picture, $user_id);
    $stmt->execute();

    $_SESSION['username'] = $username;

    header("Location: my_profile.php");
    exit;
}