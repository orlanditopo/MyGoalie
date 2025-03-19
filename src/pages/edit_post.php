<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

// Get post ID from URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate post exists and belongs to the current user
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    $_SESSION['error_message'] = "Post not found or you don't have permission to edit it.";
    header("Location: dashboard.php");
    exit;
}

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="create-post-container">
    <h1>Edit Goal</h1>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-messages">
            <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        </div>
    <?php endif; ?>
    
    <form action="../actions/update_post.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
        
        <div class="input-field">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        
        <div class="input-field">
            <label for="content">Description</label>
            <textarea id="content" name="content" rows="6" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="planned" <?php echo $post['status'] === 'planned' ? 'selected' : ''; ?>>Planned</option>
                <option value="in-progress" <?php echo $post['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="completed" <?php echo $post['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="privacy">Privacy:</label>
            <select id="privacy" name="privacy" required>
                <option value="public" <?php echo $post['privacy'] === 'public' ? 'selected' : ''; ?>>Public (everyone can see)</option>
                <option value="friends" <?php echo $post['privacy'] === 'friends' ? 'selected' : ''; ?>>Friends Only</option>
                <option value="private" <?php echo $post['privacy'] === 'private' ? 'selected' : ''; ?>>Private (only me)</option>
            </select>
        </div>
        
        <div class="input-field">
            <label for="github_repo">GitHub Repository (optional)</label>
            <input type="text" id="github_repo" name="github_repo" value="<?php echo htmlspecialchars($post['github_repo']); ?>" placeholder="username/repository">
            <small>Example: username/repository</small>
        </div>
        
        <div class="input-field">
            <label for="code_snippet">Code Snippet (optional)</label>
            <textarea id="code_snippet" name="code_snippet" rows="6" class="code-editor"><?php echo htmlspecialchars($post['code_snippet']); ?></textarea>
        </div>
        
        <div class="input-field">
            <label for="image">
                Image (optional, max 5MB)
                <?php if (!empty($post['image_path'])): ?>
                    <span class="current-image-note">Current Image: <a href="javascript:void(0)" onclick="openImageModal('<?php echo BASE_URL . '/src/' . htmlspecialchars($post['image_path']); ?>')">View</a></span>
                <?php endif; ?>
            </label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
            <small>Leave empty to keep the current image. Upload a new one to replace.</small>
        </div>
        
        <button type="submit" class="btn-primary">Update Goal</button>
        <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn">Cancel</a>
    </form>
</div>

<!-- Image Modal for current image preview -->
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