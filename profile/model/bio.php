<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/narrative/config/config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id']; // Fetch user ID from session

// Get bio input & sanitize special characters
$bio = isset($_POST['bio-text']) ? trim($_POST['bio-text']) : '';
$bio = htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'); // Convert special characters

// Debugging: Check received values
error_log("Processing bio update for user_id: $user_id with bio: $bio");

// Ensure database connection is established
if (!isset($conn) || $conn->connect_error) {
    error_log("Database connection failed: " . ($conn->connect_error ?? "Connection object is not set."));
    die("Database connection failed.");
}

// Check if the user already has a bio
$query = "SELECT bio FROM user_details WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User already has a bio, update it
    $updateQuery = "UPDATE user_details SET bio = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $bio, $user_id);

    if ($updateStmt->execute()) {
        error_log("Bio updated successfully for user_id: $user_id");
        header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=3"); // Redirect to next tab
        exit;
    } else {
        error_log("Error updating bio: " . $updateStmt->error);
        die("Error updating bio.");
    }
} else {
    // No existing bio, insert a new row
    $insertQuery = "INSERT INTO user_details (user_id, bio) VALUES (?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("is", $user_id, $bio);

    if ($insertStmt->execute()) {
        error_log("Bio inserted successfully for user_id: $user_id");
        header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=3"); // Redirect to next tab
        exit;
    } else {
        error_log("Error inserting bio: " . $insertStmt->error);
        die("Error inserting bio.");
    }
}

// Close statements and connection
$stmt->close();
$conn->close();
?>
