<?php
// Script to create more demo accounts and updates for MyGoalie

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

// Function to create a thread update for a post
function create_update($conn, $user_id, $parent_id, $thread_type, $content, $github_repo = '', $code_snippet = '', $image_path = '') {
    // Get parent post information
    $stmt = $conn->prepare("SELECT title, status FROM posts WHERE id = ?");
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $parent = $stmt->get_result()->fetch_assoc();
    
    if (!$parent) {
        echo "Parent post not found for ID: $parent_id<br>";
        return 0;
    }
    
    // Create update title based on parent title and thread type
    $title = $parent['title'] . ' - ' . ucfirst($thread_type);
    $status = $parent['status']; // Use parent's status
    
    $stmt = $conn->prepare("
        INSERT INTO posts (
            user_id, parent_id, thread_type, title, content, status, 
            github_repo, code_snippet, image_path, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param(
        "iisssssss", 
        $user_id, 
        $parent_id, 
        $thread_type, 
        $title, 
        $content, 
        $status, 
        $github_repo, 
        $code_snippet, 
        $image_path
    );
    
    if ($stmt->execute()) {
        $update_id = $conn->insert_id;
        echo "Created thread update: $title (ID: $update_id)<br>";
        return $update_id;
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

// Find existing CEO accounts to add updates to
echo "<h2>Finding Existing CEO Accounts</h2>";

$ceo_accounts = [
    'SatyaNadella' => 0,
    'SundarPichai' => 0,
    'TimCook' => 0,
    'JensenHuang' => 0
];

foreach ($ceo_accounts as $username => &$user_id) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        echo "Found user: $username (ID: $user_id)<br>";
    } else {
        echo "User $username not found.<br>";
    }
}

// Get posts for each CEO
$ceo_posts = [];
foreach ($ceo_accounts as $username => $user_id) {
    if ($user_id > 0) {
        $stmt = $conn->prepare("
            SELECT id FROM posts 
            WHERE user_id = ? AND parent_id IS NULL
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $post = $result->fetch_assoc();
            $ceo_posts[$username] = $post['id'];
            echo "Found post ID {$post['id']} for $username<br>";
        }
    }
}

// Add 2 updates to each CEO post
echo "<h2>Adding Updates to CEO Posts</h2>";

// Updates for Satya Nadella's wooden table project
if (isset($ceo_posts['SatyaNadella']) && $ceo_posts['SatyaNadella'] > 0) {
    $post_id = $ceo_posts['SatyaNadella'];
    $user_id = $ceo_accounts['SatyaNadella'];
    
    create_update(
        $conn,
        $user_id,
        $post_id,
        'update',
        "I've sourced some beautiful oak slabs for my dining table project! Found a local supplier who had exactly what I was looking for - live edge oak with excellent grain patterns. The slabs are about 3\" thick and have a stunning natural edge that will be perfect for the table. Next step is preparing the wood and finalizing the design for the metal legs.",
        '',
        '',
        'uploads/table_progress1.jpg'
    );
    
    create_update(
        $conn,
        $user_id,
        $post_id,
        'milestone',
        "Design phase complete! After sketching several options, I've decided on a design that balances aesthetics and stability. The table will feature two black metal trapezoid legs with a minimalist look to contrast with the natural wood top. I've ordered the metal legs from a fabricator and started sanding the oak slabs. The grain pattern is even more beautiful than I initially thought!",
        '',
        '',
        'uploads/table_progress2.jpg'
    );
}

// Updates for Sundar Pichai's resume portfolio
if (isset($ceo_posts['SundarPichai']) && $ceo_posts['SundarPichai'] > 0) {
    $post_id = $ceo_posts['SundarPichai'];
    $user_id = $ceo_accounts['SundarPichai'];
    
    create_update(
        $conn,
        $user_id,
        $post_id,
        'update',
        "Started working on the HTML/CSS structure for my portfolio! I've created a responsive layout that works well on both desktop and mobile. The navigation is clean and intuitive, making it easy for potential employers to find what they're looking for. Next step is to add the projects section with interactive elements.",
        'sundarpichai/portfolio',
        'html, body {\n  font-family: \'Roboto\', sans-serif;\n  margin: 0;\n  padding: 0;\n  color: #333;\n  background-color: #f8f9fa;\n}\n\n.container {\n  max-width: 1200px;\n  margin: 0 auto;\n  padding: 2rem;\n}\n\n.nav {\n  display: flex;\n  justify-content: space-between;\n  padding: 1rem 0;\n  border-bottom: 1px solid #e9ecef;\n}',
        'uploads/portfolio_progress1.jpg'
    );
    
    create_update(
        $conn,
        $user_id,
        $post_id,
        'commit',
        "Added the first project showcase to my portfolio! I've implemented a card-based layout for displaying projects with screenshots, descriptions, and links to live demos or repositories. Each project card has hover effects and animations to make the interface more engaging. I'm really happy with how the design is coming together.",
        'sundarpichai/portfolio',
        'function createProjectCard(project) {\n  const card = document.createElement(\'div\');\n  card.className = \'project-card\';\n  \n  const image = document.createElement(\'img\');\n  image.src = project.image;\n  image.alt = project.title;\n  \n  const content = document.createElement(\'div\');\n  content.className = \'card-content\';\n  \n  const title = document.createElement(\'h3\');\n  title.textContent = project.title;\n  \n  const desc = document.createElement(\'p\');\n  desc.textContent = project.description;\n  \n  const links = document.createElement(\'div\');\n  links.className = \'card-links\';\n  \n  // Add links to demo or repo\n  \n  card.appendChild(image);\n  content.appendChild(title);\n  content.appendChild(desc);\n  content.appendChild(links);\n  card.appendChild(content);\n  \n  return card;\n}',
        'uploads/portfolio_progress2.jpg'
    );
}

// Updates for Tim Cook's fitness app
if (isset($ceo_posts['TimCook']) && $ceo_posts['TimCook'] > 0) {
    $post_id = $ceo_posts['TimCook'];
    $user_id = $ceo_accounts['TimCook'];
    
    create_update(
        $conn,
        $user_id,
        $post_id,
        'update',
        "Completed the initial wireframes for the fitness app! I've mapped out the main screens: activity tracking, nutrition logging, goal setting, and progress visualization. The focus on privacy is built into the design with clear user consent points and data control options. I'm excited about the clean, intuitive interface that emphasizes ease of use without compromising on functionality.",
        'timcook/fitnessapp',
        '',
        'uploads/fitness_progress1.jpg'
    );
    
    create_update(
        $conn,
        $user_id,
        $post_id,
        'commit',
        "Started implementing the core data structures for the fitness app! I've created the basic models for user profiles, activity tracking, and nutrition logging. The privacy manager class has been expanded to include more granular controls for data retention and sharing preferences. Next step is to work on the UI implementation based on the wireframes.",
        'timcook/fitnessapp',
        'class ActivityTracker {\n  constructor(user) {\n    this.user = user;\n    this.activities = [];\n    this.goals = {};\n    this.privacySettings = new PrivacyManager();\n  }\n  \n  logActivity(type, duration, calories, details = {}) {\n    const activity = {\n      id: this.generateId(),\n      type,\n      duration,\n      calories,\n      timestamp: new Date(),\n      details,\n      syncStatus: \'local\'\n    };\n    \n    this.activities.push(activity);\n    this.checkGoals(activity);\n    return activity;\n  }\n  \n  generateId() {\n    // Create unique ID for activity\n    return Date.now().toString(36) + Math.random().toString(36).substr(2);\n  }\n}',
        'uploads/fitness_progress2.jpg'
    );
}

// Updates for Jensen Huang's platform game
if (isset($ceo_posts['JensenHuang']) && $ceo_posts['JensenHuang'] > 0) {
    $post_id = $ceo_posts['JensenHuang'];
    $user_id = $ceo_accounts['JensenHuang'];
    
    create_update(
        $conn,
        $user_id,
        $post_id,
        'update',
        "Made significant progress on the platformer game! I've implemented the basic character controller with smooth movement, jumping, and wall detection. The character feels responsive and satisfying to control, which is crucial for a platformer. I've also started designing the first level with some placeholder graphics.",
        'jensenhuang/platformer',
        '',
        'uploads/game_progress1.jpg'
    );
    
    create_update(
        $conn,
        $user_id,
        $post_id,
        'commit',
        "Added enemy AI and basic combat mechanics to the platformer! The enemies have simple patrol patterns and will chase the player when detected. I've implemented a basic health system and the ability to defeat enemies by jumping on them (classic Mario style). Next up is adding power-ups and special abilities to make the gameplay more interesting.",
        'jensenhuang/platformer',
        'public class EnemyController : MonoBehaviour {\n    public float moveSpeed = 3f;\n    public float chaseSpeed = 5f;\n    public float detectionRange = 5f;\n    public LayerMask playerLayer;\n    \n    private Rigidbody2D rb;\n    private Transform player;\n    private bool isFacingRight = true;\n    private Vector2 patrolPoints;\n    private Vector2 currentPoint;\n    private bool isChasing = false;\n    \n    void Start() {\n        rb = GetComponent<Rigidbody2D>();\n        player = GameObject.FindGameObjectWithTag("Player").transform;\n        \n        // Set up patrol points\n        patrolPoints = new Vector2(transform.position.x - 3f, transform.position.x + 3f);\n        currentPoint = new Vector2(patrolPoints.y, transform.position.y);\n    }\n    \n    void Update() {\n        // Check if player is in detection range\n        float distToPlayer = Vector2.Distance(transform.position, player.position);\n        isChasing = distToPlayer < detectionRange;\n        \n        if (isChasing) {\n            ChasePlayer();\n        } else {\n            Patrol();\n        }\n    }\n}',
        'uploads/game_progress2.jpg'
    );
}

// Create 10 more demo accounts with tech industry figures
echo "<h2>Creating 10 More Demo Accounts</h2>";

$new_accounts = [
    [
        'username' => 'ElonMusk',
        'email' => 'elon@example.com',
        'bio' => 'CEO of Tesla and SpaceX, working on sustainable energy and space exploration.',
        'github' => 'elonmusk',
        'post' => [
            'title' => 'Building a Hydroponic Garden System',
            'content' => "I'm working on creating a fully automated hydroponic garden system for my home. The goal is to grow fresh vegetables year-round with minimal maintenance using automated water circulation, lighting, and nutrient delivery. I plan to build a multi-tiered system that can grow leafy greens, herbs, and some fruiting plants.\n\nPhases of the project:\n1. Design the overall structure and water circulation system\n2. Set up the lighting and environmental controls\n3. Implement automation with sensors and microcontrollers\n4. Build a mobile app to monitor and control the system remotely\n\nI'm particularly excited about implementing computer vision to monitor plant health and growth rates.",
            'status' => 'planned',
            'privacy' => 'public',
            'github_repo' => 'elonmusk/hydroponics',
            'send_request' => true
        ]
    ],
    [
        'username' => 'SherylSandberg',
        'email' => 'sheryl@example.com',
        'bio' => 'Business executive and former COO of Meta Platforms.',
        'github' => 'sherylsandberg',
        'post' => [
            'title' => 'Writing a Book on Women in Leadership',
            'content' => "I'm embarking on writing a new book focused on emerging leadership strategies for women in technology and business. The book will combine personal experiences, research findings, and actionable advice for navigating career advancement.\n\nThe book will cover:\n- Negotiation strategies specific to women in tech\n- Building effective professional networks\n- Overcoming impostor syndrome and bias\n- Balancing leadership roles with personal responsibilities\n- Mentorship and sponsorship dynamics\n\nI'll be interviewing women leaders across various industries to provide diverse perspectives and experiences.",
            'status' => 'in-progress',
            'privacy' => 'public',
            'github_repo' => '',
            'send_request' => true
        ]
    ],
    [
        'username' => 'JeffBezos',
        'email' => 'jeff@example.com',
        'bio' => 'Founder of Amazon and Blue Origin, focused on space technology and innovation.',
        'github' => 'jeffbezos',
        'post' => [
            'title' => 'Designing a Personal Weather Station Network',
            'content' => "I'm building a network of personal weather stations for my properties to collect hyperlocal climate data. The goal is to have precise, real-time weather information specific to each location rather than relying on general forecasts.\n\nComponents of the project:\n1. Custom weather station hardware (temperature, humidity, pressure, rainfall, wind sensors)\n2. Solar power and battery backup systems\n3. Low-power wireless communication network\n4. Central server for data collection and analysis\n5. Visualization dashboard and predictive modeling\n\nI'm particularly interested in using this data to optimize energy usage and landscape management across properties.",
            'status' => 'planned',
            'privacy' => 'friends',
            'github_repo' => 'jeffbezos/weathernet',
            'send_request' => false
        ]
    ],
    [
        'username' => 'MarkZuckerberg',
        'email' => 'mark@example.com',
        'bio' => 'CEO of Meta, working on social technology and virtual reality.',
        'github' => 'zuck',
        'post' => [
            'title' => 'Building a Smart Home Automation Hub',
            'content' => "I'm developing a centralized smart home system that integrates various protocols (Zigbee, Z-Wave, Matter) into a single, privacy-focused hub. Unlike commercial solutions, this system will keep all processing and data local, only connecting to external services when explicitly requested.\n\nFeatures planned:\n- Protocol translation layer for device interoperability\n- Local voice recognition for commands\n- Machine learning for occupancy prediction and energy optimization\n- Custom dashboard for monitoring and control\n- Robust API for extending functionality\n\nThe goal is to create a system that provides convenience without compromising on privacy or requiring constant internet connectivity.",
            'status' => 'in-progress',
            'privacy' => 'public',
            'github_repo' => 'zuck/smarthomehub',
            'code_snippet' => 'class DeviceManager {\n  constructor() {\n    this.devices = {};\n    this.protocols = new Map();\n    this.eventBus = new EventEmitter();\n  }\n  \n  registerProtocol(name, handler) {\n    this.protocols.set(name, handler);\n    console.log(`Registered protocol: ${name}`);\n  }\n  \n  async discoverDevices() {\n    const discoveries = [];\n    \n    for (const [name, handler] of this.protocols.entries()) {\n      console.log(`Discovering devices using ${name} protocol...`);\n      const found = await handler.discover();\n      discoveries.push(...found);\n    }\n    \n    return discoveries;\n  }\n}',
            'send_request' => false
        ]
    ],
    [
        'username' => 'SusanWojcicki',
        'email' => 'susan@example.com',
        'bio' => 'Former CEO of YouTube and early Google employee.',
        'github' => 'susanw',
        'post' => [
            'title' => 'Creating a Digital Family Cookbook',
            'content' => "I'm working on a project to digitize and modernize my family's cookbook of recipes passed down through generations. Beyond just recipe collection, I want to create an interactive platform that combines traditional recipes with modern cooking techniques, ingredient substitutions, and nutritional information.\n\nFeatures planned:\n- Beautiful visual layout with photos of each dish\n- Recipe scaling functionality for different serving sizes\n- Automatic grocery list generation\n- Recipe modification tracking to see how dishes evolve over time\n- Family stories and memories associated with special recipes\n\nI'm using web technologies to create an experience that works well on both desktop and mobile devices, allowing easy access while cooking.",
            'status' => 'planned',
            'privacy' => 'friends',
            'github_repo' => 'susanw/family-cookbook',
            'send_request' => true
        ]
    ],
    [
        'username' => 'BillGates',
        'email' => 'bill@example.com',
        'bio' => 'Co-founder of Microsoft and philanthropist focused on global health and climate change.',
        'github' => 'billgates',
        'post' => [
            'title' => 'Building a Personal Library Management System',
            'content' => "I'm creating a comprehensive library management system for my personal collection of books. With thousands of volumes spread across multiple locations, I need a better way to catalog, search, and track my books.\n\nComponents of the system:\n1. Database design for storing book information, locations, lending status\n2. Barcode/ISBN scanning capability for easy data entry\n3. Integration with online book APIs for metadata population\n4. Reading history and notes tracking\n5. Recommendation engine based on reading patterns\n\nThe system will feature both a web interface and mobile app for accessing the library from anywhere. I'm particularly interested in implementing NLP for improved search capabilities across book contents and notes.",
            'status' => 'in-progress',
            'privacy' => 'public',
            'github_repo' => 'billgates/library-system',
            'code_snippet' => 'class Book {\n  constructor(data) {\n    this.isbn = data.isbn;\n    this.title = data.title;\n    this.authors = data.authors || [];\n    this.publishedDate = data.publishedDate;\n    this.pageCount = data.pageCount;\n    this.categories = data.categories || [];\n    this.language = data.language;\n    this.location = data.location;\n    this.notes = data.notes || [];\n    this.readStatus = data.readStatus || \'unread\';\n    this.acquisitionDate = data.acquisitionDate;\n  }\n  \n  getDisplayTitle() {\n    return `${this.title} by ${this.authors.join(\', \')}`;\n  }\n}',
            'send_request' => true
        ]
    ],
    [
        'username' => 'GinniRometty',
        'email' => 'ginni@example.com',
        'bio' => 'Former CEO and Chairman of IBM, focused on AI and cloud computing.',
        'github' => 'grometty',
        'post' => [
            'title' => 'Developing a Personal Finance Dashboard',
            'content' => "I'm building a comprehensive personal finance dashboard that aggregates data from multiple accounts and provides visualizations and insights about spending patterns, investment performance, and financial goals.\n\nKey features planned:\n- Secure integration with banking and investment APIs\n- Customizable categorization of transactions\n- Goal setting and tracking with projections\n- Tax optimization suggestions\n- Retirement planning scenarios\n\nUnlike most financial apps, this will focus on long-term planning and wealth building rather than just day-to-day budgeting. I'm implementing strong encryption and security measures to ensure sensitive financial data remains protected.",
            'status' => 'planned',
            'privacy' => 'private',
            'github_repo' => 'grometty/finance-dashboard',
            'send_request' => true
        ]
    ],
    [
        'username' => 'SteveWozniak',
        'email' => 'woz@example.com',
        'bio' => 'Co-founder of Apple and inventor, passionate about engineering and education.',
        'github' => 'thewoz',
        'post' => [
            'title' => 'Building a Retro Gaming Console',
            'content' => "I'm constructing a custom retro gaming console using modern hardware but with authentic vintage gaming experiences. The goal is to combine the best of old-school gaming with modern conveniences.\n\nComponents of the project:\n1. Custom hardware based on Raspberry Pi with specialized controllers\n2. FPGA implementation for accurate hardware emulation of classic systems\n3. Custom OS optimized for gaming performance and authenticity\n4. Physical design inspired by classic consoles but with modern improvements\n5. Game library organization system with metadata\n\nI'm particularly focusing on authentic controller feel and video output options that recreate the look of CRT displays while working on modern TVs. The system will support multiple classic platforms from the 70s through the 90s.",
            'status' => 'in-progress',
            'privacy' => 'public',
            'github_repo' => 'thewoz/retroconsole',
            'send_request' => false
        ]
    ],
    [
        'username' => 'AndyJassy',
        'email' => 'andy@example.com',
        'bio' => 'CEO of Amazon, previously led Amazon Web Services (AWS).',
        'github' => 'ajassy',
        'post' => [
            'title' => 'Creating a Smart Kitchen Inventory System',
            'content' => "I'm developing an automated kitchen inventory system that tracks food items, suggests recipes based on available ingredients, and helps reduce food waste.\n\nComponents of the system:\n1. Smart containers with weight sensors to track consumption\n2. Barcode scanning for easy item entry\n3. Expiration date tracking with notifications\n4. Recipe recommendation engine\n5. Automated shopping list generation\n\nThe system will learn from usage patterns to predict when items need to be replenished and optimize shopping frequency. I'm particularly interested in implementing computer vision to identify non-barcoded items like produce.",
            'status' => 'planned',
            'privacy' => 'friends',
            'github_repo' => 'ajassy/kitchen-inventory',
            'send_request' => false
        ]
    ],
    [
        'username' => 'LisaSu',
        'email' => 'lisa@example.com',
        'bio' => 'CEO of AMD, leading semiconductor design and innovation.',
        'github' => 'lisasu',
        'post' => [
            'title' => 'Building a Custom Mechanical Keyboard',
            'content' => "I'm designing and building a custom mechanical keyboard tailored to my specific preferences and workflow. I'm aiming to create a keyboard that combines aesthetic appeal with maximum typing efficiency and comfort.\n\nComponents of the project:\n1. Custom PCB design with QMK firmware support\n2. Machined aluminum case with brass weight\n3. Custom switch selection and modification (lubing and filming)\n4. Sound dampening with multiple layers of foam\n5. Custom keycap set design\n\nThe layout will be a 75% form factor with dedicated macro keys for programming and specialized workflows. I'm particularly focused on achieving the perfect sound profile through careful material selection and assembly techniques.",
            'status' => 'in-progress',
            'privacy' => 'public',
            'github_repo' => 'lisasu/custom-keyboard',
            'send_request' => false
        ]
    ]
];

// Create the accounts and posts, send friend requests
foreach ($new_accounts as $account) {
    $user_id = create_user(
        $conn, 
        $account['username'], 
        $account['email'], 
        'password123', 
        $account['bio'], 
        $account['github']
    );
    
    if ($user_id > 0) {
        $post_id = create_post(
            $conn,
            $user_id,
            $account['post']['title'],
            $account['post']['content'],
            $account['post']['status'],
            $account['post']['privacy'],
            $account['post']['github_repo'],
            $account['post']['code_snippet'] ?? '',
            ''  // No image for simplicity
        );
        
        // Send friend request if needed
        if ($account['post']['send_request']) {
            send_friend_request($conn, $user_id, $main_user_id);
        }
    }
}

echo "<br><h2>All Done!</h2>";
echo "Created 10 new demo accounts, each with a goalie post.<br>";
echo "5 of these accounts have sent friend requests to orlanditopo.<br>";
echo "Added 2 updates to each of the 4 original CEO accounts' goals.<br>";
echo "You can now log in with any of these accounts using the password 'password123'.<br>";
?> 