<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current profile picture from database
$query = "SELECT profile_picture FROM user_details WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profile_picture = $row['profile_picture'];

    if ($profile_picture && $profile_picture !== "default-profile.png") {
        $profilePicturePath = BASE_PATH . "public/images/users/$user_id/" . $profile_picture;

        // Delete the image file from server
        if (file_exists($profilePicturePath)) {
            unlink($profilePicturePath);
        }

        // Update the database to remove profile picture reference
        $updateQuery = "UPDATE user_details SET profile_picture = NULL WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $user_id);

        if ($updateStmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update database"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No image found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}

// Close DB connections
$stmt->close();
$conn->close();
?>
