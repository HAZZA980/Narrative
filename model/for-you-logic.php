<?php

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    die("User not logged in. Redirecting...");
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User ID not found in session.");
}

// Fetch user's preferred categories from user_preferences
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

//----------------------------------------------------------------------------------------------------------------------
// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Fetch blogs based on preferred categories or fetch latest blogs with pagination
$blogs_result = null;
if (!empty($preferred_categories)) {
    // Query blogs based on preferred categories with pagination
    $placeholders = implode(",", array_fill(0, count($preferred_categories), "?"));
    $query = "SELECT b.id, b.user_id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, b.category, b.Tags, b.Image, u.username AS Author 
          FROM tbl_blogs b
          JOIN users u ON b.user_id = u.user_id
          WHERE b.category IN ($placeholders) AND b.Private = 0
          ORDER BY b.datePublished DESC 
          LIMIT ? OFFSET ?";


    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing blogs query: " . $conn->error);
    }

    // Merge preferred categories and the pagination parameters
    $params = array_merge($preferred_categories, [$articles_per_page, $offset]);

    // Dynamically build the type string for bind_param()
    $type_str = str_repeat("s", count($preferred_categories)) . "ii";
    $stmt->bind_param($type_str, ...$params);

    $stmt->execute();
    $blogs_result = $stmt->get_result();

    if (!$blogs_result) {
        die("Error executing blogs query: " . $stmt->error);
    }

    $stmt->close();
} else {
    // If no preferred categories, fetch latest blogs with pagination
    $query = "SELECT b.id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, 
                     b.Category, b.Image, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Private = 0
              ORDER BY b.datePublished DESC 
              LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing blogs query: " . $conn->error);
    }

    $stmt->bind_param("ii", $articles_per_page, $offset);
    $stmt->execute();
    $blogs_result = $stmt->get_result();

    if (!$blogs_result) {
        die("Error fetching latest blogs: " . $conn->error);
    }

    $stmt->close();
}

// Count the total number of articles for pagination
$total_query = "SELECT COUNT(*) as total FROM tbl_blogs WHERE Private = 0";
if (!empty($preferred_categories)) {
    $total_query .= " AND Category IN (" . implode(",", array_fill(0, count($preferred_categories), "?")) . ")";
}
$total_stmt = $conn->prepare($total_query);

if (!empty($preferred_categories)) {
    $total_stmt->bind_param(str_repeat("s", count($preferred_categories)), ...$preferred_categories);
}

$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $articles_per_page);

$total_stmt->close();

// Query articles NOT in the user's preferred categories for the ASIDE bar
$non_recommended_result = null;
if (!empty($preferred_categories)) {
    $placeholders = implode(",", array_fill(0, count($preferred_categories), "?"));
    $query = "SELECT b.id, b.title, b.datePublished, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Category NOT IN ($placeholders) AND b.Private = 0
              ORDER BY b.datePublished DESC LIMIT 5";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing non-recommended query: " . $conn->error);
    }

    $stmt->bind_param(str_repeat("s", count($preferred_categories)), ...$preferred_categories);
    $stmt->execute();
    $non_recommended_result = $stmt->get_result();

    if (!$non_recommended_result) {
        die("Error fetching non-recommended blogs: " . $stmt->error);
    }

    $stmt->close();
} else {
    $query = "SELECT b.id, b.title, b.datePublished, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Private = 0
              ORDER BY b.datePublished DESC LIMIT 5";
    $non_recommended_result = $conn->query($query);

    if (!$non_recommended_result) {
        die("Error fetching non-recommended blogs: " . $conn->error);
    }
}

//----------------------------------------------------------------------------------------------------------------------
// Query topics (categories) NOT in the user's preferred categories for the ASIDE Bar
$non_recommended_topics_result = null;
if (!empty($preferred_categories)) {
    $placeholders = implode(",", array_fill(0, count($preferred_categories), "?"));
    $query = "SELECT DISTINCT Category FROM tbl_blogs WHERE Category NOT IN ($placeholders) AND Private = 0";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing non-recommended topics query: " . $conn->error);
    }

    $stmt->bind_param(str_repeat("s", count($preferred_categories)), ...$preferred_categories);
    $stmt->execute();
    $non_recommended_topics_result = $stmt->get_result();

    if (!$non_recommended_topics_result) {
        die("Error fetching non-recommended topics: " . $stmt->error);
    }

    $stmt->close();
} else {
    $query = "SELECT DISTINCT Category FROM tbl_blogs WHERE Private = 0";
    $non_recommended_topics_result = $conn->query($query);

    if (!$non_recommended_topics_result) {
        die("Error fetching non-recommended topics: " . $conn->error);
    }
}

// Mapping of categories to category file names
$category_map = [
    "Lifestyle" => "lifestyle.php",
    "Writing Craft" => "writing-craft.php",
    "Travel" => "travel.php",
    "Reviews" => "reviews.php",
    "History & Culture" => "history-and-culture.php",
    "Entertainment" => "entertainment.php",
    "Business" => "business.php",
    "Technology" => "technology.php",
    "Politics" => "politics.php",
    "Science" => "science.php",
    "Health & Fitness" => "health.php",
    "Sports" => "sports.php",
    "Food & Drink" => "food.php",
    "Gaming" => "gaming.php",
    "Philosophy" => "philosophy.php",
];

?>