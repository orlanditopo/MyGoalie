<?php
// Script to create demo accounts and posts for MyGoalie

// Include necessary files
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
function create_post($conn, $user_id, $title, $content, $status = 'planned', $github_repo = '', $code_snippet = '', $image_path = '') {
    $stmt = $conn->prepare("
        INSERT INTO posts (
            user_id, title, content, status, github_repo, code_snippet, image_path, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("issssss", $user_id, $title, $content, $status, $github_repo, $code_snippet, $image_path);
    
    if ($stmt->execute()) {
        $post_id = $conn->insert_id;
        echo "Created post: $title (ID: $post_id)<br>";
        return $post_id;
    } else {
        echo "Error creating post $title: " . $conn->error . "<br>";
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

// Create tech CEO accounts
echo "<h2>Creating CEO Accounts</h2>";

$satya_id = create_user(
    $conn, 
    'SatyaNadella', 
    'satya@example.com', 
    'password123', 
    'CEO of Microsoft, passionate about cloud computing and woodworking.', 
    'satyanadella'
);

$sundar_id = create_user(
    $conn, 
    'SundarPichai', 
    'sundar@example.com', 
    'password123', 
    'CEO of Google and Alphabet, interested in AI and engineering.', 
    'sundarpichai'
);

$tim_id = create_user(
    $conn, 
    'TimCook', 
    'tim@example.com', 
    'password123', 
    'CEO of Apple, fitness enthusiast and privacy advocate.', 
    'timcook'
);

$jensen_id = create_user(
    $conn, 
    'JensenHuang', 
    'jensen@example.com', 
    'password123', 
    'CEO of NVIDIA, AI enthusiast and game development hobbyist.', 
    'jensenhuang'
);

echo "<br><h2>Creating Goal Posts</h2>";

// Create posts for each CEO
if ($satya_id > 0) {
    create_post(
        $conn,
        $satya_id,
        'Building a Wooden Dining Table',
        "I'm working on building a solid oak dining table for my home. The table will be 72\" x 42\" with a live edge and metal legs. This is my first major woodworking project, and I'm excited to document the process here.\n\nMy plan is to:\n1. Source high-quality oak slabs\n2. Design the table layout\n3. Sand and prepare the wood\n4. Apply finish\n5. Attach the legs\n\nI'll post updates as I make progress. Any tips from experienced woodworkers would be appreciated!",
        'in-progress',
        '',
        '',
        'uploads/wooden_table.jpg'
    );
}

if ($sundar_id > 0) {
    create_post(
        $conn,
        $sundar_id,
        'Creating an Engineering Resume Portfolio',
        "I'm building a comprehensive engineering resume and portfolio to highlight my technical skills. I want to create something that stands out from traditional resumes and showcases my projects effectively.\n\nThe portfolio will include:\n- Interactive project demonstrations\n- Code samples with explanations\n- Technical blog posts\n- Skills assessment\n- Professional achievements\n\nI plan to use HTML/CSS/JavaScript to create an interactive web-based portfolio that potential employers can explore.",
        'planned',
        'sundarpichai/portfolio',
        '<!DOCTYPE html>\n<html>\n<head>\n  <title>Engineering Portfolio</title>\n</head>\n<body>\n  <header>\n    <h1>Engineering Skills & Projects</h1>\n  </header>\n  <main>\n    <!-- Project showcases will go here -->\n  </main>\n</body>\n</html>',
        'uploads/resume.jpg'
    );
}

if ($tim_id > 0) {
    create_post(
        $conn,
        $tim_id,
        'Developing a Fitness Tracking Mobile App',
        "I'm developing a fitness tracking app focused on privacy and seamless integration with wearable devices. The app will provide insights without being intrusive, and will emphasize data ownership by users.\n\nFeatures planned:\n- Activity tracking (steps, workouts, sleep)\n- Nutrition logging\n- Goal setting and progress visualization\n- Privacy-focused data management\n- Local data storage options\n\nI'm starting with wireframes and user flow diagrams before moving on to actual development.",
        'planned',
        'timcook/fitnessapp',
        '// Sample code for data privacy mechanism\nclass PrivacyManager {\n  constructor() {\n    this.userConsent = false;\n    this.dataRetention = 30; // days\n  }\n  \n  setUserConsent(hasConsented) {\n    this.userConsent = hasConsented;\n    return this.userConsent;\n  }\n  \n  eraseExpiredData() {\n    // Implementation details\n  }\n}',
        'uploads/fitness_app.jpg'
    );
}

if ($jensen_id > 0) {
    create_post(
        $conn,
        $jensen_id,
        'Creating a 2D Mario-Style Platform Game',
        "I'm working on building a 2D platformer game inspired by classic Mario games but with modern graphics and physics. This is a passion project to understand game development better.\n\nPlanned features:\n- Character with smooth platforming mechanics\n- Level editor for creating custom challenges\n- Procedurally generated elements\n- Physics-based puzzles\n- Modern visual effects while maintaining retro feel\n\nI'm using Unity for development and will be sharing progress screenshots and playable demos.",
        'in-progress',
        'jensenhuang/platformer',
        'using UnityEngine;\n\npublic class PlayerController : MonoBehaviour {\n    public float moveSpeed = 5f;\n    public float jumpForce = 10f;\n    private Rigidbody2D rb;\n    private bool isGrounded = false;\n    \n    void Start() {\n        rb = GetComponent<Rigidbody2D>();\n    }\n    \n    void Update() {\n        // Handle input and movement\n        float moveDirection = Input.GetAxis("Horizontal");\n        rb.velocity = new Vector2(moveDirection * moveSpeed, rb.velocity.y);\n        \n        if (Input.GetButtonDown("Jump") && isGrounded) {\n            rb.velocity = new Vector2(rb.velocity.x, jumpForce);\n        }\n    }\n}',
        'uploads/platform_game.jpg'
    );
}

echo "<br><h2>Sending Friend Requests</h2>";

// Send friend requests to the main user from Satya and Tim
if ($satya_id > 0) {
    send_friend_request($conn, $satya_id, $main_user_id);
}

if ($tim_id > 0) {
    send_friend_request($conn, $tim_id, $main_user_id);
}

echo "<br><h2>All Done!</h2>";
echo "Created 4 tech CEO accounts, each with a unique goal post.<br>";
echo "Sent friend requests from Satya Nadella and Tim Cook to orlanditopo.<br>";
echo "You can now log in with any of these accounts using the password 'password123'.<br>";
?> 