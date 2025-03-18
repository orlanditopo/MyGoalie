<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyGoalie - Track Your Goals</title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="nav-brand">
                <a href="<?php echo BASE_URL; ?>">MyGoalie</a>
            </div>
            <div class="nav-links">
                <?php if (is_logged_in()): ?>
                    <a href="<?php echo BASE_URL; ?>/src/pages/dashboard.php">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/src/pages/my_profile.php">My Profile</a>
                    <a href="<?php echo BASE_URL; ?>/src/pages/friends.php">Friends</a>
                    <a href="<?php echo BASE_URL; ?>/src/auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/src/auth/login.php">Login</a>
                    <a href="<?php echo BASE_URL; ?>/src/auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main> 