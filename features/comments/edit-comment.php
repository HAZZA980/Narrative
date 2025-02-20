<?php
ob_start();
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die('User not authenticated');
}

// Validate inputs
if (isset($_POST['comment'], $_POST['comment_id'], $_POST['article_id'])) {
    $comment_id = intval($_POST['comment_id']);
    $article_id = intval($_POST['article_id']);
    $updated_comment = trim($_POST['comment']);

    // Update the comment in the database
    $stmt = $conn->prepare("UPDATE article_comments SET comment = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $updated_comment, $comment_id, $_SESSION['user_id']);
    $stmt->execute();

    // Redirect back to the article page
    header("Location: " . BASE_URL . "user/article.php?id=$article_id");
    exit();
} else {
    die('Invalid request');
}

