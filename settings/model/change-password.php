<?php
session_start(); // Ensure session is started
include_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php'; // Include DB connection

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "layouts/pages/users/user_auth.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the necessary form fields are present
    if (!isset($_POST['current-password']) || !isset($_POST['password']) || !isset($_POST['confirm_password'])) {
        die("Debug: Missing required password fields.");
    }

    // Get form values
    $current_password = $_POST['current-password'];
    $new_password = $_POST['password'];  // 'password' from the form
    $confirm_password = $_POST['confirm_password'];  // 'confirm_password' from the form
    $user_id = $_SESSION['user_id'];

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        die("Debug: New password and confirm password do not match.");
    }

    // Validate the new password strength (basic check: length should be at least 8 characters)
    if (strlen($new_password) < 8) {
        die("Debug: Password must be at least 8 characters long.");
    }

    // Fetch current password from the database
    $query = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Debug: Prepare statement failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if current password matches the stored password
    if (!password_verify($current_password, $user['password'])) {
        die("Debug: Incorrect current password.");
    }

    // Hash the new password before saving it
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password in the database
    $update_query = "UPDATE users SET password = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    if (!$update_stmt) {
        die("Debug: Prepare update statement failed: " . $conn->error);
    }

    $update_stmt->bind_param("si", $new_password_hash, $user_id);
    if (!$update_stmt->execute()) {
        die("Debug: Update query execution failed: " . $update_stmt->error);
    }

    // Store success message in session
    $_SESSION['password_success'] = "Password updated successfully!";

    // Redirect back to the settings page with the success message
    header("Location: " . BASE_URL . "settings/account-management.php?accountManagement=password");
    exit;
}
?>
