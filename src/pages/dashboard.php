<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <a href="<?php echo BASE_URL; ?>/src/actions/create_post.php" class="btn btn-primary">Create New Goal</a>
    </div>

    <div class="dashboard-content">
        <div class="goals-section">
            <h2>Your Goals</h2>
            <?php
            global $conn;
            $stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($post = $result->fetch_assoc()) {
                    ?>
                    <div class="goal-card">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                        <div class="goal-meta">
                            <span class="date"><?php echo format_date($post['created_at']); ?></span>
                            <span class="status <?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span>
                        </div>
                        <div class="goal-actions">
                            <a href="<?php echo BASE_URL; ?>/src/actions/edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">Edit</a>
                            <a href="<?php echo BASE_URL; ?>/src/actions/delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this goal?')">Delete</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-goals">You haven\'t created any goals yet. <a href="' . BASE_URL . '/src/actions/create_post.php">Create your first goal!</a></p>';
            }
            ?>
        </div>

        <div class="friends-section">
            <h2>Friends' Goals</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT p.*, u.username 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                JOIN friendships f ON (f.user_id = ? AND f.friend_id = p.user_id) 
                OR (f.friend_id = ? AND f.user_id = p.user_id)
                WHERE p.status = 'public'
                ORDER BY p.created_at DESC
            ");
            $stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($post = $result->fetch_assoc()) {
                    ?>
                    <div class="goal-card">
                        <div class="goal-author">
                            <img src="<?php echo get_profile_image($post['user_id']); ?>" alt="Profile" class="profile-image">
                            <span><?php echo htmlspecialchars($post['username']); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                        <div class="goal-meta">
                            <span class="date"><?php echo format_date($post['created_at']); ?></span>
                            <span class="status <?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-goals">No friends\' goals to display. <a href="' . BASE_URL . '/src/pages/friends.php">Find some friends!</a></p>';
            }
            ?>
        </div>
    </div>
</div>

<?php
// Include footer
include dirname(__DIR__) . '/templates/footer.php';
?>
