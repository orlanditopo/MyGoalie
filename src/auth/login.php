<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/src/pages/dashboard.php');
    exit();
}

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="form-container">
    <h2>Login</h2>
    <form action="<?php echo BASE_URL; ?>/src/auth/login_action.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p class="form-footer">
        Don't have an account? <a href="<?php echo BASE_URL; ?>/src/auth/register.php">Register here</a>
    </p>
</div>

<?php
// Include footer
include dirname(__DIR__) . '/templates/footer.php';
?> 