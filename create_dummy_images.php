<?php
// Script to create dummy images for MyGoalie demo posts
require_once 'src/includes/config.php';
require_once 'src/includes/db.php';

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/src/uploads';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
    echo "Created uploads directory at $upload_dir<br>";
}

// Function to create a simple colored image with text
function createDummyImage($filename, $text, $width = 800, $height = 600) {
    global $upload_dir;
    $filepath = $upload_dir . '/' . $filename;
    
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Colors
    $bg_color = imagecolorallocate($image, rand(180, 255), rand(180, 255), rand(180, 255));
    $text_color = imagecolorallocate($image, 50, 50, 50);
    
    // Fill background
    imagefill($image, 0, 0, $bg_color);
    
    // Add text
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($image, $font_size, $x, $y, $text, $text_color);
    
    // Save image
    imagejpeg($image, $filepath, 90);
    imagedestroy($image);
    
    echo "Created dummy image: $filename<br>";
    return 'uploads/' . $filename;
}

// Create dummy images for each demo post
$images = [
    'wooden_table.jpg' => 'Wooden Dining Table Project',
    'resume.jpg' => 'Engineering Resume Portfolio',
    'fitness_app.jpg' => 'Fitness Tracking Mobile App',
    'platform_game.jpg' => '2D Mario-Style Platform Game'
];

// Create each image and update the database
foreach ($images as $filename => $text) {
    $image_path = createDummyImage($filename, $text);
    
    // Get the associated post title
    $post_title = '';
    switch ($filename) {
        case 'wooden_table.jpg':
            $post_title = 'Building a Wooden Dining Table';
            break;
        case 'resume.jpg':
            $post_title = 'Creating an Engineering Resume Portfolio';
            break;
        case 'fitness_app.jpg':
            $post_title = 'Developing a Fitness Tracking Mobile App';
            break;
        case 'platform_game.jpg':
            $post_title = 'Creating a 2D Mario-Style Platform Game';
            break;
    }
    
    // Update database
    if (!empty($post_title)) {
        $stmt = $conn->prepare("UPDATE posts SET image_path = ? WHERE title = ?");
        $stmt->bind_param("ss", $image_path, $post_title);
        if ($stmt->execute()) {
            echo "Updated database with image path for post '$post_title'<br>";
        } else {
            echo "Failed to update database for post '$post_title': " . $conn->error . "<br>";
        }
    }
}

echo "<br><h2>All Done!</h2>";
echo "Dummy images have been created and associated with the demo posts.<br>";
echo "The demo accounts are now ready to use.<br>";
?> 