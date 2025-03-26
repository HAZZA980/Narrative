<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if (!$conn) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"])) {
    $user_id = intval($_POST["user_id"]);

    // Debugging
    error_log("Attempting to delete user with ID: " . $user_id);

    // Step 1: Delete related records in user_preferences
    $delete_preferences_query = "DELETE FROM user_preferences WHERE user_id = ?";
    $stmt1 = $conn->prepare($delete_preferences_query);

    if (!$stmt1) {
        die(json_encode(["success" => false, "message" => "Prepare statement failed: " . $conn->error]));
    }

    $stmt1->bind_param("i", $user_id);

    if (!$stmt1->execute()) {
        error_log("Failed to delete user preferences: " . $stmt1->error);
        die(json_encode(["success" => false, "message" => "Failed to delete user preferences."]));
    }

    $stmt1->close();

    // Step 2: Delete user from users table
    $delete_user_query = "DELETE FROM users WHERE user_id = ?";
    $stmt2 = $conn->prepare($delete_user_query);

    if (!$stmt2) {
        die(json_encode(["success" => false, "message" => "Prepare statement failed: " . $conn->error]));
    }

    $stmt2->bind_param("i", $user_id);

    if ($stmt2->execute()) {
        error_log("User with ID $user_id deleted successfully.");
        echo json_encode(["success" => true, "message" => "User has been deleted successfully."]);
        header("Location: " . BASE_URL . "admin/user-management.php");
        exit();
    } else {
        error_log("Failed to delete user: " . $stmt2->error);
        echo json_encode(["success" => false, "message" => "Failed to delete user. Error: " . $stmt2->error]);
    }

    $stmt2->close();
    $conn->close();
} else {
    error_log("Invalid request: Missing or incorrect user_id");
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
