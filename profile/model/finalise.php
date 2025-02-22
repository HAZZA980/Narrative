<?php
// Fetch user details from the database
$user_id = $_SESSION['user_id'];

$query = "SELECT dob, bio, profile_picture FROM user_details WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize default values
$dob = $bio = $profile_picture = null;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $dob = $row['dob'];
    $bio = $row['bio'];
    $profile_picture = $row['profile_picture'];
}

// Define the profile picture path
$profilePicturePath = (!empty($profile_picture) && file_exists(BASE_PATH . "public/images/users/$user_id/$profile_picture"))
    ? BASE_URL . "public/images/users/$user_id/" . htmlspecialchars($profile_picture)
    : BASE_URL . "public/images/users/default-profile.png";



// Fetch user's preferred categories from user_preferences
$stmt = $conn->prepare("SELECT DISTINCT tag FROM user_preferences WHERE user_id = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$preferred_categories = [];
while ($row = $result->fetch_assoc()) {
    $preferred_categories[] = $row['tag']; // Assuming 'tag' holds category names
}

$stmt->close();

// If no preferences found, set a default message
if (empty($preferred_categories)) {
    $preferred_categories[] = 'No preferences provided';
}
?>

