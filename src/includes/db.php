<?php
$servername = "localhost";
$username = "root";
$password = "zxcvbnm@0987";
$dbname = "social_media_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}