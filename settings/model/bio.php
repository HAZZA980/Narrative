<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "db_narrative");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Check if the session is available and the user ID is set
    session_start(); // Make sure the session is started
    if (!isset($_SESSION['user_id'])) {
        die("Session 'user_id' is not set.");
    }

    $user_id = $_SESSION['user_id'] ?? null;
    $bio = $_POST['bio'] ?? '';

    // Debug: Check if the bio is being received correctly
    if (empty($bio)) {
        die("Bio is empty. Please check the form submission.");
    }

    // Debug: Check user ID
    if (is_null($user_id)) {
        die("User ID is null. Please check session.");
    }

    $query = "SELECT bio FROM user_details WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Error in SQL preparation: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User already has a bio, so update it
        $update_query = "UPDATE user_details SET bio = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        if ($update_stmt === false) {
            die("Error in SQL preparation for update: " . $conn->error);
        }
        $update_stmt->bind_param("si", $bio, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION['bio_success'] = "Biography updated successfully!";
        } else {
            $_SESSION['bio_error'] = "Failed to update biography.";
        }
    } else {
        // User does not have a bio, so insert a new one
        $insert_query = "INSERT INTO user_details (user_id, bio) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        if ($insert_stmt === false) {
            die("Error in SQL preparation for insert: " . $conn->error);
        }
        $insert_stmt->bind_param("is", $user_id, $bio);

        if ($insert_stmt->execute()) {
            $_SESSION['bio_success'] = "Biography added successfully!";
        } else {
            $_SESSION['bio_error'] = "Failed to add biography.";
        }
    }

    // Debug: Check session messages
    error_log("Bio success/error message: " . ($_SESSION['bio_success'] ?? $_SESSION['bio_error']));

    // Redirect back to the settings page
    header("Location: http://localhost/phpProjects/Narrative/settings/profile-settings.php?profileSettings=bio");
    exit;
}

?>
