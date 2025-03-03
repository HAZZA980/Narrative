<?php
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Check if article ID is provided and valid
$article_id = $_GET['id'] ?? null;
if (!$article_id || !is_numeric($article_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing Article ID.']);
    exit;
}

// ✅ Retrieve the article details (including image and user_id)
$query = "SELECT image, user_id FROM tbl_blogs WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($image, $user_id);
$stmt->fetch();

// Check if the article exists
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Article not found.']);
    exit;
}

// ✅ Define the correct user directory based on user_id
$userDirectory = BASE_PATH . "public/images/users/" . $user_id;
$imagePath = $userDirectory . "/" . $image;

// ✅ Delete the image file if it exists (but only if it's not the default logo)
if ($image && $image !== 'narrative-logo-big.png' && file_exists($imagePath)) {
    if (!unlink($imagePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete the image file.']);
        exit;
    }
}

// ✅ Delete the article from the database
$query = "DELETE FROM tbl_blogs WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $article_id);
if ($stmt->execute()) {
    // Redirect the user back to the previous page if available
    $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'account/feed.php';
    if (strpos($referer, 'article.php') !== false) {
        header('Location: ' . BASE_URL . 'forYou.php');
    } else {
        header('Location: ' . $referer);
    }
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete the article.']);
    exit();
}
?>
