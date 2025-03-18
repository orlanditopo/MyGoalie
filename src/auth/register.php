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
            <form action="register_action.php" method="post">
                <div class="input-field">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="input-field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="input-field">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="input-field">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="index.php">Log in</a></p>
        </div>
    </div>
</body>
</html>

