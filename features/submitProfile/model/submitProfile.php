<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . 'phpProjects/Narrative/config/config.php'; // Include database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the user ID from session (Assuming the user is logged in)
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        die("User not authenticated");
    }

    // Get form inputs
    $dob = $_POST['dob'] ?? null;
    $bio = $_POST['bio-text'] ?? null;

    // Handle profile picture upload
    if (!empty($_FILES["profile-pic"]["name"])) {
        $target_dir = "uploads/profile_pics/";
        $file_name = basename($_FILES["profile-pic"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name;
        $upload_ok = 1;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image file
        $check = getimagesize($_FILES["profile-pic"]["tmp_name"]);
        if ($check === false) {
            die("File is not an image.");
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES["profile-pic"]["tmp_name"], $target_file)) {
            $profile_picture = $target_file;
        } else {
            die("Error uploading file.");
        }
    } else {
        $profile_picture = null;
    }

    // Insert or update user details
    $sql = "INSERT INTO user_details (user_id, dob, profile_picture, bio) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE dob = VALUES(dob), profile_picture = VALUES(profile_picture), bio = VALUES(bio)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $dob, $profile_picture, $bio);

    if ($stmt->execute()) {
        header("Location: " . BASE_PATH . "forYou.php");
        exit();

    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
