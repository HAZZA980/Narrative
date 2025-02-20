<?php
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Check if article ID is provided
$article_id = $_GET['id'] ?? null;
if (!$article_id || !is_numeric($article_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing Article ID.']);
    exit;
}

// Prepare SQL query to get article details (including the image and user_id)
$query = "SELECT b.image, b.user_id, u.username 
          FROM tbl_blogs b
          JOIN users u ON b.user_id = u.user_id
          WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($image, $user_id, $username);
$stmt->fetch();

// Check if the article exists
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Article not found.']);
    exit;
}

// Define the user's image directory based on the user's username (retrieved from users table)
$userDirectory = BASE_PATH . "public/images/users/" . $username;

// Delete the image file if it exists
if ($image) {
    $imagePath = $userDirectory . "/" . $image;

    // Check if the image file exists and delete it
    if (file_exists($imagePath)) {
        if (!unlink($imagePath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete the image file.']);
            exit;
        }
    }
}

// Prepare SQL query to delete the article
$query = "DELETE FROM tbl_blogs WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $article_id);

if ($stmt->execute()) {
    // Redirect to the feed page after successful deletion
    header('Location: ' . BASE_URL . 'account/feed.php');
    exit();  // Make sure no further code is executed after the redirect
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete the article.']);
    exit();  // Stop further code execution in case of failure
}
?>
