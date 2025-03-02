<?php
session_start();

// Set JSON response headers
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_clean(); // Clears any previous output

// Database connection
$conn = new mysqli("localhost", "root", "", "db_narrative");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
define('BASE_URL', 'http://localhost/phpProjects/Narrative/');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = intval($_POST['article_id']);
    $user_id = intval($_POST['user_id']);
    $bookmark_action = $_POST['bookmark_action'];

    $success = false;

    if ($bookmark_action === 'add') {
        $query = "INSERT INTO article_likes (article_id, user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE user_id = user_id";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $success = $stmt->execute();
    } elseif ($bookmark_action === 'remove') {
        $query = "DELETE FROM article_likes WHERE article_id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $success = $stmt->execute();
    }

    // Get updated like count
    $like_count = 0;
    $like_count_query = "SELECT COUNT(*) AS like_count FROM article_likes WHERE article_id = ?";
    $stmt = $conn->prepare($like_count_query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $like_count = $row['like_count'];
    }

    echo json_encode(["success" => $success, "like_count" => $like_count]);
    exit; // Ensure script stops here
}
exit;
