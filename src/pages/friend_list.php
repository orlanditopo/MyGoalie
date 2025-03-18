<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';
require_once 'friends.php';

$user_id = $_SESSION['user_id'];
$incoming_requests = get_incoming_friend_requests($user_id);
$friends = get_friends($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept'])) {
        $from_user_id = $_POST['from_user_id'];
        accept_friend_request($from_user_id, $user_id);
    } elseif (isset($_POST['reject'])) {
        $from_user_id = $_POST['from_user_id'];
        reject_friend_request($from_user_id, $user_id);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Friend List</h1>
        <div class="dashboard-buttons">
            <a href="my_profile.php" class="btn">My Profile</a>
            <a href="dashboard.php" class="btn">Dashboard</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
        
        <h2>Incoming Friend Requests</h2>
        <?php while ($row = $incoming_requests->fetch_assoc()): ?>
            <div class="friend-request">
                <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" width="50" height="50" style="border-radius: 50%;">
                <?php echo htmlspecialchars($row['username']); ?>
                <form action="" method="post" class="request-actions">
                    <input type="hidden" name="from_user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                    <button type="submit" name="accept" class="btn">Accept</button>
                    <button type="submit" name="reject" class="btn">Reject</button>
                </form>
            </div>
        <?php endwhile; ?>

        <h2>Friends</h2>
        <?php while ($row = $friends->fetch_assoc()): ?>
            <div class="friend">
                <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" width="50" height="50" style="border-radius: 50%;">
                <?php echo htmlspecialchars($row['username']); ?>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>