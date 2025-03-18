<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];

$result = $conn->query("SELECT * FROM users WHERE id='$user_id'");
$user = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .edit-profile-form img {
            max-width: 200px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Profile</h1>
        <form action="update_profile.php" method="post" enctype="multipart/form-data" class="edit-profile-form">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="input-group">
                <label for="bio">Bio:</label>
                <textarea name="bio" id="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>
            <div class="input-group">
                <label for="profile_picture">Profile Picture:</label>
                <?php if ($user['profile_picture']) { ?>
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                <label for="remove_picture">Remove Picture:</label>
                <input type="checkbox" name="remove_picture" id="remove_picture">
                <?php } else { ?>
                <input type="file" name="profile_picture" id="profile_picture">
                <?php } ?>
            </div>
            <button type="submit" class="btn">Save Changes</button>
        </form>
        <a href="my_profile.php" class="btn">Back to My Profile</a>
    </div>
</body>
</html>
