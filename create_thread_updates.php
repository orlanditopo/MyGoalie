<?php
// Script to add thread updates to the original CEO posts
require_once 'src/includes/config.php';
require_once 'src/includes/db.php';
require_once 'src/includes/functions.php';

// Function to create a thread update
function create_thread_update($conn, $user_id, $parent_id, $thread_type, $content, $status = 'in-progress') {
    // Get the parent post title
    $stmt = $conn->prepare("SELECT title FROM posts WHERE id = ?");
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $parent = $result->fetch_assoc();
    
    if (!$parent) {
        echo "Error: Parent post not found.<br>";
        return 0;
    }
    
    $title = $parent['title'] . ' - ' . ucfirst($thread_type);
    $github_repo = '';
    $code_snippet = '';
    $image_path = '';
    
    $stmt = $conn->prepare("
        INSERT INTO posts (
            user_id, parent_id, thread_type, title, content, status, github_repo, code_snippet, image_path, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("iisssssss", $user_id, $parent_id, $thread_type, $title, $content, $status, $github_repo, $code_snippet, $image_path);
    
    if ($stmt->execute()) {
        $post_id = $conn->insert_id;
        echo "Created thread update for post $parent_id: $title (ID: $post_id)<br>";
        return $post_id;
    } else {
        echo "Error creating thread update: " . $conn->error . "<br>";
        return 0;
    }
}

// Get the original 4 CEOs
$stmt = $conn->prepare("
    SELECT u.id, u.username, p.id as post_id, p.title 
    FROM users u 
    JOIN posts p ON u.id = p.user_id
    WHERE u.username IN ('SatyaNadella', 'SundarPichai', 'TimCook', 'JensenHuang')
    AND p.parent_id IS NULL
");
$stmt->execute();
$original_ceos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo "<h2>Adding Updates to Original CEO Posts</h2>";

foreach ($original_ceos as $ceo) {
    // Add first update - general progress
    $update_content = "I've started making progress on this goal. Initial planning is complete and I've gathered the necessary resources. Looking forward to diving deeper into the implementation phase.";
    create_thread_update($conn, $ceo['id'], $ceo['post_id'], 'update', $update_content);
    
    // Add second update - more specific milestone
    $milestone_content = "Reached a significant milestone today! Completed about 25% of the work and learned a lot in the process. Here's a brief summary of what I've accomplished so far and what challenges I've faced.";
    create_thread_update($conn, $ceo['id'], $ceo['post_id'], 'milestone', $milestone_content);
}

echo "<br><h2>All Done!</h2>";
echo "Added 2 updates (1 update, 1 milestone) to each of the original 4 CEO goal posts.<br>";
?> 