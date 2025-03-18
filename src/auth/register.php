<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main">
        <div class="login-form">
            <h1>Register</h1>
            <?php if (isset($_SESSION['register_errors'])): ?>
                <div class="error-messages">
                    <?php 
                    foreach ($_SESSION['register_errors'] as $error) {
                        echo "<p class='error'>$error</p>";
                    }
                    unset($_SESSION['register_errors']);
                    ?>
                </div>
            <?php endif; ?>
            <form action="register_action.php" method="post">
                <div class="input-field">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required 
                           value="<?php echo isset($_SESSION['register_data']['username']) ? htmlspecialchars($_SESSION['register_data']['username']) : ''; ?>">
                </div>
                <div class="input-field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required
                           value="<?php echo isset($_SESSION['register_data']['email']) ? htmlspecialchars($_SESSION['register_data']['email']) : ''; ?>">
                </div>
                <div class="input-field">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">👁️</button>
                    </div>
                </div>
                <div class="input-field">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-container">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁️</button>
                    </div>
                </div>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
    </script>
</body>
</html>

