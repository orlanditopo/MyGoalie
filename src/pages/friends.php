<?php

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Require login
require_login();

// Get friend requests
$stmt = $conn->prepare("
    SELECT u.*, f.id as friendship_id 
    FROM users u 
    JOIN friendships f ON u.id = f.user_id 
    WHERE f.friend_id = ? AND f.status = 'pending'
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$friend_requests = $stmt->get_result();

// Get current friends
$stmt = $conn->prepare("
    SELECT u.* 
    FROM users u 
    JOIN friendships f ON (f.user_id = ? AND f.friend_id = u.id) OR (f.friend_id = ? AND f.user_id = u.id)
    WHERE f.status = 'accepted'
");
$stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$friends = $stmt->get_result();

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="friends-container">
    <h1>Friends</h1>

    <?php if ($friend_requests->num_rows > 0): ?>
        <div class="friend-requests">
            <h2>Friend Requests</h2>
            <div class="friends-list">
                <?php while ($request = $friend_requests->fetch_assoc()): ?>
                    <div class="friend-card">
                        <img src="<?php echo get_profile_image($request['id']); ?>" alt="Profile" class="friend-image">
                        <div class="friend-info">
                            <h3><?php echo htmlspecialchars($request['username']); ?></h3>
                            <div class="friend-actions">
                                <form action="../actions/accept_friend.php" method="post" style="display: inline;">
                                    <input type="hidden" name="friendship_id" value="<?php echo $request['friendship_id']; ?>">
                                    <button type="submit" class="btn btn-primary">Accept</button>
                                </form>
                                <form action="../actions/reject_friend.php" method="post" style="display: inline;">
                                    <input type="hidden" name="friendship_id" value="<?php echo $request['friendship_id']; ?>">
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="friends-list">
        <h2>My Friends</h2>
        <?php if ($friends->num_rows > 0): ?>
            <?php while ($friend = $friends->fetch_assoc()): ?>
                <div class="friend-card">
                    <img src="<?php echo get_profile_image($friend['id']); ?>" alt="Profile" class="friend-image">
                    <div class="friend-info">
                        <h3><?php echo htmlspecialchars($friend['username']); ?></h3>
                        <a href="profile.php?id=<?php echo $friend['id']; ?>" class="btn btn-secondary">View Profile</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You don't have any friends yet. Start connecting with others!</p>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include dirname(__DIR__) . '/templates/footer.php';
?>
