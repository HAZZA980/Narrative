<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'features/write/write-icon-fixed.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure user_id exists in the session
if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not found in session.");
}

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();

    try {
        // Step 1: Delete user's articles
        $query_delete_articles = "DELETE FROM tbl_blogs WHERE user_id = ?";
        $stmt_articles = $conn->prepare($query_delete_articles);
        if (!$stmt_articles) throw new Exception("Error preparing DELETE statement for articles: " . $conn->error);
        $stmt_articles->bind_param("i", $user_id);
        $stmt_articles->execute();
        $stmt_articles->close();

        // Step 2: Remove user images
        $user_image_dir = '../../../../public/images/users/' . $user_id;
        if (is_dir($user_image_dir)) {
            $files = glob($user_image_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }
            rmdir($user_image_dir);
        }

        // Step 3: Delete user preferences
        $query_delete_preferences = "DELETE FROM user_preferences WHERE user_id = ?";
        $stmt_preferences = $conn->prepare($query_delete_preferences);
        if (!$stmt_preferences) throw new Exception("Error preparing DELETE statement for preferences: " . $conn->error);
        $stmt_preferences->bind_param("i", $user_id);
        $stmt_preferences->execute();
        $stmt_preferences->close();

        // Step 4: Delete user from database
        $query_delete_user = "DELETE FROM users WHERE user_id = ?";
        $stmt_user = $conn->prepare($query_delete_user);
        if (!$stmt_user) throw new Exception("Error preparing DELETE statement for user: " . $conn->error);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $stmt_user->close();

        // Commit transaction
        $conn->commit();

        // Clear session
        session_unset();
        session_destroy();

        // Display styled success message
        echo '
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Deleting Account...</title>
            <style>
                /* Base styles */
                .log-out-main-content {
                    font-family: Arial, sans-serif;
                    background-color: #f8f9fa;
                    color: #343a40;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }

                .logging-out {
                    text-align: center;
                    background: rgba(255, 255, 255, 0.15);
                    padding: 2.5rem;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    width: 420px;
                    height: 200px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    animation: fadeIn 0.8s ease-in-out;
                    position: relative;
                }

                .logging-out-title, .redirecting-title {
                    font-size: 1.8rem;
                    font-weight: 600;
                    color: #1E3A8A;
                    transition: opacity 1s ease-in-out;
                    position: absolute;
                }

                .logging-out-title {
                    top: 20px;
                }

                .redirecting-title {
                    opacity: 0;
                    top: 20px;
                }

                .logging-out-desc {
                    font-size: 1.2rem;
                    color: #FF6B00;
                    transition: opacity 1s ease-in-out;
                    position: absolute;
                    top: 70px;
                }

                .loading-circle {
                    width: 50px;
                    height: 50px;
                    border: 5px solid rgba(0, 0, 0, 0.2);
                    border-radius: 50%;
                    border-top-color: #FF6B00;
                    animation: spin 1s linear infinite;
                    position: absolute;
                    bottom: 20px;
                }

                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-20px); }
                    to { opacity: 1; transform: translateY(0); }
                }

                @media (max-width: 480px) {
                    .logging-out {
                        width: 90%;
                        height: 180px;
                        padding: 2rem;
                    }

                    .logging-out-title, .redirecting-title {
                        font-size: 1.6rem;
                    }

                    .logging-out-desc {
                        font-size: 1.1rem;
                    }

                    .loading-circle {
                        width: 40px;
                        height: 40px;
                        border-width: 4px;
                    }
                }
            </style>
            <script>
                function fadeOutIn() {
                    const logoutTitle = document.querySelector(".logging-out-title");
                    const logoutDesc = document.querySelector(".logging-out-desc");
                    const redirectingTitle = document.querySelector(".redirecting-title");

                    // Fade out "Deleting Account..."
                    setTimeout(() => {
                        logoutTitle.style.opacity = "0";
                    }, 1500);

                    // Fade in "Clearing the databanks..."
                    setTimeout(() => {
                        logoutTitle.style.display = "none";
                        redirectingTitle.style.opacity = "1";
                    }, 2500);

                    // Redirect after transition
                    setTimeout(() => {
                        window.location.href = "../user_auth.php"; 
                    }, 4000);
                }

                window.onload = fadeOutIn;
            </script>
        </head>
        <body>
            <main class="log-out-main-content">
                <div class="logging-out">
                    <h1 class="logging-out-title">Deleting Account...</h1>
                    <h1 class="redirecting-title">Clearing the Databanks...</h1>
                    <p class="logging-out-desc">Please wait</p>
                    <div class="loading-circle"></div>
                </div>
            </main>
        </body>';
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error deleting account: " . $e->getMessage());
    }
} else {
    echo '<form method="post">
        <p>Are you sure you want to delete your account? This action cannot be undone.</p>
        <button type="submit">Delete Account</button>
    </form>';
}
?>
