<?php
// delete-comment.php
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if (!isset($_SESSION['user_id'])) {
    die('User not authenticated');
}

// Get the comment ID and article ID from the URL
$comment_id = intval($_GET['comment_id']);
$article_id = intval($_GET['article_id']);

// Delete the comment from the database
$stmt = $conn->prepare("DELETE FROM article_comments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $_SESSION['user_id']);
$stmt->execute();

header("Location: " . BASE_URL . "user/article.php?id=$article_id"); // Redirect back to the article page
exit();