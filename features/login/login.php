<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sign_in'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];

                // Check if user has preferences
                $stmt_pref = $conn->prepare("SELECT COUNT(*) AS tag_count FROM user_preferences WHERE user_id = ?");
                $stmt_pref->bind_param("i", $user['user_id']);
                $stmt_pref->execute();
                $result_pref = $stmt_pref->get_result();
                $tag_data = $result_pref->fetch_assoc();

                if ($tag_data['tag_count'] == 0) {
                    header("Location: " . BASE_URL . "profile/set-up-profile.php");
                } else {
                    header("Location: " . BASE_URL . "forYou.php");
                }
                exit;
            } else {
                $_SESSION['login_error'] = "Invalid email or password.";
                header("Location: " . BASE_URL . "user_auth.php");
                exit;
            }
        } else {
            $_SESSION['login_error'] = "Database error: " . $conn->error;
            header("Location: " . BASE_URL . "user_auth.php");
            exit;
        }
    } else {
        $_SESSION['login_error'] = "Please fill in all fields.";
        header("Location: " . BASE_URL . "user_auth.php");
        exit;
    }
}
?>
