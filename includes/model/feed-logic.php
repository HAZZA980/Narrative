<?php


$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Get logged-in user's ID if available

if (isset($_GET['username'])) {
    $username = htmlspecialchars($_GET['username']);
    // Use $username to fetch the user's profile or feed data.
}

// Get the username from the URL
$username = $_GET['username'] ?? null;
if (!$username) {
    die("Username not provided.");
}

// Verify if the user exists
$user_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
if (!$user_stmt) {
    die("Error preparing user query: " . $conn->error);
}
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    die("User not found.");
}

$user_data = $user_result->fetch_assoc();
$user_id = $user_data['user_id'];

// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Fetch blogs written by the user with pagination
$query = "SELECT b.user_id, b.id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, b.Tags, b.Image 
          FROM tbl_blogs b
          WHERE b.user_id = ? AND b.Private = 0
          ORDER BY b.datePublished DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error preparing blogs query: " . $conn->error);
}

$stmt->bind_param("iii", $user_id, $articles_per_page, $offset);
$stmt->execute();
$blogs_result = $stmt->get_result();

if (!$blogs_result) {
    die("Error executing blogs query: " . $stmt->error);
}

// Count the total number of articles for pagination
$total_query = "SELECT COUNT(*) as total FROM tbl_blogs WHERE user_id = ? AND Private = 0";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $articles_per_page);


//--------------------------------------------------------------------------------------------------


// Query articles NOT in the user's preferred tags for the ASIDE bar
$non_recommended_result = null;
if (!empty($preferred_tags)) {
    $placeholders = implode(",", array_fill(0, count($preferred_tags), "?"));
    $query = "SELECT b.id, b.title, b.datePublished, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Tags NOT IN ($placeholders) 
              AND b.Private = 0 
              AND u.username != ? 
              ORDER BY b.datePublished DESC LIMIT 5";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing query: " . $conn->error);
    }

    // Dynamically bind parameters
    $params = array_merge($preferred_tags, [$_GET['username']]); // Add the username passed in the URL
    $type_str = str_repeat("s", count($preferred_tags)) . "s"; // Add an "s" for the username
    $stmt->bind_param($type_str, ...$params);

    $stmt->execute();
    $non_recommended_result = $stmt->get_result();

    if (!$non_recommended_result) {
        die("Error fetching non-recommended blogs: " . $stmt->error);
    }
} else {
    // If no preferred tags, fetch articles not authored by the username
    $query = "SELECT b.id, b.title, b.datePublished, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Private = 0 
              AND u.username != ? 
              ORDER BY b.datePublished DESC LIMIT 5";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing query: " . $conn->error);
    }

    $username = $_GET['username'];
    $stmt->bind_param("s", $username);

    $stmt->execute();
    $non_recommended_result = $stmt->get_result();

    if (!$non_recommended_result) {
        die("Error fetching non-recommended blogs: " . $stmt->error);
    }
}

//----------------------------------------------------------------------------------------------------------------------
// Query topics (tags) NOT in the user's preferred tags for the ASIDE Bar
$non_recommended_topics_result = null;
if (!empty($preferred_tags)) {
    $placeholders = implode(",", array_fill(0, count($preferred_tags), "?"));
    $query = "SELECT DISTINCT Tags FROM tbl_blogs WHERE Tags NOT IN ($placeholders) AND Private = 0";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing non-recommended topics query: " . $conn->error);
    }

    $stmt->bind_param(str_repeat("s", count($preferred_tags)), ...$preferred_tags);
    $stmt->execute();
    $non_recommended_topics_result = $stmt->get_result();

    if (!$non_recommended_topics_result) {
        die("Error fetching non-recommended topics: " . $stmt->error);
    }
} else {
    $query = "SELECT DISTINCT Tags FROM tbl_blogs WHERE Private = 0";
    $non_recommended_topics_result = $conn->query($query);

    if (!$non_recommended_topics_result) {
        die("Error fetching non-recommended topics: " . $conn->error);
    }
}


// Fetch the user_id corresponding to the username
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows == 0) {
    die("User not found.");
}

// Prepare and execute the query
$stmt1 = $conn->prepare("SELECT user_id, profile_picture, bio FROM user_details WHERE user_id = ?");
if (!$stmt1) {
    die("Error preparing statement: " . $conn->error);
}
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$result = $stmt1->get_result();

// Fetch user details
$user = $result->fetch_assoc();
$user_id = $user['user_id'] ?? null;
$profilePic = $user['profile_picture'] ?? null;  // Default to null if not found
$bio = $user['bio'] ?? null; // Default to null if not found

$stmt->close();

// Fetch user's preferences from user_preferences
$stmt = $conn->prepare("SELECT DISTINCT tag FROM user_preferences WHERE user_id = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$preferred_categories = [];
while ($row = $result->fetch_assoc()) {
    $preferred_categories[] = $row['tag']; // Assuming 'tag' holds category names
}

$stmt->close();

// If no preferences found, set a default message
if (empty($preferred_categories)) {
    $preferred_categories[] = 'No preferences provided';
}
?>