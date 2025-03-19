<?php
// Script to create more demo accounts and add updates to existing ones
require_once 'src/includes/config.php';
require_once 'src/includes/db.php';
require_once 'src/includes/functions.php';

// Function to create a user
function create_user($conn, $username, $email, $password, $bio, $github_username = '') {
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "User $username already exists.<br>";
        $user = $result->fetch_assoc();
        return $user['id'];
    }
    
    // Create the user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, bio, github_username) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $hashed_password, $bio, $github_username);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        echo "Created user: $username (ID: $user_id)<br>";
        return $user_id;
    } else {
        echo "Error creating user $username: " . $conn->error . "<br>";
        return 0;
    }
}

// Function to create a post
function create_post($conn, $user_id, $title, $content, $status = 'planned', $privacy = 'public', $github_repo = '', $code_snippet = '', $image_path = '') {
    $stmt = $conn->prepare("
        INSERT INTO posts (
            user_id, title, content, status, privacy, github_repo, code_snippet, image_path, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("isssssss", $user_id, $title, $content, $status, $privacy, $github_repo, $code_snippet, $image_path);
    
    if ($stmt->execute()) {
        $post_id = $conn->insert_id;
        echo "Created post: $title (ID: $post_id)<br>";
        return $post_id;
    } else {
        echo "Error creating post $title: " . $conn->error . "<br>";
        return 0;
    }
}

// Function to create a thread update
function create_thread_update($conn, $user_id, $parent_id, $thread_type, $content, $status = 'in-progress', $github_repo = '', $code_snippet = '', $image_path = '') {
    // Get the parent post title
    $stmt = $conn->prepare("SELECT title FROM posts WHERE id = ?");
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $parent = $result->fetch_assoc();
    
    if (!$parent) {
        echo "Error: Parent post not found.<br>";
        return 0;
    }
    
    $title = $parent['title'] . ' - ' . ucfirst($thread_type);
    
    $stmt = $conn->prepare("
        INSERT INTO posts (
            user_id, parent_id, thread_type, title, content, status, github_repo, code_snippet, image_path, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iissssssss", $user_id, $parent_id, $thread_type, $title, $content, $status, $github_repo, $code_snippet, $image_path);
    
    if ($stmt->execute()) {
        $post_id = $conn->insert_id;
        echo "Created thread update for post $parent_id: $title (ID: $post_id)<br>";
        return $post_id;
    } else {
        echo "Error creating thread update: " . $conn->error . "<br>";
        return 0;
    }
}

// Function to send a friend request
function send_friend_request($conn, $user_id, $friend_id) {
    // Check if friendship already exists
    $stmt = $conn->prepare("
        SELECT id FROM friendships 
        WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
    ");
    $stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo "Friendship already exists between users $user_id and $friend_id.<br>";
        return false;
    }
    
    // Send friend request
    $stmt = $conn->prepare("INSERT INTO friendships (user_id, friend_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
    $stmt->bind_param("ii", $user_id, $friend_id);
    
    if ($stmt->execute()) {
        echo "Sent friend request from user $user_id to user $friend_id.<br>";
        return true;
    } else {
        echo "Error sending friend request: " . $conn->error . "<br>";
        return false;
    }
}

// Get the ID of the main user (orlanditopo)
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$username = 'orlanditopo';
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Main user 'orlanditopo' not found. Please ensure this account exists first.<br>");
}

$main_user = $result->fetch_assoc();
$main_user_id = $main_user['id'];
echo "Found main user: orlanditopo (ID: $main_user_id)<br><br>";

// 1. Create 10 new CEO accounts
echo "<h2>Creating New CEO Accounts</h2>";

$new_ceos = [
    [
        'username' => 'BillGates',
        'email' => 'bill@example.com',
        'bio' => 'Co-founder of Microsoft, philanthropist, and avid reader.',
        'github' => 'billgates'
    ],
    [
        'username' => 'SusanWojcicki',
        'email' => 'susan@example.com',
        'bio' => 'Former CEO of YouTube, tech executive and advocate for women in tech.',
        'github' => 'susanw'
    ],
    [
        'username' => 'MarkZuckerberg',
        'email' => 'mark@example.com',
        'bio' => 'CEO of Meta, founder of Facebook, and VR enthusiast.',
        'github' => 'zuck'
    ],
    [
        'username' => 'JeffBezos',
        'email' => 'jeff@example.com',
        'bio' => 'Founder of Amazon and Blue Origin, space exploration advocate.',
        'github' => 'jeffbezos'
    ],
    [
        'username' => 'SherylSandberg',
        'email' => 'sheryl@example.com',
        'bio' => 'Former COO of Facebook, author, and women\'s leadership advocate.',
        'github' => 'ssandberg'
    ],
    [
        'username' => 'ElonMusk',
        'email' => 'elon@example.com',
        'bio' => 'CEO of Tesla and SpaceX, founder of multiple companies.',
        'github' => 'elonmusk'
    ],
    [
        'username' => 'GinniRometty',
        'email' => 'ginni@example.com',
        'bio' => 'Former CEO of IBM, business leader and AI proponent.',
        'github' => 'grometty'
    ],
    [
        'username' => 'SteveWozniak',
        'email' => 'woz@example.com',
        'bio' => 'Co-founder of Apple, inventor, and philanthropist.',
        'github' => 'woz'
    ],
    [
        'username' => 'AndyJassy',
        'email' => 'andy@example.com',
        'bio' => 'CEO of Amazon, former head of AWS, cloud computing pioneer.',
        'github' => 'ajassy'
    ],
    [
        'username' => 'LisaSu',
        'email' => 'lisa@example.com',
        'bio' => 'CEO of AMD, semiconductor industry leader, and engineer.',
        'github' => 'lisasu'
    ],
];

$new_ceo_ids = [];
foreach ($new_ceos as $ceo) {
    $id = create_user(
        $conn, 
        $ceo['username'], 
        $ceo['email'], 
        'password123', 
        $ceo['bio'], 
        $ceo['github']
    );
    if ($id > 0) {
        $new_ceo_ids[] = $id;
    }
}

// 2. Create one goalie post for each new CEO
echo "<br><h2>Creating Goalie Posts for New CEOs</h2>";

$goalie_posts = [
    'Building a Raspberry Pi Smart Home Hub',
    'Learning to Play the Piano',
    'Creating a Personal Finance Tracking System',
    'Building a Backyard Observatory',
    'Writing a Non-Fiction Book',
    'Designing a Sustainable Garden',
    'Creating a Podcast Series',
    'Building a Custom Gaming PC',
    'Developing a Mobile App for Local Volunteering',
    'Training for a Marathon'
];

$new_post_ids = [];
foreach ($new_ceo_ids as $index => $ceo_id) {
    if (isset($goalie_posts[$index])) {
        $title = $goalie_posts[$index];
        $content = "This is my goal to $title. I'm excited to document my progress here and share my journey with the community. This goal is important to me because it will help me develop new skills and challenge myself in new ways.";
        
        $post_id = create_post(
            $conn,
            $ceo_id,
            $title,
            $content,
            'planned',
            'public'
        );
        
        if ($post_id > 0) {
            $new_post_ids[$ceo_id] = $post_id;
        }
    }
}

// 3. Have 5 of the new CEOs send friend requests to the main user
echo "<br><h2>Sending Friend Requests to Main User</h2>";

// Select 5 random CEOs from the new ones
$friend_requesters = array_slice($new_ceo_ids, 0, 5);
foreach ($friend_requesters as $ceo_id) {
    send_friend_request($conn, $ceo_id, $main_user_id);
}

// 4. Add updates to the original 4 CEO posts
echo "<br><h2>Adding Updates to Original CEO Posts</h2>";

// Get the original 4 CEOs
$stmt = $conn->prepare("
    SELECT u.id, u.username, p.id as post_id, p.title 
    FROM users u 
    JOIN posts p ON u.id = p.user_id
    WHERE u.username IN ('SatyaNadella', 'SundarPichai', 'TimCook', 'JensenHuang')
    AND p.parent_id IS NULL
");
$stmt->execute();
$original_ceos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($original_ceos as $ceo) {
    // Add first update - general progress
    $update_content = "I've started making progress on this goal. Initial planning is complete and I've gathered the necessary resources. Looking forward to diving deeper into the implementation phase.";
    create_thread_update($conn, $ceo['id'], $ceo['post_id'], 'update', $update_content);
    
    // Add second update - more specific milestone
    $milestone_content = "Reached a significant milestone today! Completed about 25% of the work and learned a lot in the process. Here's a brief summary of what I've accomplished so far and what challenges I've faced.";
    create_thread_update($conn, $ceo['id'], $ceo['post_id'], 'milestone', $milestone_content);
}

echo "<br><h2>All Done!</h2>";
echo "Created 10 new CEO accounts, each with a goal post.<br>";
echo "5 CEOs have sent friend requests to orlanditopo.<br>";
echo "Added 2 updates (1 update, 1 milestone) to each of the original 4 CEO goal posts.<br>";
echo "You can now log in with any of these accounts using the password 'password123'.<br>";
?> 