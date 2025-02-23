<?php
// Include configuration file for database connection
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to add a comment.']);
    exit;
}

// Check if the form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;

    // Retrieve and sanitize input data
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

    // Validate input data
    if (empty($article_id) || empty($comment)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid article or comment content.']);
        exit;
    }

    // Get the logged-in user's ID
    $user_id = $_SESSION['user_id'];

    // Sanitize the comment by escaping special characters for SQL safety
    $comment = $conn->real_escape_string($comment); // Escape only for SQL

    // Prepare and execute the SQL query to insert the comment
    $query = "INSERT INTO article_comments (article_id, user_id, comment, commented_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    // Bind parameters for the SQL statement
    $stmt->bind_param("iis", $article_id, $user_id, $comment);

    if ($stmt->execute()) {
        // Comment added successfully
        echo json_encode(['status' => 'success', 'message' => 'Comment added successfully.']);

        // Redirect to the article page after successful comment submission
        header("Location: " . BASE_URL . "user/article.php?id=" . $article_id);
        exit; // Ensure no further code is executed after the redirect
    } else {
        // Error adding comment
        echo json_encode(['status' => 'error', 'message' => 'Failed to add comment: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Close the database connection
$conn->close();
?>
