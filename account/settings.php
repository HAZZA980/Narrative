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
    <style>
        .feed-outer-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        /* Modal Background */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.3); /* Subtle dark background */
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        /* Modal Content Box */
        .modal-content {
            top: 0;
            left: 0;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            width: 400px; /* Fixed width for a small modal */
            max-width: 90%; /* Ensures it is responsive */
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Modal Title */
        .modal-content h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
        }

        /* Modal Action Buttons */
        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px; /* Spacing between the buttons */
        }

        .modal-actions button {
            padding: 12px 25px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }


        /* Update Button Specific Styling */
        .btn-confirm {
            background: linear-gradient(to bottom, forestgreen, darkgreen); /* Darker red gradient on hover */
            box-shadow: 0 5px 10px rgba(178, 34, 34, 0.3); /* Red shadow on hover */
            color: white;
        }

        .btn-confirm:hover {
            background-color: darkgreen; /* Slightly darker green */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Cancel Button Specific Styling */
        .btn-cancel {
            background: linear-gradient(to bottom, #8B0000, #660000); /* Darker red gradient on hover */
            box-shadow: 0 5px 10px rgba(178, 34, 34, 0.3); /* Red shadow on hover */
            color: white;
            margin-left: 10px;
        }

        .btn-cancel:hover {
            background-color: darkred; /* Slightly darker red */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }


    </style>
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
                    <li><a href="#" class="settings-link delete" onclick="openDeleteModal()">Delete Account</a></li>
                </ul>
            </div>
        </section>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h2>Are you sure you want to delete your account?</h2>
        <div class="modal-actions">
            <button class="btn-confirm" onclick="confirmDelete()">Confirm</button>
            <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>

<form id="delete-account-form" action="<?php echo BASE_URL?>settings/deleteAccount.php" method="POST" style="display: none;">
    <input type="hidden" name="confirm_delete" value="true">
</form>

<script>
    function openDeleteModal() {
        document.getElementById("deleteModal").style.display = "flex";
    }
    function closeDeleteModal() {
        document.getElementById("deleteModal").style.display = "none";
    }
    function confirmDelete() {
        document.getElementById("delete-account-form").submit();
    }
    window.onclick = function(event) {
        const modal = document.getElementById("deleteModal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
</script>
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