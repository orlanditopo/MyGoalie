<?php
// Script to update image paths for MyGoalie demo posts
require_once 'src/includes/config.php';
require_once 'src/includes/db.php';

// Dummy image paths (these don't need to exist physically for the UI to show placeholders)
$image_paths = [
    'Building a Wooden Dining Table' => 'uploads/wooden_table.jpg',
    'Creating an Engineering Resume Portfolio' => 'uploads/resume.jpg', 
    'Developing a Fitness Tracking Mobile App' => 'uploads/fitness_app.jpg',
    'Creating a 2D Mario-Style Platform Game' => 'uploads/platform_game.jpg'
];

// Update each post with its image path
foreach ($image_paths as $post_title => $image_path) {
    $stmt = $conn->prepare("UPDATE posts SET image_path = ? WHERE title = ?");
    $stmt->bind_param("ss", $image_path, $post_title);
    
    if ($stmt->execute()) {
        echo "Updated image path for post '$post_title' to '$image_path'<br>";
    } else {
        echo "Failed to update image path for post '$post_title': " . $conn->error . "<br>";
    }
}

echo "<br><h2>All Done!</h2>";
echo "Image paths have been updated in the database.<br>";
echo "Note: The actual image files don't exist, but the website will show placeholders.<br>";
echo "The demo accounts are now ready to use.<br>";
?> 