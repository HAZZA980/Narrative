<?php include "config/config.php";
include BASE_PATH . 'features/write/write-icon-fixed.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="public/css/styles-home.css">
    <title>Home | Narrative</title>
    <style>
    </style>
</head>
<body>
<div class="main-container">
    <h1 class="welcome-heading">Welcome to Narrative</h1>
    <p class="sub-heading">Your stories. Your voice. Your world.</p>
    <button class="cta-button" onclick="explore()">Explore Now</button>

    <!-- Features Section -->
    <div class="features-section">
        <div class="feature-card">
            <img src="public/images/homepage/campfire-stories.jpeg" alt="Discover Stories">
            <div class="card-content">
                <h3>Discover Stories</h3>
                <p>Explore a world of diverse perspectives and captivating narratives.</p>
            </div>
        </div>

        <div class="feature-card">
            <img src="public/images/homepage/share-your-voice.jpg" alt="Share Your Voice">
            <div class="card-content">
                <h3>Share Your Voice</h3>
                <p>Join the conversation and share your unique experiences.</p>
            </div>
        </div>

        <div class="feature-card">
            <img src="public/images/homepage/connect-with-others.jpeg" alt="Connect with Others">
            <div class="card-content">
                <h3>Connect with Others</h3>
                <p>Build connections with people who share your passions and interests.</p>
            </div>
        </div>
    </div>
</div>

<script>
    BASE_URL = '<?php echo BASE_URL?>'
    // Button click function
    function explore() {
        window.location.href = BASE_URL + "explore/home.php";
    }
</script>
</body>
</html>
