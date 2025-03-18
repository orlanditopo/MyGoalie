<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/github.php';

// Require login
require_login();

// Get user's GitHub username
$stmt = $conn->prepare("SELECT github_username FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$github_username = $user['github_username'] ?? '';

// Get repositories if GitHub username is set
$repositories = [];
if (!empty($github_username)) {
    $repositories = get_github_repositories($github_username);
}

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="container">
    <div class="create-post-container">
        <h1>Create New Goal</h1>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-messages">
                <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
            </div>
        <?php endif; ?>
        
        <form action="../actions/submit_post.php" method="post">
            <div class="input-field">
                <label for="title">Goal Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="input-field">
                <label for="content">Description</label>
                <textarea id="content" name="content" rows="5" required></textarea>
                <small>Describe what you want to achieve with this goal.</small>
            </div>
            
            <div class="input-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="planned">Planned</option>
                    <option value="in-progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            
            <div class="github-section">
                <h3>GitHub Integration</h3>
                
                <?php if (empty($github_username)): ?>
                    <div class="github-notice">
                        <p>To link your goals with GitHub projects, <a href="edit_profile.php">add your GitHub username</a> first.</p>
                    </div>
                <?php else: ?>
                    <div class="input-field">
                        <label for="github_repo">GitHub Repository</label>
                        <select id="github_repo" name="github_repo">
                            <option value="">None</option>
                            <?php foreach ($repositories as $repo): ?>
                                <option value="<?php echo htmlspecialchars($repo['full_name']); ?>">
                                    <?php echo htmlspecialchars($repo['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Connect this goal to one of your GitHub repositories.</small>
                    </div>
                    
                    <div class="input-field">
                        <label for="code_snippet">Code Snippet (optional)</label>
                        <textarea id="code_snippet" name="code_snippet" rows="8" class="code-editor"></textarea>
                        <small>Share a snippet of your code to highlight your progress.</small>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn-primary">Create Goal</button>
        </form>
    </div>
</div>

<script>
    // Show/hide code snippet field based on repository selection
    document.getElementById('github_repo').addEventListener('change', function() {
        const codeSnippetField = document.getElementById('code_snippet').parentElement;
        if (this.value) {
            codeSnippetField.style.display = 'block';
        } else {
            codeSnippetField.style.display = 'none';
        }
    });
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const githubRepo = document.getElementById('github_repo');
        if (githubRepo) {
            const codeSnippetField = document.getElementById('code_snippet').parentElement;
            codeSnippetField.style.display = githubRepo.value ? 'block' : 'none';
        }
    });
</script>

<?php
// Include footer
include dirname(__DIR__) . '/templates/footer.php';
?> 