<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - MyGoalie' : 'MyGoalie'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/src/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
        // Check if dark mode should be enabled
        const darkMode = localStorage.getItem('darkMode') === 'enabled';
        if (darkMode) {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
        }
    </script>
</head>
<body class="<?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'enabled' ? 'dark-mode' : ''; ?>">
    <div class="wrapper">
        <header>
            <nav>
                <div class="logo">
                    <a href="<?php echo BASE_URL; ?>/src/pages/dashboard.php">MyGoalie</a>
                </div>
                <div class="nav-right">
                    <ul class="nav-links">
                        <?php if (is_logged_in()): ?>
                            <li><a href="<?php echo BASE_URL; ?>/src/pages/dashboard.php">Dashboard</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/pages/discover.php">Discover</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/pages/profile.php">Profile</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/pages/friends.php">Friends</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/pages/create_post.php">New Goal</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/actions/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>/src/pages/login.php">Login</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/pages/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                    <div class="dark-mode-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" id="darkModeToggle" <?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'enabled' ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="mode-icon" id="modeIcon">
                            <?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'enabled' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>'; ?>
                        </span>
                    </div>
                </div>
            </nav>
        </header>
        <main> 