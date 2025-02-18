<?php
// Start output buffering to prevent issues with header()
ob_start();
include '../../../config/config.php';


// Get the data from the form
$article_id = $_POST['article_id'];
$user_id = $_POST['user_id'];
$like_action = $_POST['bookmark_action']; // Reusing the name "bookmark_action" for like

try {
    // Check the action (add or remove like)
    if ($like_action == 'add') {
        // Add the like to the article_likes table
        $query = "INSERT INTO article_likes (article_id, user_id, liked_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
    } elseif ($like_action == 'remove') {
        // Remove the like from the article_likes table
        $query = "DELETE FROM article_likes WHERE article_id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
    }

// Redirect back to the previous page
    if (isset($_SERVER['HTTP_REFERER'])) {
        $previousPage = $_SERVER['HTTP_REFERER'];
        header("Location: $previousPage");
    } else {
        // Fallback if HTTP_REFERER is not set
        header("Location: forYou.php");
    }
    exit;
} catch (Exception $e) {
    // Log and display error for debugging
    error_log("Error in like.php: " . $e->getMessage());
    echo "An error occurred: " . $e->getMessage();
    exit;
}
