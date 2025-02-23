<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Check if the user is already verified
if (!isset($_SESSION['verified_user'])) {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['password'])) {
        $password = $_POST['password'];

        // Get the logged-in user's data
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            die("User not logged in.");
        }

        // Fetch user from DB
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();
        $stmt->close();

        // Verify password
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['verified_user'] = true; // Grant access
            // Redirect to the settings page
            header("Location: http://localhost/phpProjects/Narrative/settings/account-management.php");
            exit; // Ensure no further script execution
        } else {
            $error = "Incorrect password. Please try again.";
        }
    }
}

// If not verified, show password prompt
if (!isset($_SESSION['verified_user'])):
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verify Identity</title>
        <style>
            .verify-container-outer {
                height: 80%;  /* Ensure the body takes full height */
                margin: 0;
                margin-top: -10rem;
                display: flex;
                justify-content: center; /* Horizontally center */
                align-items: center; /* Vertically center */
                background-color: #f4f4f4; /* Optional: adds a background color */
            }

            .verify-container {
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                text-align: center;
                width: 350px;
            }
            h2 {
                margin-bottom: 15px;
                font-size: 20px;
                color: #333;
            }
            .error-message {
                color: red;
                margin-bottom: 10px;
            }
            input[type="password"] {
                width: 100%;
                padding: 10px;
                margin: 10px 0;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
            button {
                background-color: #007bff;
                color: #fff;
                border: none;
                padding: 10px 15px;
                font-size: 16px;
                border-radius: 5px;
                cursor: pointer;
            }
            button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>

    <div class="verify-container-outer">
    <div class="verify-container">
        <h2>This is sensitive data</h2>
        <p>Please enter your password to continue.</p>
        <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
        <form method="post">
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Continue</button>
        </form>
    </div>
    </div>
    </body>
    </html>
    <?php
    exit; // Stop further execution until user is verified
endif;
?>

<!-- If the user is verified, continue with the settings page -->
<?php include BASE_PATH . 'settings/feed.php'; ?>
