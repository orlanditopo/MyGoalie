<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio']);
    $github_username = trim($_POST['github_username']);
    
    $stmt = $conn->prepare("UPDATE users SET bio = ?, github_username = ? WHERE id = ?");
    $stmt->bind_param("ssi", $bio, $github_username, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to update profile.";
    }
}

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="profile-container">
    <div class="edit-profile-form">
        <h1>Edit Profile</h1>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-messages">
                <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
            </div>
        <?php endif; ?>
        <form action="edit_profile.php" method="post">
            <div class="input-field">
                <label for="username">Username</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>
            <div class="input-field">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="input-field">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            <div class="input-field">
                <label for="github_username">GitHub Username</label>
                <input type="text" id="github_username" name="github_username" value="<?php echo htmlspecialchars($user['github_username'] ?? ''); ?>">
                <small>Enter your GitHub username to display your activity on your profile.</small>
            </div>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<?php
// Include footer
include dirname(__DIR__) . '/templates/footer.php';
?>
