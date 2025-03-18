<?php
require_once 'src/includes/config.php';
require_once 'src/includes/functions.php';

// Include header
include 'src/templates/header.php';
?>

<div class="welcome-section">
    <h1>Welcome to MyGoalie</h1>
    <p>Track your goals, share your achievements, and connect with others on their journey.</p>
    
    <?php if (!is_logged_in()): ?>
        <div class="cta-buttons">
            <a href="<?php echo BASE_URL; ?>/src/auth/register.php" class="btn btn-primary">Get Started</a>
            <a href="<?php echo BASE_URL; ?>/src/auth/login.php" class="btn btn-secondary">Login</a>
        </div>
    <?php else: ?>
        <div class="cta-buttons">
            <a href="<?php echo BASE_URL; ?>/src/pages/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'src/templates/footer.php';
?>