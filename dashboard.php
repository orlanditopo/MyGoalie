<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

$sql = "SELECT posts.*, users.username, users.profile_picture FROM posts INNER JOIN users ON posts.user_id = users.id ORDER BY created_at DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <div class="dashboard-buttons">
            <a href="my_profile.php" class="btn">My Profile</a>
            <a href="create_post.php" class="btn">Create Post</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
        <div class="timeline">
            <h2>Timeline</h2>
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="post">
                <div class="post-header">
                    <div class="post-author"><img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" width="50" height="50" style="border-radius: 50%;"> <?php echo htmlspecialchars($row['username']); ?></div>
                    <div class="post-timestamp"><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($row['created_at']))); ?></div>
                </div>
                <div class="post-content">
                    <?php if (!empty($row['image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Post Image" width="300">
                    <?php endif; ?>
                    <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
