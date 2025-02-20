<?php

// Start output buffering to prevent premature output
ob_start();

session_start(); // Ensure session is started


// Debug: Check if session variables are set correctly
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die("Debug: User not logged in. Redirecting...");
    header("Location: " . BASE_URL . "signIn_Register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Check if form data is being received
    if (!isset($_POST['current_password']) || !isset($_POST['new_password'])) {
        die("Debug: Password fields missing.");
    }

    $current_password = $_POST['current_password'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $user_id = $_SESSION['user_id'];

    // Debug: Check if user_id is set correctly
    if (!$user_id) {
        die("Debug: User ID is not set in session.");
    }

    // Debug: Verify database connection
    if (!$conn) {
        die("Debug: Database connection failed.");
    }

    $query = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Debug: Prepare statement failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        die("Debug: Execute statement failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        die("Debug: Fetching result failed.");
    }

    $user = $result->fetch_assoc();
    if (!$user) {
        die("Debug: No user found with this ID.");
    }

    // Debug: Check if password matches
    if (!password_verify($current_password, $user['password'])) {
        die("Debug: Incorrect current password.");
    }

    $query = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Debug: Prepare statement for update failed: " . $conn->error);
    }

    $stmt->bind_param("si", $new_password, $user_id);
    if (!$stmt->execute()) {
        die("Debug: Execute update statement failed: " . $stmt->error);
    }

    // Debug: Check if headers are already sent before redirect
    if (headers_sent($file, $line)) {
        die("Debug: Headers already sent in $file on line $line.");
    }

    echo "Debug: Password updated successfully. Redirecting...";
// Redirect with success message
    header("Location: " . BASE_URL . "account/settings.php?success=password_changed");
    exit;
}

ob_end_flush();
?>
