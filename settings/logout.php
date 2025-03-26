<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Unset all session variables and destroy the session
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <style>
        /* Base styles for the page */
        .log-out-main-content {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa; /* Light gray background */
            color: #343a40; /* Dark text for readability */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full height viewport */
        }

        .logging-out {
            text-align: center;
            background: rgba(255, 255, 255, 0.15); /* Glassmorphism effect */
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            width: 420px; /* Fixed width */
            height: 200px; /* Fixed height */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.8s ease-in-out;
            position: relative;
        }

        /* Title and text styles */
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
            opacity: 0; /* Hidden initially */
            top: 20px;
        }

        .logging-out-desc {
            font-size: 1.2rem;
            color: #FF6B00;
            transition: opacity 1s ease-in-out;
            position: absolute;
            top: 70px; /* Keep "Please wait" in the same spot */
        }

        /* CSS Loading Spinner */
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

        /* Spinning animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
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
            const logoutTitle = document.querySelector('.logging-out-title');
            const logoutDesc = document.querySelector('.logging-out-desc');
            const redirectingTitle = document.querySelector('.redirecting-title');

            // Fade out "Logging out..."
            setTimeout(() => {
                logoutTitle.style.opacity = "0";
            }, 1500);

            // Fade in "Redirecting to home page..." smoothly
            setTimeout(() => {
                logoutTitle.style.display = "none";
                redirectingTitle.style.opacity = "1";
            }, 2500);

            // Redirect after fade transition
            setTimeout(() => {
                window.location.href = "<?php echo BASE_URL . 'explore/home.php'; ?>";
            }, 4000);
        }

        // Start the transition when the page loads
        window.onload = fadeOutIn;
    </script>
</head>
<body>

<main class="log-out-main-content">
    <div class="logging-out">
        <h1 class="logging-out-title">Logging out...</h1>
        <h1 class="redirecting-title">Redirecting to home page...</h1>
        <p class="logging-out-desc">Please wait</p>
        <div class="loading-circle"></div>
    </div>
</main>

</body>
</html>
