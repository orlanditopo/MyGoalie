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
        <h1>Discover</h1>
        <div class="feed-filter">
            <a href="<?php echo BASE_URL; ?>/src/pages/dashboard.php" class="btn btn-secondary">My Feed</a>
            <a href="<?php echo BASE_URL; ?>/src/pages/discover.php" class="btn btn-primary">Discover</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="feed-container">
        <?php
        // Get all public posts excluding the user's own posts
        $stmt = $conn->prepare("
            SELECT p.*, u.username, u.github_username 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.privacy = 'public' 
            AND p.deleted_at IS NULL
            ORDER BY p.created_at DESC
            LIMIT 50
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($post = $result->fetch_assoc()) {
                ?>
                <div class="post-item">
                    <div class="post-meta">
                        <div class="post-author">
                            <a href="<?php echo BASE_URL; ?>/src/pages/profile.php?user_id=<?php echo $post['user_id']; ?>">
                                <img src="<?php echo get_profile_image($post['user_id']); ?>" alt="Profile" class="profile-image-small">
                                <?php echo htmlspecialchars($post['username']); ?>
                            </a>
                        </div>
                        <div class="post-date">
                            <?php echo format_date($post['created_at']); ?>
                        </div>
                    </div>
                    
                    <div class="post-header">
                        <h3>
                            <?php if (empty($post['parent_id'])): ?>
                                <span class="goalie-indicator">Goalie</span>
                            <?php else: ?>
                                <span class="thread-type <?php echo $post['thread_type']; ?>"><?php echo ucfirst($post['thread_type']); ?></span>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/src/pages/view_post.php?id=<?php echo $post['id']; ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                    </div>
                    
                    <?php if (!empty($post['image_path'])): ?>
                        <div class="post-image">
                            <img src="<?php echo BASE_URL . '/' . 'src/' . $post['image_path']; ?>" alt="Post image">
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-content">
                        <?php 
                        // Display a preview of the content
                        $preview = strlen($post['content']) > 200 ? substr($post['content'], 0, 200) . '...' : $post['content'];
                        echo nl2br(htmlspecialchars($preview)); 
                        ?>
                    </div>
                    
                    <div class="post-footer">
                        <div class="post-status <?php echo $post['status']; ?>">
                            <?php echo ucfirst($post['status']); ?>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/src/pages/view_post.php?id=<?php echo $post['id']; ?>" class="btn-view-post">View Details</a>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="no-posts">
                <p>No public posts found. Be the first to share!</p>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<?php
// Include footer
include dirname(__DIR__) . '/templates/footer.php';
?> 