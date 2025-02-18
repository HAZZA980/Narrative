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
            height: 80vh; /* Full height viewport */
        }

        /* Centered container for the logout message */
        .logging-out {
            text-align: center;
            background: #ffffff; /* White background for the message box */
            padding: 2rem;
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            max-width: 400px;
            width: 100%;
        }

        /* Title styles */
        .logging-out-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #007bff; /* Bootstrap primary blue color */
        }

        /* Description styles */
        .logging-out-desc {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: #6c757d; /* Muted gray text */
        }

        /* Countdown styling */
        .countdown {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745; /* Bootstrap success green color */
            animation: pulse 1s infinite;
        }

        /* Countdown pulsing animation */
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

    </style>
    <script>
        let countdownValue = 3;

        function startCountdown() {
            const countdownElement = document.getElementById('countdown');
            const interval = setInterval(() => {
                if (countdownValue > 1) {
                    countdownValue--;
                    countdownElement.textContent = countdownValue;
                } else {
                    clearInterval(interval);
                    // Redirect after the countdown finishes
                    window.location.href = "<?php echo BASE_URL . 'explore/home.php'; ?>";
                }
            }, 1000); // Update every second
        }

        // Start the countdown when the page loads
        window.onload = startCountdown;
    </script>
</head>
<body>

<main class="log-out-main-content">
    <div class="logging-out">
        <h1 class="logging-out-title">You have been logged out successfully.</h1>
        <p class="logging-out-desc">Redirecting to the home page in <span id="countdown"
                                                                                class="countdown">3</span> seconds...
        </p>
    </div>
</main

</body>
</html>
