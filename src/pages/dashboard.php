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
        <a href="<?php echo BASE_URL; ?>/src/pages/create_post.php" class="btn btn-primary">Create New Goal</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <div class="feed-container">
        <?php
        // Get all goals (personal and friends') in a single query
        $stmt = $conn->prepare("
            SELECT p.*, u.username, u.github_username,
                   CASE 
                       WHEN p.user_id = ? THEN 1
                       ELSE 0
                   END as is_own_goal
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.user_id = ? 
               OR p.user_id IN (
                   SELECT CASE 
                       WHEN user_id = ? THEN friend_id
                       ELSE user_id
                   END
                   FROM friendships
                   WHERE (user_id = ? OR friend_id = ?)
                   AND status = 'accepted'
               )
            ORDER BY p.created_at DESC
        ");
        $stmt->bind_param("iiiii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($post = $result->fetch_assoc()) {
                ?>
                <div class="goal-card">
                    <div class="goal-header">
                        <div class="goal-author">
                            <img src="<?php echo get_profile_image($post['user_id']); ?>" alt="Profile" class="profile-image-small">
                            <span><?php echo htmlspecialchars($post['username']); ?></span>
                            <?php if ($post['is_own_goal']): ?>
                                <span class="own-goal-badge">Your Goal</span>
                            <?php endif; ?>
                        </div>
                        <div class="goal-meta">
                            <span class="date"><?php echo format_date($post['created_at']); ?></span>
                            <span class="status <?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span>
                        </div>
                    </div>
                    
                    <h3><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                    
                    <?php if (!empty($post['image_path'])): ?>
                        <div class="goal-image" onclick="openImageModal('<?php echo BASE_URL . '/src/' . htmlspecialchars($post['image_path']); ?>')">
                            <img src="<?php echo BASE_URL . '/src/' . htmlspecialchars($post['image_path']); ?>" alt="Goal image">
                        </div>
                    <?php endif; ?>
                    
                    <div class="goal-content">
                        <div class="content-preview">
                            <p><?php echo htmlspecialchars(substr($post['content'], 0, 150)) . (strlen($post['content']) > 150 ? '...' : ''); ?></p>
                            <?php if (strlen($post['content']) > 150): ?>
                                <button class="show-more-btn" onclick="toggleContent(this)">Show More</button>
                            <?php endif; ?>
                        </div>
                        <div class="content-full" style="display: none;">
                            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                            <button class="show-less-btn" onclick="toggleContent(this)">Show Less</button>
                        </div>
                    </div>
                    
                    <?php if (!empty($post['github_repo'])): ?>
                        <div class="github-integration">
                            <a href="https://github.com/<?php echo htmlspecialchars($post['github_repo']); ?>" target="_blank" class="github-badge">
                                <span>GitHub: <?php echo htmlspecialchars($post['github_repo']); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($post['code_snippet'])): ?>
                        <div class="code-snippet">
                            <pre><code><?php echo htmlspecialchars($post['code_snippet']); ?></code></pre>
                        </div>
                    <?php endif; ?>
                    
                    <div class="goal-actions">
                        <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">View Details</a>
                        <?php if ($post['is_own_goal']): ?>
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">Edit</a>
                            <a href="../actions/delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this goal?')">Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p class="no-goals">No goals to display. <a href="create_post.php">Create your first goal!</a></p>';
        }
        ?>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <span class="close-modal" onclick="closeImageModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
function toggleContent(button) {
    const card = button.closest('.goal-card');
    const preview = card.querySelector('.content-preview');
    const full = card.querySelector('.content-full');
    
    if (button.classList.contains('show-more-btn')) {
        preview.style.display = 'none';
        full.style.display = 'block';
    } else {
        preview.style.display = 'block';
        full.style.display = 'none';
    }
}

// Image modal functions
function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    modal.style.display = 'flex';
    modalImg.src = imageSrc;
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

// Close modal when clicking outside the image
window.onclick = function(event) {
    const modal = document.getElementById('imageModal');
    if (event.target === modal) {
        closeImageModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});
</script>

<?php
// Include footer
include dirname(__DIR__) . '/templates/footer.php';
?>
