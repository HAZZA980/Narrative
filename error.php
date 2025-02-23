<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/phpProjects/narrative/config/config.php";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ERROR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .error-message-title {
            color: #e74c3c;
            font-size: 36px;
            margin-top: 50px;
        }

        .error-message-para {
            font-size: 18px;
            color: #555;
            margin-top: 20px;
        }

        .error-message-link {
            color: #3498db;
            text-decoration: none;
        }

        .error-message-link:hover {
            text-decoration: underline;
        }

        .error-message-image-container {
            width: 300px;
            margin-top: 30px;
        }

        .error-message-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .error-message {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .error-message .error-message-title {
            margin: 0;
        }

        .error-message .error-message-para {
            margin-top: 15px;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="error-message-container">
    <div class="error-message">
        <img class="error-message-image-container" src="<?php echo BASE_URL ?>public/images/error-page/broken-screen.png" alt="Error Image">
        <h3 class="error-message-title">WHOOPS - Something's Broken</h3>
        <h6 class="error-message-para">Try using the <a class="error-message-link" href="<?php echo BASE_PATH; ?>search.php">SEARCH</a> if you're looking for something
            specific. <br><br>Otherwise, try the Explore tab to browse categories or visit the <a class="error-message-link" href="<?php echo BASE_PATH; ?>siteMap.php">SITE MAP</a>.</h6>
    </div>
</div>

</body>
</html>
