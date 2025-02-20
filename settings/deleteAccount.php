<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Ensure user_id exists in the session
if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not found in session.");
}

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Step 1: Delete all the user's articles from tbl_blogs
        $query_delete_articles = "DELETE FROM tbl_blogs WHERE user_id = ?";
        $stmt_articles = $conn->prepare($query_delete_articles);
        if (!$stmt_articles) {
            throw new Exception("Error preparing DELETE statement for articles: " . $conn->error);
        }
        $stmt_articles->bind_param("i", $user_id);
        $stmt_articles->execute();
        $stmt_articles->close();

        // Step 2: Remove user images from public/images/users/[user_id]
        $user_image_dir = '../../../../public/images/users/' . $user_id;
        if (is_dir($user_image_dir)) {
            $files = glob($user_image_dir . '/*'); // Get all files in the directory
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file); // Delete file
                }
            }
            rmdir($user_image_dir); // Remove directory
        }

        // Step 3: Delete user preferences from user_preferences
        $query_delete_preferences = "DELETE FROM user_preferences WHERE user_id = ?";
        $stmt_preferences = $conn->prepare($query_delete_preferences);
        if (!$stmt_preferences) {
            throw new Exception("Error preparing DELETE statement for preferences: " . $conn->error);
        }
        $stmt_preferences->bind_param("i", $user_id);
        $stmt_preferences->execute();
        $stmt_preferences->close();

        // Step 4: Delete the user from users table
        $query_delete_user = "DELETE FROM users WHERE user_id = ?";
        $stmt_user = $conn->prepare($query_delete_user);
        if (!$stmt_user) {
            throw new Exception("Error preparing DELETE statement for user: " . $conn->error);
        }
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $stmt_user->close();

        // Commit the transaction
        $conn->commit();

        // Clear session and log out the user
        session_unset();
        session_destroy();

        // Output success message and redirect
        echo '<p>Account deleted successfully. You will be redirected in 3 seconds.</p>';
        echo '<script>
        setTimeout(function() {
            window.location.href = "../signIn_register.php"; // Redirect to the sign-in page
        }, 3000); // 3000 milliseconds = 3 seconds
        </script>';
        exit;

    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        die("Error deleting account: " . $e->getMessage());
    }
} else {
    // Display the confirmation form
    echo '<form method="post">
        <p>Are you sure you want to delete your account? This action cannot be undone.</p>
        <button type="submit">Delete Account</button>
    </form>';
}
?>
