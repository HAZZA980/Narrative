<?php

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "layouts/pages/users/signIn_register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $user_id = $_SESSION['user_id'];

    if (!empty($new_username)) {
        $query = "UPDATE users SET username = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $new_username, $user_id);

        if ($stmt->execute()) {
            $_SESSION['username'] = $new_username;
            header("Location: " . BASE_URL . "account/settings.php?success=username_changed");
            exit;
        } else {
            echo "Error updating username.";
        }
    }
}
?>
