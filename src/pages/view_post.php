<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/github.php';

// Require login
require_login();

// Get post ID from URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if we're looking at a child post (update/milestone/commit)
$is_child_post = false;
$parent_post = null;

// Find the post
$stmt = $conn->prepare("SELECT p.*, u.username, u.github_username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.deleted_at IS NULL");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    $_SESSION['error_message'] = "Post not found.";
    header("Location: dashboard.php");
    exit;
}

// If this is a child post, get the parent post (goalie)
if (!empty($post['parent_id'])) {
    $is_child_post = true;
    $stmt = $conn->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.deleted_at IS NULL");
    $stmt->bind_param("i", $post['parent_id']);
    $stmt->execute();
    $parent_post = $stmt->get_result()->fetch_assoc();
    
    if (!$parent_post) {
        $_SESSION['error_message'] = "Parent post not found.";
        header("Location: dashboard.php");
        exit;
    }
    
    // Set the main post to the parent (goalie)
    $goalie_post = $parent_post;
    $current_update = $post;
} else {
    // This is a parent/goalie post
    $goalie_post = $post;
    $current_update = null;
}

// Get all updates/threads for this post
$stmt = $conn->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.parent_id = ? AND p.deleted_at IS NULL ORDER BY p.created_at DESC");
$stmt->bind_param("i", $goalie_post['id']);
$stmt->execute();
$updates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check if the post has GitHub repo
$has_github = !empty($post['github_repo']);

