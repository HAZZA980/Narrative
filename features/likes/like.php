<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "", "db_narrative");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "User not authenticated"]);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = $_POST['article_id'];
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];

    if ($action === 'add') {
        // Check if the user has already liked the article
        $check_query = "SELECT * FROM article_likes WHERE article_id = ? AND user_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Insert like if it doesn't exist
            $query = "INSERT INTO article_likes (article_id, user_id) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $article_id, $user_id);
            $stmt->execute();
        }
    } elseif ($action === 'remove') {
        // Remove the like
        $query = "DELETE FROM article_likes WHERE article_id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
    }

    // Get updated like count
    $like_count_query = "SELECT COUNT(*) AS like_count FROM article_likes WHERE article_id = ?";
    $stmt = $conn->prepare($like_count_query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $like_count = $result->fetch_assoc()['like_count'];

    echo json_encode(["success" => true, "likes" => $like_count]);
    exit;
}
?>



