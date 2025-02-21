<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

function isValidPassword($password) {
    return preg_match('/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $password);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($username) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            if (!isValidPassword($password)) {
                $_SESSION['register_error'] = "Password must be at least 8 characters long, include 1 number, and 1 uppercase letter.";
                header("Location: " . BASE_URL . "user_auth.php?tab=register");
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['register_error'] = "Please enter a valid email address.";
                header("Location: " . BASE_URL . "user_auth.php?tab=register");
                exit;
            }

            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $_SESSION['register_error'] = "Email already registered.";
                header("Location: " . BASE_URL . "user_auth.php?tab=register");
                exit;
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);

                if ($stmt->execute()) {
                    $_SESSION['pre_filled_email'] = $email;
                    header("Location: " . BASE_URL . "user_auth.php?tab=login");
                    exit;
                } else {
                    $_SESSION['register_error'] = "Error: " . $stmt->error;
                    header("Location: " . BASE_URL . "user_auth.php?tab=register");
                    exit;
                }
            }
        } else {
            $_SESSION['register_error'] = "Passwords do not match.";
            header("Location: " . BASE_URL . "user_auth.php?tab=register");
            exit;
        }
    } else {
        $_SESSION['register_error'] = "Please fill in all fields.";
        header("Location: " . BASE_URL . "user_auth.php?tab=register");
        exit;
    }
}
?>
