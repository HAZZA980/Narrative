<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Get the article ID from the query string
$article_id = $_GET['id'] ?? null;

if (!$article_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid article ID.']);
    exit;
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Update the article's visibility in the database
$query = "UPDATE tbl_blogs SET private = '1' WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare the query.']);
    exit;
}

$stmt->bind_param("ii", $article_id, $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Article marked as private.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update the article.']);
}

$stmt->close();
$conn->close();
