<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "account/account-masthead.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/settings.css">
    <title>Account Settings</title>
</head>
<body>
<?php include "../layouts/mastheads/articles/account-masthead.php"; ?>

<div class="settings-outer-container">
    <div class="settings-inner-container">

        <section class="settings-quick-links">
            <div class="settings-section">
                <h3 class="settings-section-title">Account Management</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>settings/view/changeUsername.php"
                           class="settings-link">Change Username</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/view/changePassword.php"
                           class="settings-link">Change Password</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/logout.php" class="settings-link">Log
                            Out</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/deleteAccount.php"
                           class="settings-link delete">Delete Account</a></li>
                </ul>
            </div>

            <div class="settings-section">
                <h3 class="settings-section-title">Article Management</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>includes/createArticle.php" class="settings-link">Write an
                            Article</a></li>
                    <li><a href="<?php echo BASE_URL; ?>model/feed.php" class="settings-link">Edit Your Articles</a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>model/feed.php" class="settings-link">Delete an Article</a></li>
                    <li><a href="<?php echo BASE_URL; ?>layouts/pages/user/settings/recommendations.php"
                           class="settings-link">Update Your Recommended Topics</a></li>
                </ul>
            </div>
        </section>

        <section class="faqs-section">
            <div class="faqs">
                <h3 id="faqs-title">FAQs</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>includes/createArticle.php" class="settings-link">Do I need to
                            create an account to read the blog?</a></li>
                    <li><a href="<?php echo BASE_URL; ?>model/feed.php" class="settings-link">Who is my Target
                            Audience</a></li>
                    <li><a href="<?php echo BASE_URL; ?>model/feed.php" class="settings-link">Delete an Article</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/recommendations.php"
                           class="settings-link">Update Your Recommended Topics</a></li>
                    <li><a href="<?php echo BASE_URL; ?>layouts/pages/user/settings/faqs.php" class="settings-link">More
                            FAQs</a></li>
                </ul>
            </div>
        </section>
    </div>

</div>
<?php
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'password_changed') {
        echo "<script>alert('Password has been changed successfully!');</script>";
    } elseif ($_GET['success'] === 'username_changed') {
        echo "<script>alert('Username has been changed successfully!');</script>";
    }
}
?>
</body>
</html>
