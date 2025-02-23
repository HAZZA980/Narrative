<?php

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "layouts/pages/users/user_auth.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $user_id = $_SESSION['user_id'];
    session_start();  // Ensure sessions are started
    include_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php'; // Include database connection

// Check if the user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: " . BASE_URL . "layouts/pages/users/user_auth.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['username'])) {
            $new_username = trim($_POST['username']);
            $user_id = $_SESSION['user_id'];

            if (!empty($new_username)) {
                // Prepare the update query
                $query = "UPDATE users SET username = ? WHERE user_id = ?";
                if ($stmt = $conn->prepare($query)) {
                    $stmt->bind_param("si", $new_username, $user_id);

                    if ($stmt->execute()) {
                        $_SESSION['username'] = $new_username;
                        $_SESSION['username_success'] = "Username has been changed successfully!";
                        header("Location: " . BASE_URL . "settings/account-management.php?accountManagement=username");
                        exit;
                    } else {
                        $_SESSION['username_error'] = "Error updating username. Please try again.";
                    }
                    $stmt->close();
                } else {
                    $_SESSION['username_error'] = "Database error: Unable to prepare statement.";
                }
            } else {
                $_SESSION['username_error'] = "Username cannot be empty.";
            }
        }
    }

// Redirect back if there's an error
    header("Location: " . BASE_URL . "settings/account-management.php?accountManagement=username");
    exit;


    if (!empty($new_username)) {
        $query = "UPDATE users SET username = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $new_username, $user_id);

        if ($stmt->execute()) {
            $_SESSION['username'] = $new_username;
            header("Location: " . BASE_URL . "settings/account-management.php?accountManagement=username");
            exit;
        } else {
            echo "Error updating username.";
        }
    }
}
$stmt->close();
?>
