<?php

require_once 'db.php';

function send_friend_request($from_user_id, $to_user_id) {
    global $conn;

    // Check if friend request already exists
    $sql = "SELECT * FROM friend_requests WHERE sender_id='$from_user_id' AND receiver_id='$to_user_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return false;
    }

    // Insert friend request into the database
    $sql = "INSERT INTO friend_requests (sender_id, receiver_id) VALUES ('$from_user_id', '$to_user_id')";
    return $conn->query($sql);
}

function get_incoming_friend_requests($user_id) {
    global $conn;
    $sql = "SELECT users.id, users.username, users.profile_picture FROM users INNER JOIN friend_requests ON users.id = friend_requests.sender_id WHERE friend_requests.receiver_id='$user_id' AND friend_requests.status='pending'";
    return $conn->query($sql);
}

function accept_friend_request($from_user_id, $to_user_id) {
    global $conn;

    // Update the friend request status to 'accepted'
    $sql = "UPDATE friend_requests SET status='accepted' WHERE sender_id='$from_user_id' AND receiver_id='$to_user_id'";
    return $conn->query($sql);
}

function reject_friend_request($from_user_id, $to_user_id) {
    global $conn;

    // Update the friend request status to 'rejected'
    $sql = "UPDATE friend_requests SET status='rejected' WHERE sender_id='$from_user_id' AND receiver_id='$to_user_id'";
    return $conn->query($sql);
}

function get_friends($user_id) {
    global $conn;
    $sql = "SELECT users.id, users.username, users.profile_picture FROM users INNER JOIN friend_requests ON users.id = friend_requests.receiver_id WHERE friend_requests.sender_id='$user_id' AND friend_requests.status='accepted' UNION SELECT users.id, users.username, users.profile_picture FROM users INNER JOIN friend_requests ON users.id = friend_requests.sender_id WHERE friend_requests.receiver_id='$user_id' AND friend_requests.status='accepted'";
    return $conn->query($sql);
}

function search_users($search_query) {
    global $conn;
    $sql = "SELECT id, username, profile_picture FROM users WHERE username LIKE '%$search_query%'";
    return $conn->query($sql);
}

?>
