<?php
// Assuming the necessary includes (e.g., database connection) are already made.
session_start();
$user_id = $_SESSION['user_id']; // Get the user_id from the session (assuming user is logged in)

// Define variables
$dob = $_POST['dob'] ?? null;
$profile_picture = null;
$bio = $_POST['bio'] ?? null;  // Add your bio processing code here if required (e.g., textarea)

$message = "";

// Define the user's image directory
$userDirectory = BASE_PATH . "public/images/users/" . $user_id;

// Create the user's image directory if it doesn't exist
if (!is_dir($userDirectory) && !mkdir($userDirectory, 0777, true)) {
    error_log("Failed to create directory: $userDirectory. Check permissions.");
    die("Failed to create directory for user images.");
}

// Check if the form has been submitted and the data is present
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Profile Picture Upload
    if (!empty($_FILES['profile-pic']['name']) && $_FILES['profile-pic']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['profile-pic']['name']);
        $imagePath = $userDirectory . "/" . $imageName;

        // Attempt to move the uploaded file to the user's directory
        if (move_uploaded_file($_FILES['profile-pic']['tmp_name'], $imagePath)) {
            $profile_picture = $imageName;  // Save the filename to store in the database
        } else {
            $message = "Failed to upload the image.";
        }
    } else {
        // If no image is uploaded, set a default image
        $profile_picture = 'narrative-logo-big.png';  // Default image
    }

    // Insert or update user details in the database
    if ($dob && $user_id) {
        // Prepare SQL query to insert/update data
        $stmt = $conn->prepare("INSERT INTO user_details (user_id, dob, profile_picture, bio) VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE dob = ?, profile_picture = ?, bio = ?");
        $stmt->bind_param("issssss", $user_id, $dob, $profile_picture, $bio, $dob, $profile_picture, $bio);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Redirect to the next part of the setup process
            header("Location: " . BASE_URL . "user/set-up-account.php#next-tab");
            exit;
        } else {
            // Handle error if insertion fails
            $message = "There was an error saving your details. Please try again.";
        }
    }
}
?>

<?php if (!empty($message)): ?>
    <div class="error"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
