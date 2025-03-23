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
    $article_id = isset($_POST['article_id']) ? $_POST['article_id'] : null;
    if (!$article_id) {
        echo json_encode(["success" => false, "message" => "Article ID is missing"]);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    $action = isset($_POST['action']) ? $_POST['action'] : null;
    if (!$action) {
        echo json_encode(["success" => false, "message" => "Action parameter is missing"]);
        exit;
    }

    if ($action === 'add') {
        // Check if the user has already bookmarked the article
        $check_query = "SELECT * FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Insert bookmark if it doesn't exist
            $query = "INSERT INTO user_bookmarks (article_id, user_id) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $article_id, $user_id);
            $stmt->execute();
        }
    } elseif ($action === 'remove') {
        // Remove the bookmark
        $query = "DELETE FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
    }

    // Check if the article is still bookmarked after the action
    $check_query = "SELECT * FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $article_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article_bookmarked = $result->num_rows > 0;

    echo json_encode(["success" => true, "bookmarked" => $article_bookmarked]);
    exit;
}
?>
