<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "db_narrative");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
define('BASE_URL', 'http://localhost/phpProjects/Narrative/');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/');

// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

// Get data from AJAX request
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['article_id']) || !isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
}

$article_id = intval($data['article_id']);
$user_id = $_SESSION['user_id']; // Get user ID from session
$action = $data['action'];

if ($action == "add") {
    // Add to bookmarks
    $query = "INSERT INTO user_bookmarks (user_id, article_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE article_id = article_id";
} elseif ($action == "remove") {
    // Remove from bookmarks
    $query = "DELETE FROM user_bookmarks WHERE user_id = ? AND article_id = ?";
} else {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
    exit;
}

// Execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $article_id);
$success = $stmt->execute();

echo json_encode(["success" => $success]);
?>