// Get commits if GitHub repo is set
$commits = [];
if ($has_github) {
    list($owner, $repo) = explode('/', $post['github_repo']);
    $commits = get_repository_commits($post['github_repo']);
}

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="post-container">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-messages">
            <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Breadcrumb navigation for child posts -->
    <?php if ($is_child_post): ?>
        <div class="breadcrumb">
            <a href="view_post.php?id=<?php echo $goalie_post['id']; ?>">
                <i class="fas fa-arrow-left"></i> Back to <?php echo htmlspecialchars($goalie_post['title']); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <!-- Main Goalie Post -->
    <div class="post-header">
        <h1>
            <?php echo htmlspecialchars($goalie_post['title']); ?>
            <span class="goalie-indicator">Goalie</span>
        </h1>
        <div class="post-meta">
            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($goalie_post['username']); ?></span>
            <span><i class="fas fa-calendar"></i> <?php echo format_date($goalie_post['created_at']); ?></span>
            <span class="status <?php echo $goalie_post['status']; ?>"><?php echo ucfirst($goalie_post['status']); ?></span>
            <div class="privacy-indicator privacy-<?php echo $goalie_post['privacy']; ?>">
                <?php echo ucfirst($goalie_post['privacy']); ?>
            </div>
        </div>
    </div>
    
    <div class="post-content">
        <?php if (!empty($goalie_post['image_path'])): ?>
            <div class="post-image" onclick="openImageModal('<?php echo BASE_URL . '/src/' . htmlspecialchars($goalie_post['image_path']); ?>')">
                <img src="<?php echo BASE_URL . '/src/' . htmlspecialchars($goalie_post['image_path']); ?>" alt="Post image">
            </div>
        <?php endif; ?>
        
        <p><?php echo nl2br(htmlspecialchars($goalie_post['content'])); ?></p>
        
        <?php if (!empty($goalie_post['github_repo'])): ?>
            <div class="github-link">
                <h3><i class="fab fa-github"></i> GitHub Repository</h3>
                <a href="https://github.com/<?php echo htmlspecialchars($goalie_post['github_repo']); ?>" target="_blank">
                    <?php echo htmlspecialchars($goalie_post['github_repo']); ?>
                </a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($goalie_post['code_snippet'])): ?>
            <div class="code-snippet">
                <h3>Code Snippet</h3>
                <pre><code><?php echo htmlspecialchars($goalie_post['code_snippet']); ?></code></pre>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($is_child_post && isset($current_update)): ?>
        <!-- Show current update/thread item -->
        <div class="current-update">
            <h2>Current <?php echo ucfirst($current_update['thread_type']); ?></h2>
            <div class="goal-thread-item">
                <div class="thread-meta">
                    <div>
                        <span class="thread-type <?php echo $current_update['thread_type']; ?>"><?php echo ucfirst($current_update['thread_type']); ?></span>
                        <span><?php echo format_date($current_update['created_at']); ?></span>
                    </div>
                </div>
                
                <?php if (!empty($current_update['image_path'])): ?>
                    <div class="update-image" onclick="openImageModal('<?php echo BASE_URL . '/src/' . htmlspecialchars($current_update['image_path']); ?>')">
                        <img src="<?php echo BASE_URL . '/src/' . htmlspecialchars($current_update['image_path']); ?>" alt="Update image">
                    </div>
                <?php endif; ?>
                
                <div class="update-content">
                    <p><?php echo nl2br(htmlspecialchars($current_update['content'])); ?></p>
                </div>
                
                <?php if (!empty($current_update['github_repo'])): ?>
                    <div class="github-link">
                        <h4><i class="fab fa-github"></i> GitHub Repository</h4>
                        <a href="https://github.com/<?php echo htmlspecialchars($current_update['github_repo']); ?>" target="_blank">
                            <?php echo htmlspecialchars($current_update['github_repo']); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($current_update['code_snippet'])): ?>
                    <div class="code-snippet">
                        <h4>Code Snippet</h4>
                        <pre><code><?php echo htmlspecialchars($current_update['code_snippet']); ?></code></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Add Update Form (only shown on parent/goalie post view) -->
    <?php if (!$is_child_post && $goalie_post['user_id'] == $_SESSION['user_id']): ?>
        <div class="add-update">
            <h2>Add an Update</h2>
            <form action="../actions/add_thread.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="parent_id" value="<?php echo $goalie_post['id']; ?>">
                
                <div class="input-field">
                    <label for="thread_type">Update Type</label>
                    <select id="thread_type" name="thread_type">
                        <option value="update">General Update</option>
                        <option value="milestone">Milestone</option>
                        <option value="commit">Code Commit</option>
                    </select>
                </div>
                
                <div class="input-field">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" rows="4" required></textarea>
                </div>
                
                <div class="input-field">
                    <label for="github_repo">GitHub Repository (optional)</label>
                    <input type="text" id="github_repo" name="github_repo" placeholder="username/repository">
                    <small>Example: username/repository</small>
                </div>
                
                <div class="input-field">
                    <label for="code_snippet">Code Snippet (optional)</label>
                    <textarea id="code_snippet" name="code_snippet" rows="4" class="code-editor"></textarea>
                </div>
                
                <div class="input-field">
                    <label for="image">Image (optional, max 5MB)</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small>Allowed formats: JPEG, PNG, GIF, WEBP. Maximum size: 5MB.</small>
                </div>
                
                <button type="submit" class="btn-primary">Add Update</button>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Thread/Updates List -->
    <?php if (!empty($updates)): ?>
        <div class="goal-thread-container">
            <h2>Updates &amp; Progress</h2>
            
            <?php foreach ($updates as $update): ?>
                <?php if ($is_child_post && $update['id'] == $current_update['id']) continue; // Skip current update if we're viewing an update page ?>
                
                <div class="goal-thread-item">
                    <div class="thread-meta">
                        <div>
                            <span class="thread-type <?php echo $update['thread_type']; ?>"><?php echo ucfirst($update['thread_type']); ?></span>
                            <span><?php echo format_date($update['created_at']); ?></span>
                        </div>
                        <a href="view_post.php?id=<?php echo $update['id']; ?>">View Details</a>
                    </div>
                    
                    <?php if (!empty($update['image_path'])): ?>
                        <div class="update-image" onclick="openImageModal('<?php echo BASE_URL . '/src/' . htmlspecialchars($update['image_path']); ?>')">
                            <img src="<?php echo BASE_URL . '/src/' . htmlspecialchars($update['image_path']); ?>" alt="Update image">
                        </div>
                    <?php endif; ?>
                    
                    <div class="update-content">
                        <p><?php echo nl2br(htmlspecialchars(substr($update['content'], 0, 200)) . (strlen($update['content']) > 200 ? '...' : '')); ?></p>
                    </div>
                    
                    <?php if (!empty($update['github_repo'])): ?>
                        <div class="github-link">
                            <a href="https://github.com/<?php echo htmlspecialchars($update['github_repo']); ?>" target="_blank">
                                <i class="fab fa-github"></i> <?php echo htmlspecialchars($update['github_repo']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?php if (!$is_child_post): ?>
            <div class="no-updates">
                <p>No updates have been posted for this goal yet.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="post-actions">
        <?php if ($goalie_post['user_id'] == $_SESSION['user_id']): ?>
            <a href="edit_post.php?id=<?php echo $goalie_post['id']; ?>" class="btn btn-secondary">Edit Goal</a>
            <a href="../actions/delete_post.php?id=<?php echo $goalie_post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this goal?')">Delete Goal</a>
        <?php endif; ?>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <span class="close-modal" onclick="closeImageModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
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