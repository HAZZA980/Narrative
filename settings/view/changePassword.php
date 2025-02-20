<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "layouts/mastheads/articles/account-masthead.php";
include BASE_PATH . "settings/model/change-password.php";

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password</title>
    <link rel="stylesheet" href="<?php echo BASE_URL?>settings/css/change-password.css">
</head>
<body>

<main class="change-password-parent-container">
    <section class="change-password-section-container">
        <form method="post" class="change-password-form">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <button type="submit">Change Password</button>
        </form>
    </section>
</main>
</html>
