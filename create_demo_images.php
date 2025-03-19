<?php
// Script to set up demo images for MyGoalie
require_once 'src/includes/config.php';
require_once 'src/includes/db.php';

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/src/uploads';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
    echo "Created uploads directory at $upload_dir<br>";
}

// Demo image URLs
$image_urls = [
    'wooden_table.jpg' => 'https://images.unsplash.com/photo-1615875605825-5eb9bb5d52ac',
    'resume.jpg' => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4',
    'fitness_app.jpg' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1',
    'platform_game.jpg' => 'https://images.unsplash.com/photo-1579373903781-fd5c0c30c4cd'
];

// Download and save images
foreach ($image_urls as $filename => $url) {
    $filepath = $upload_dir . '/' . $filename;
    
    if (file_exists($filepath)) {
        echo "Image $filename already exists<br>";
        continue;
    }
    
    try {
        // Download the image data
        $imageData = file_get_contents($url);
        
        if ($imageData === false) {
            echo "Failed to download image from $url<br>";
            continue;
        }
        
        // Save the image
        $result = file_put_contents($filepath, $imageData);
        
        if ($result === false) {
            echo "Failed to save image to $filepath<br>";
        } else {
            echo "Successfully downloaded and saved $filename<br>";
            
            // Update the database to reference the correct image path
            $image_path = 'uploads/' . $filename;
            
            // Get the post ID based on the image filename (simplified mapping)
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
    } catch (Exception $e) {
        echo "Error processing $filename: " . $e->getMessage() . "<br>";
    }
}

echo "<br><h2>All Done!</h2>";
echo "Images have been downloaded and associated with the demo posts.<br>";
echo "The demo accounts are now ready to use.<br>";
?> 