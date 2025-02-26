<?php
// Delete user PHP script
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"])) {
    $user_id = intval($_POST["user_id"]);

    // SQL query to delete the user from the database
    $delete_query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // User was deleted successfully
        echo json_encode([
            "success" => true,
            "message" => "User has been deleted successfully."
        ]);
        // Redirect back to the users page (or wherever is appropriate)
        header("Location: " . BASE_URL . "admin/user-management.php");
    } else {
        // Error deleting user
        echo json_encode([
            "success" => false,
            "message" => "Failed to delete user."
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    // Invalid request
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
}
?>
