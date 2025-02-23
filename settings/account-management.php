<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "account/account-masthead.php";
include BASE_PATH . "settings/model/update_dob.php";

// Ensure the user is verified before accessing settings
if (!isset($_SESSION['verified_user'])) {
    header("Location: verify-identity.php");
    exit;
}

// Determine which section to show
$section = $_GET['accountManagement'] ?? 'username';
$section = $_GET['accountManagement'] ?? 'dob';


// Include the appropriate logic files
if ($section === 'username') {
    include BASE_PATH . "settings/model/change-username.php";
} elseif ($section === 'password') {
    include BASE_PATH . "settings/model/change-password.php";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>settings/css/settings.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>account/css/styles-register-password.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>account/css/account-management-template.css">

    <title>Account Settings</title>
    <style>

    </style>
</head>
<body>

<div class="settings-outer-container">
    <div class="settings-inner-container">

        <div class="settings-container">
            <!-- Sidebar Menu -->
            <nav class="settings-sidebar">
                <h3 class="settings-section-title">Account Management</h3>
                <ul class="settings-menu">
                    <li><a href="?accountManagement=username" class="<?= $section === 'username' ? 'active' : '' ?>">Change
                            Username</a></li>
                    <li><a href="?accountManagement=password" class="<?= $section === 'password' ? 'active' : '' ?>">Change
                            Password</a></li>
                    <li><a href="?accountManagement=dob" class="<?= $section === 'dob' ? 'active' : '' ?>">Change Date
                            of Birth</a></li>
                </ul>
            </nav>

            <!-- Content Section -->
            <main class="settings-content">
                <?php if ($section === 'username'): ?>
                    <section class="settings-section">
                        <h2>Change Username</h2>

                        <?php if (isset($_SESSION['username_success'])): ?>
                            <p class="success-message"><?= $_SESSION['username_success']; ?></p>
                            <?php unset($_SESSION['username_success']); ?>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['username_error'])): ?>
                            <p class="error-message"><?= $_SESSION['username_error']; ?></p>
                            <?php unset($_SESSION['username_error']); ?>
                        <?php endif; ?>
                        <form method="post" class="settings-form">
                            <label for="username">New Username:</label>
                            <input type="text" id="username" name="username" required>
                            <button type="submit" name="change_username">Update</button>
                        </form>
                    </section>

                <?php elseif ($section === 'password'): ?>
                    <section class="settings-section">
                        <h2>Change Password</h2>
                        <?php if (isset($_SESSION['password_success'])): ?>
                            <p class="success-message"><?= $_SESSION['password_success'];
                                unset($_SESSION['password_success']); ?></p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['password_error'])): ?>
                            <p class="error-message"><?= $_SESSION['password_error'];
                                unset($_SESSION['password_error']); ?></p>
                        <?php endif; ?>


                        <form method="post" class="settings-form">
                            <label for="current-password">Current Password:</label>
                            <input type="password" id="current-password" name="current-password" required>

                            <div class="form-group">
                                <label for="register-password">Password:
                                    <span class="info-icon">i</span>
                                </label>
                                <input type="password" id="register-password" name="password" required
                                       onkeyup="checkPasswordStrength()" onfocus="showStrengthMeter()">

                                <!-- Password Strength Indicator -->
                                <div id="password-strength-container" class="hidden">
                                    <div class="password-strength">
                                        <div id="strength-bar"></div>
                                    </div>
                                    <span id="strength-text">Weak</span>
                                </div>

                                <div id="requirements-box">
                                    <span class="password-requirements">
                                        Password must:<br>
                                        - Be at least 8 characters long <br>
                                        - Include 1 number <br>
                                        - Include 1 uppercase letter
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="register-confirm-password">Confirm Password:</label>
                                <input type="password" id="register-confirm-password" name="confirm_password"
                                       required onkeyup="checkPasswordMatch()">
                                <span id="password-match-message"></span>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn" id="submit-button" disabled>Update Password</button>
                            </div>
                        </form>
                    </section>

                <?php elseif ($section === 'dob'): ?>
                <section class="settings-section">
                    <h2>Change Date of Birth</h2>
                    <?php if (isset($_SESSION['dob_success'])): ?>
                        <p class="success-message"><?= $_SESSION['dob_success']; unset($_SESSION['dob_success']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['dob_error'])): ?>
                        <p class="error-message"><?= $_SESSION['dob_error']; unset($_SESSION['dob_error']); ?></p>
                    <?php endif; ?>

                    <form method="post" class="settings-form">
                        <label for="dob">New Date of Birth:</label>
                        <input type="date" id="dob" name="dob" required>
                        <button type="submit">Update</button>
                    </form>
                </section>
                <?php endif; ?>
            </main>
        </div>


    </div>
</div>
<script src="<?php echo BASE_URL ?>account/js/passwordStrengthChecker.js"></script>

</body>
</html>