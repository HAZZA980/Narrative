<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Form not submitted via POST method.");
}

// Check if the submit button was pressed
if (!isset($_POST['update_profile_picture'])) {
    die("Submit button not pressed.");
}

// Ensure the user is logged in
$user_id = $_SESSION['user_id']; // Fetch user ID from session
$profile_picture = null;

// Define the user's image directory
$userDirectory = BASE_PATH . "public/images/users/" . $user_id;

// Ensure the directory exists or create it
if (!is_dir($userDirectory) && !mkdir($userDirectory, 0777, true)) {
    die("Failed to create directory for user images.");
}

// Check if the user already has a profile picture saved
$query = "SELECT profile_picture FROM user_details WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$currentProfilePicture = null;

if ($result->num_rows > 0) {
    // Fetch the current profile picture
    $row = $result->fetch_assoc();
    $currentProfilePicture = $row['profile_picture'];
}

// Handle profile picture upload if a new picture is selected
if (!empty($_FILES['profile-pic']['name'])) {
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
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == true) {
        // If the user wants to remove the image, set it to NULL
        $profile_picture = NULL;
    } else {
        // If no new image is uploaded and no removal is requested, keep the current image
        $profile_picture = $currentProfilePicture ?? 'default-profile.png'; // Fallback to default if no image is set
    }
}

// Ensure database connection is valid
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}

// Only update if the profile picture has changed
if ($profile_picture !== $currentProfilePicture) {
    // If there's an existing image and it's being replaced, delete the old image file
    if ($currentProfilePicture && file_exists($userDirectory . "/" . $currentProfilePicture)) {
        unlink($userDirectory . "/" . $currentProfilePicture); // Remove the old image from the server
    }

    // Update the profile picture in the database
    $updateQuery = "UPDATE user_details SET profile_picture = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $profile_picture, $user_id);

    if ($updateStmt->execute()) {
        header("Location: " . BASE_URL . "settings/profile-settings.php?profileSettings=profile_picture"); // Redirect back
        exit;
    } else {
        die("Error updating profile.");
    }
} else {
    // No changes to the profile picture, do nothing (refresh the page)
    header("Location: " . BASE_URL . "settings/profile-settings.php?profileSettings=profile_picture"); // Redirect back
    exit;
}

// Close DB connections
$stmt->close();
$conn->close();

?>