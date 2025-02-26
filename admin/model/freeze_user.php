<?php
session_start();
header('Content-Type: application/json');

// Debugging: Log all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$conn = new mysqli("localhost", "root", "", "db_narrative");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$response = ['success' => false, 'message' => 'Something went wrong.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id'])) {
    $article_id = intval($_POST['article_id']);  // Get the article_id

    // Step 1: Get the user_id associated with this article_id from tbl_blogs
    $stmt = $conn->prepare("SELECT user_id FROM tbl_blogs WHERE id = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id);
    $stmt->fetch();

    // Check if article exists
    if ($stmt->num_rows === 0) {
        $response['message'] = "Article not found.";
        echo json_encode($response);
        exit;
    }

    $stmt->close();  // Close the statement after fetching the user_id

    // Step 2: Check the freeze_user status of the associated user in the Users table
    $stmt = $conn->prepare("SELECT freeze_user FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($currentFreezeStatus);
    $stmt->fetch();

    // Check if the user exists
    if ($stmt->num_rows === 0) {
        $response['message'] = "User not found.";
        echo json_encode($response);
        exit;
    }

    // Step 3: Toggle the freeze_user status (0 to 1 or 1 to 0)
    $newFreezeStatus = ($currentFreezeStatus == 1) ? 0 : 1;

    // Update freeze_user column in Users table
    $updateStmt = $conn->prepare("UPDATE users SET freeze_user = ? WHERE user_id = ?");
    $updateStmt->bind_param("ii", $newFreezeStatus, $user_id);

    if ($updateStmt->execute()) {
        $response = [
            'success' => true,
            'message' => $newFreezeStatus ? "User account has been frozen." : "User account has been unfrozen."
        ];
    } else {
        $response['message'] = "Database update failed.";
    }

    echo json_encode($response);
    exit;
} else {
    $response['message'] = "Invalid request.";
    echo json_encode($response);
    exit;
}
?>
