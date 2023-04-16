<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Get user's posts
$sql = "SELECT * FROM posts WHERE user_id='$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);

// Delete post if delete button is clicked
if (isset($_POST['delete_post'])) {
    $post_id = $_POST['post_id'];

    // Delete post image file if it exists
    $result = $conn->query("SELECT image FROM posts WHERE id='$post_id'");
    $post = $result->fetch_assoc();
    if ($post['image']) {
        unlink($post['image']);
    }

    // Delete post from database
    $conn->query("DELETE FROM posts WHERE id='$post_id'");

    // Redirect to current page to update post list
    header("Location: my_profile.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>My Profile</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <div class="dashboard-buttons">
            <a href="edit_profile.php" class="btn">Edit Profile</a>
            <a href="create_post.php" class="btn">Create Post</a>
            <a href="logout.php" class="btn">Logout</a>
            <a href="dashboard.php" class="btn">Dashboard</a>
            <a href="friend_list.php" class="btn">Friend List</a>
        </div>
        <h2>My Posts</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="post">
            <div class="post-header">
                <div class="post-timestamp"><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($row['created_at']))); ?></div>
            </div>
            <div class="post-content">
                <?php if (!empty($row['image'])): ?>
                <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Post Image" width="300">
                <?php endif; ?>
                <?php echo nl2br(htmlspecialchars($row['content'])); ?>
            </div>
            <form action="" method="post">
                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                <button type="submit" name="delete_post" class="btn">Delete Post</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>