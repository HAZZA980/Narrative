<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect the user to the login page if not logged in
    header("Location: " . BASE_URL . "signIn_register.php");
    exit;
}

// Proceed with the like functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = $_POST['article_id'];
    $user_id = $_POST['user_id'];
    $bookmark_action = $_POST['bookmark_action'];

    // Check if the like is being added or removed
    if ($bookmark_action === 'add') {
        $query = "INSERT INTO article_likes (article_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
    } elseif ($bookmark_action === 'remove') {
        $query = "DELETE FROM article_likes WHERE article_id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
    }

    // Redirect back to the article page
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
