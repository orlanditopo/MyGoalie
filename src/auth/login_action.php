<?php
session_start();

require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $row['id']; // Store the user_id in the session
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "User not found.";
    }
}
?>
