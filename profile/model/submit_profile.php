<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id']; // Fetch user ID from session
$dob = $_POST['dob'] ?? '';
$profile_picture = null;

// Define the user's image directory
$userDirectory = BASE_PATH . "public/images/users/" . $user_id;

// Ensure the directory exists or create it
if (!is_dir($userDirectory) && !mkdir($userDirectory, 0777, true)) {
    die("Failed to create directory for user images.");
}

// Handle profile picture upload if new picture is selected
if (!empty($_FILES['profile-pic']['name']) && $_FILES['profile-pic']['error'] === UPLOAD_ERR_OK) {
    // Generate a new file name using user_id
    $fileExtension = pathinfo($_FILES['profile-pic']['name'], PATHINFO_EXTENSION);
    $profilePictureName = "user-" . $user_id . "-profile-picture." . $fileExtension;
    $profilePicturePath = $userDirectory . "/" . $profilePictureName;

    if (move_uploaded_file($_FILES['profile-pic']['tmp_name'], $profilePicturePath)) {
        $profile_picture = $profilePictureName;
    } else {
        die("Failed to upload profile picture.");
    }
} else {
    // If no new image is uploaded, keep the existing profile picture
    // If the user has removed it, set the profile_picture as NULL or default
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == true) {
        $profile_picture = NULL;
    } else {
        $profile_picture = 'default-profile.png'; // Default image
    }
}

// Debugging: Ensure database connection is valid
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}

// Update or insert user profile
$query = "SELECT profile_picture FROM user_details WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing record
    $updateQuery = "UPDATE user_details SET dob = ?, profile_picture = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $dob, $profile_picture, $user_id);

    if ($updateStmt->execute()) {
        header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=2"); // Redirect to Bio tab
        exit;
    } else {
        die("Error updating profile.");
    }
} else {
    // Insert new profile record if none exists
    $insertQuery = "INSERT INTO user_details (user_id, dob, profile_picture) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iss", $user_id, $dob, $profile_picture);

    if ($insertStmt->execute()) {
        header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=2"); // Redirect to Bio tab
        exit;
    } else {
        die("Error inserting profile.");
    }
}

// Close DB connections
$stmt->close();
$conn->close();
?>
