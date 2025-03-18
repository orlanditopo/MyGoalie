<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/github.php';

// Require login
require_login();

// Check if post ID is set
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$post_id = (int)$_GET['id'];

// Get post data
$stmt = $conn->prepare("
    SELECT p.*, u.username, u.github_username 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    header("Location: dashboard.php");
    exit();
}

// Get post updates
$stmt = $conn->prepare("SELECT * FROM post_updates WHERE post_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$updates = $stmt->get_result();

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

<div class="container">
    <div class="post-container">
        <div class="post-header">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span class="post-author">By: <?php echo htmlspecialchars($post['username']); ?></span>
                <span class="post-date">Created: <?php echo format_date($post['created_at']); ?></span>
                <span class="status <?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span>
            </div>
        </div>
        
        <div class="post-content">
            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            
            <?php if (!empty($post['image_path'])): ?>
                <div class="post-image">
                    <img src="<?php echo BASE_URL . '/src/' . htmlspecialchars($post['image_path']); ?>" alt="Goal image">
                </div>
            <?php endif; ?>
            
            <?php if ($has_github): ?>
                <div class="github-link">
                    <h3>GitHub Repository</h3>
                    <a href="https://github.com/<?php echo htmlspecialchars($post['github_repo']); ?>" target="_blank">
                        <?php echo htmlspecialchars($post['github_repo']); ?> 📁
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($post['code_snippet'])): ?>
                <div class="code-snippet">
                    <h3>Code Snippet</h3>
                    <pre><code><?php echo htmlspecialchars($post['code_snippet']); ?></code></pre>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($updates->num_rows > 0): ?>
            <div class="post-updates">
                <h2>Updates</h2>
                <?php while ($update = $updates->fetch_assoc()): ?>
                    <div class="update-item">
                        <div class="update-meta">
                            <span class="update-date"><?php echo format_date($update['created_at']); ?></span>
                        </div>
                        <div class="update-content">
                            <p><?php echo nl2br(htmlspecialchars($update['content'])); ?></p>
                            
                            <?php if (!empty($update['image_path'])): ?>
                                <div class="post-image">
                                    <img src="<?php echo BASE_URL . '/src/' . htmlspecialchars($update['image_path']); ?>" alt="Update image">
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($update['github_commit'])): ?>
                                <div class="github-commit">
                                    <a href="https://github.com/<?php echo htmlspecialchars($post['github_repo']); ?>/commit/<?php echo htmlspecialchars($update['github_commit']); ?>" target="_blank">
                                        View commit: <?php echo substr($update['github_commit'], 0, 7); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($update['code_snippet'])): ?>
                                <div class="code-snippet">
                                    <h4>Code Snippet</h4>
                                    <pre><code><?php echo htmlspecialchars($update['code_snippet']); ?></code></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($post['user_id'] === $_SESSION['user_id'] || is_admin()): ?>
            <div class="add-update">
                <h2>Add an Update</h2>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="error-messages">
                        <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
                    </div>
                <?php endif; ?>
                
                <form action="../actions/add_update.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    
                    <div class="input-field">
                        <label for="content">Update Content</label>
                        <textarea id="content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="input-field">
                        <label for="image">Image (optional)</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>Add an image to show your progress (max 5MB).</small>
                    </div>
                    
                    <?php if ($has_github): ?>
                        <div class="input-field">
                            <label for="github_commit">GitHub Commit (optional)</label>
                            <select id="github_commit" name="github_commit">
                                <option value="">None</option>
                                <?php foreach ($commits as $commit): ?>
                                    <option value="<?php echo $commit['sha']; ?>">
                                        <?php echo substr($commit['sha'], 0, 7) . ' - ' . htmlspecialchars(substr($commit['commit']['message'], 0, 50)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>Link this update to a specific commit.</small>
                        </div>
                        
                        <div class="input-field">
                            <label for="code_snippet">Code Snippet (optional)</label>
                            <textarea id="code_snippet" name="code_snippet" rows="6" class="code-editor"></textarea>
                            <small>Share updated code to show your progress.</small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="input-field">
                        <label for="status">Update Goal Status</label>
                        <select id="status" name="status">
                            <option value="">Keep current status</option>
                            <option value="planned" <?php echo $post['status'] === 'planned' ? 'selected' : ''; ?>>Planned</option>
                            <option value="in-progress" <?php echo $post['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $post['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary">Add Update</button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="post-actions">
            <?php if ($post['user_id'] === $_SESSION['user_id']): ?>
                <a href="edit_post.php?id=<?php echo $post_id; ?>" class="btn btn-secondary">Edit Goal</a>
                <a href="../actions/delete_post.php?id=<?php echo $post_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this goal?')">Delete Goal</a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php
// Include footer
include dirname(__DIR__) . '/templates/footer.php';
?> 