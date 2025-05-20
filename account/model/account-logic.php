<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and check login status
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
error_log("User not logged in. Redirecting to login page.");
header("Location: " . BASE_URL . "user_auth.php");
exit;
}

// Debug session variables
error_log("Session Data: " . print_r($_SESSION, true));

// Get user details from the database
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id === null) {
error_log("Error: user_id is not set in the session.");
echo "<p>Error: Unable to fetch user details. Please try again.</p>";
exit;
}

$query = "SELECT username, created_at FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
error_log("Error preparing user details query: " . $conn->error);
exit;
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
error_log("Error executing user details query: " . $stmt->error);
exit;
}

$result = $stmt->get_result();
if ($result->num_rows > 0) {
$user = $result->fetch_assoc();
} else {
error_log("No user found for user_id: $user_id");
echo "<p>Error fetching user details.</p>";
exit;
}

// Debug fetched user data
error_log("Fetched User Data: " . print_r($user, true));
// Query to count articles by category (Tags) for the current user
$query = "SELECT Category, COUNT(*) AS article_count
FROM tbl_blogs
WHERE user_id = ?
GROUP BY Category"; // Assuming 'user_id' is the correct column
$stmt = $conn->prepare($query);
if (!$stmt) {
error_log("Error preparing articles query: " . $conn->error);
exit;
}

// Bind the user_id as the parameter
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
error_log("Error executing articles query: " . $stmt->error);
exit;
}

$result = $stmt->get_result();

$article_counts = [];
if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
$article_counts[] = $row;
}
}






// Count articles written in the last 30 days
$query = "SELECT COUNT(*) AS count FROM tbl_blogs WHERE user_id = ? AND DatePublished >= NOW() - INTERVAL 30 DAY";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$articles_last_30_days = $result->fetch_assoc()['count'] ?? 0;

// Count total articles written
$query = "SELECT COUNT(*) AS count FROM tbl_blogs WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_articles = $result->fetch_assoc()['count'] ?? 0;

// Count comments in the last 30 days
$query = "SELECT COUNT(*) AS count FROM article_comments WHERE user_id = ? AND commented_at >= NOW() - INTERVAL 30 DAY";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$comments_last_30_days = $result->fetch_assoc()['count'] ?? 0;

// Count total comments
$query = "SELECT COUNT(*) AS count FROM article_comments WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_comments = $result->fetch_assoc()['count'] ?? 0;






$badges = [
'newbie_writer' => false,
'prolific_writer' => false,
'feedback_guru' => false,
'social_butterfly' => false,
'trendsetter' => false,
'deep_thinker' => false,
'early_bird' => false,
'top_contributor' => false,
'loyal_reader' => false,
'comment_extra' => false,
'master_commenter' => false,
'bookmark_collector' => false,
'consistent_contributor' => false,
];

// Newbie Writer
$query = "SELECT COUNT(*) AS article_count FROM tbl_blogs WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()['article_count'] > 0) {
$badges['newbie_writer'] = true;
}

// Fetch the article count once and store it in a variable
$article_data = $result->fetch_assoc();

// Check for Prolific Writer
// Newbie Writer
$query = "SELECT COUNT(*) AS article_count FROM tbl_blogs WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()['article_count'] > 50) {
$badges['prolific_writer'] = true;
}

// Feedback Guru
$query = "SELECT COUNT(*) AS comment_count FROM article_comments WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()['comment_count'] >= 100) {
$badges['feedback_guru'] = true;
}

// Social Butterfly
$query = "SELECT COUNT(*) AS bookmark_count FROM user_bookmarks WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()['bookmark_count'] >= 10) {
$badges['social_butterfly'] = true;
}

// Trendsetter
$query = "SELECT COUNT(*) AS featured_count FROM tbl_blogs WHERE user_id = ? AND featured = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()['featured_count'] >= 5) {
$badges['trendsetter'] = true;
}

// Deep Thinker
$query = "SELECT LENGTH(content) AS content_length FROM tbl_blogs WHERE user_id = ? HAVING content_length >= 2000 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
$badges['deep_thinker'] = true;
}

// Early Bird
$query = "SELECT COUNT(*) AS early_comments FROM article_comments ac
JOIN tbl_blogs tb ON ac.article_id = tb.id
WHERE ac.user_id = ? AND TIMESTAMPDIFF(MINUTE, tb.DatePublished, ac.commented_at) <= 60";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()['early_comments'] >= 10) {
$badges['early_bird'] = true;
}

// Top Contributor
$query = "SELECT COUNT(DISTINCT Category) AS category_count FROM tbl_blogs WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()['category_count'] >= 3) {
$badges['top_contributor'] = true;
}

// Loyal Reader
$query = "SELECT COUNT(DISTINCT Category) AS tag_count FROM user_bookmarks ub
JOIN tbl_blogs tb ON ub.article_id = tb.id
WHERE ub.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()['tag_count'] >= 5) {
$badges['loyal_reader'] = true;
}

// Topic Enthusiast
$query = "SELECT Category, COUNT(*) AS article_count
FROM tbl_blogs
WHERE user_id = ?
GROUP BY Category
HAVING article_count >= 10";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$badges['topic_enthusiast'] = $result->num_rows > 0; // True if at least one tag qualifies

// Bookmark Collector
$query = "SELECT COUNT(*) AS bookmark_count
FROM user_bookmarks
WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookmark_data = $result->fetch_assoc();

if ($bookmark_data && $bookmark_data['bookmark_count'] >= 50) {
$badges['bookmark_collector'] = true;
}

// Consistent Contributor
$query = "SELECT COUNT(*) AS weeks_active
FROM (
SELECT YEARWEEK(DatePublished, 1) AS publish_week
FROM tbl_blogs
WHERE user_id = ?
GROUP BY YEARWEEK(DatePublished, 1)
) AS weekly_publications";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$weekly_data = $result->fetch_assoc();

if ($weekly_data && $weekly_data['weeks_active'] >= 4) {
$badges['consistent_contributor'] = true;
}
?>