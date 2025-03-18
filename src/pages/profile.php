<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/github.php';

// Require login
require_login();

// Get user profile data
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: dashboard.php");
    exit();
}

// Get user's goals
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$goals = $stmt->get_result();

// Check if we're friends with this user
$is_friend = false;
if ($user_id !== $_SESSION['user_id']) {
    $stmt = $conn->prepare("SELECT * FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $stmt->bind_param("iiii", $_SESSION['user_id'], $user_id, $user_id, $_SESSION['user_id']);
    $stmt->execute();
    $is_friend = $stmt->get_result()->num_rows > 0;
}

// Get GitHub activity if username is set
$github_activity = null;
if (!empty($user['github_username'])) {
    $github_activity = get_github_activity($user['github_username']);
}

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="profile-container">
    <div class="profile-header">
        <img src="<?php echo get_profile_image($user['id']); ?>" alt="Profile" class="profile-image">
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['username']); ?></h1>
            <p class="bio"><?php echo htmlspecialchars($user['bio'] ?? 'No bio yet.'); ?></p>
            <?php if ($user_id === $_SESSION['user_id']): ?>
                <a href="edit_profile.php" class="btn btn-secondary">Edit Profile</a>
            <?php endif; ?>
            <?php if ($user_id !== $_SESSION['user_id']): ?>
                <?php if (!$is_friend): ?>
                    <form action="../actions/add_friend.php" method="post" style="margin-top: 1rem;">
                        <input type="hidden" name="friend_id" value="<?php echo $user_id; ?>">
                        <button type="submit" class="btn btn-primary">Add Friend</button>
                    </form>
                <?php else: ?>
                    <span class="friend-status">Friends</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="goals-section">
        <h2><?php echo $user_id === $_SESSION['user_id'] ? 'My Goals' : htmlspecialchars($user['username']) . "'s Goals"; ?></h2>
        <?php if ($goals->num_rows > 0): ?>
            <?php while ($goal = $goals->fetch_assoc()): ?>
                <div class="goal-card">
                    <h3><?php echo htmlspecialchars($goal['title']); ?></h3>
                    <p><?php echo htmlspecialchars($goal['content']); ?></p>
                    <div class="goal-meta">
                        <span class="date"><?php echo format_date($goal['created_at']); ?></span>
                        <span class="status <?php echo $goal['status']; ?>"><?php echo ucfirst($goal['status']); ?></span>
                    </div>
                    <?php if ($user_id === $_SESSION['user_id']): ?>
                        <div class="goal-actions">
                            <a href="../actions/edit_post.php?id=<?php echo $goal['id']; ?>" class="btn btn-secondary">Edit</a>
                            <a href="../actions/delete_post.php?id=<?php echo $goal['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this goal?')">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-goals">No goals yet.</p>
        <?php endif; ?>
    </div>

    <?php if ($github_activity !== null): ?>
        <div class="github-section">
            <h2>GitHub Activity</h2>
            <?php echo format_github_activity($github_activity); ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include dirname(__DIR__) . '/templates/footer.php';
?> 