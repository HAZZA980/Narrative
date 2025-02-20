<?php
ob_start();
include $_SERVER['DOCUMENT_ROOT']. '/phpProjects/Narrative/config/config.php';

// Get the data from the form
$article_id = $_POST['article_id'];
$user_id = $_POST['user_id'];
$bookmark_action = $_POST['bookmark_action'];

// Check the action (add or remove bookmark)
if ($bookmark_action == 'add') {
    // Add the article to the user_bookmarks table
    $query = "INSERT INTO user_bookmarks (user_id, article_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $article_id);
    $stmt->execute();
} elseif ($bookmark_action == 'remove') {
    // Remove the article from the user_bookmarks table
    $query = "DELETE FROM user_bookmarks WHERE user_id = ? AND article_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $article_id);
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
?>
