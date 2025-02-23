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
<?php include BASE_PATH . "acount/account-masthead.php"; ?>

<div class="settings-outer-container">
    <div class="settings-inner-container">

        <section class="settings-quick-links">
            <div class="settings-section">
                <h3 class="settings-section-title">Account Management</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>settings/account-management.php?accountManagement=username" class="settings-link">Change Username</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/account-management.php?accountManagement=password" class="settings-link">Change Password</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/account-management.php?accountManagement=dob" class="settings-link">Change Date of Birth</a></li>
                </ul>
            </div>

            <div class="settings-section">
                <h3 class="settings-section-title">Profile Settings</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>settings/profile-settings.php?profileSettings=profile_picture" class="settings-link">Update Profile Picture</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/profile-settings.php?profileSettings=bio" class="settings-link">Update Biography</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/profile-settings.php?profileSettings=media_links" class="settings-link">Update Social Media Links</a></li>
                </ul>
            </div>

            <div class="settings-section">
                <h3 class="settings-section-title">Content Preferences</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>settings/content-preferences.php?accountManagement=update-topics" class="settings-link">Update Your Recommended Topics</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/content-preferences.php?accountManagement=notification-preferences" class="settings-link">Notification Preferences</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/content-preferences.php?accountManagement=language-preferences" class="settings-link">Language Preferences</a></li>
                </ul>
            </div>

            <div class="settings-section">
                <h3 class="settings-section-title">Privacy & Security</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>settings/view/privacySettings.php" class="settings-link">Privacy Settings</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/view/securitySettings.php" class="settings-link">Security Settings</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/view/blockList.php" class="settings-link">Blocked Accounts</a></li>
                </ul>
            </div>

            <div class="settings-section">
                <h3 class="settings-section-title">Account Actions</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>settings/logout.php" class="settings-link">Log Out</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/deleteAccount.php" class="settings-link delete">Delete Account</a></li>
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
