<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "layouts/mastheads/articles/account-masthead.php";
include BASE_PATH . "settings/model/change-username.php";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Username</title>
    <link rel="stylesheet" href="<?php echo BASE_URL?>settings/css/change-username.css">

</head>
<body>

<main class="change-username-parent-containerr">
    <section class="change-username-section-container">
        <form method="post" class="change-username-form">
            <label for="username">New Username:</label>
            <input type="text" id="username" name="username" required>
            <button type="submit">Update</button>
        </form>
    </section>
</main>
</body>
</html>
