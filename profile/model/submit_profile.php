<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id']; // Fetch user ID from session
$dob = $_POST['dob'] ?? '';
$profile_picture = null;

// Debugging: Output received values
error_log("Processing DOB & Profile Picture update for user_id: $user_id, dob: $dob");

// Define the user's image directory
$userDirectory = BASE_PATH . "public/images/users/" . $user_id;

// Ensure the directory exists or create it
if (!is_dir($userDirectory) && !mkdir($userDirectory, 0777, true)) {
    error_log("Failed to create directory: $userDirectory. Check permissions.");
    die("Failed to create directory for user images.");
}

// Handle profile picture upload
if (!empty($_FILES['profile-pic']['name']) && $_FILES['profile-pic']['error'] === UPLOAD_ERR_OK) {
    // Generate a new file name using user_id
    $fileExtension = pathinfo($_FILES['profile-pic']['name'], PATHINFO_EXTENSION);
    $profilePictureName = "user-" . $user_id . "-profile-picture." . $fileExtension;
    $profilePicturePath = $userDirectory . "/" . $profilePictureName;

    if (move_uploaded_file($_FILES['profile-pic']['tmp_name'], $profilePicturePath)) {
        $profile_picture = $profilePictureName;
        error_log("Profile picture uploaded successfully: $profilePicturePath");
    } else {
        error_log("Failed to upload profile picture.");
        die("Failed to upload profile picture.");
    }
} else {
    // If no new image is uploaded, keep the existing profile picture
    $profile_picture = 'default-profile.png';
    error_log("No new profile picture uploaded. Using default.");
}

// Debugging: Ensure database connection is valid
if (!isset($conn) || $conn->connect_error) {
    error_log("Database connection failed: " . ($conn->connect_error ?? "Connection object is not set."));
    die("Database connection failed.");
}

// Check if user already has a record in user_details
$query = "SELECT profile_picture FROM user_details WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User already has a record, update it
    $updateQuery = "UPDATE user_details SET dob = ?, profile_picture = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $dob, $profile_picture, $user_id);

    if ($updateStmt->execute()) {
        error_log("DOB & Profile Picture updated for user_id: $user_id");
        header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=2"); // Redirect to Bio tab
        exit;
    } else {
        error_log("Error updating profile: " . $updateStmt->error);
        die("Error updating profile.");
    }
} else {
    // No existing record, insert new row
    $insertQuery = "INSERT INTO user_details (user_id, dob, profile_picture) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iss", $user_id, $dob, $profile_picture);

    if ($insertStmt->execute()) {
        error_log("DOB & Profile Picture inserted for user_id: $user_id");
        header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=2"); // Redirect to Bio tab
        exit;
    } else {
        error_log("Error inserting profile: " . $insertStmt->error);
        die("Error inserting profile.");
    }
}

// Close statements and connection
$stmt->close();
$conn->close();

