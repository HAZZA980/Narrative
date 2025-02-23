<?php
// Initialize PHP session
ob_start();
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Initialize variables
$error = null;

// Retrieve pre-filled email and password from the session, if available
$preFilledEmail = isset($_SESSION['pre_filled_email']) ? $_SESSION['pre_filled_email'] : '';
$preFilledPassword = isset($_SESSION['pre_filled_password']) ? $_SESSION['pre_filled_password'] : '';
unset($_SESSION['pre_filled_email']); // Clear the pre-filled email after use
unset($_SESSION['pre_filled_password']); // Clear the pre-filled password after use

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/styles-signIn-register.css">
    <link rel="stylesheet" href="<?php echo BASE_URL?>account/css/styles-register-password.css">
    <title>Sign In / Register</title>
    <script>
        // Function to set active tab based on URL parameter
        function setActiveTab() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'login'; // Default to login

            document.querySelectorAll('.tab-content').forEach(tabContent => tabContent.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

            document.getElementById(tab).classList.add('active');
            document.querySelector(`.tab[data-tab="${tab}"]`).classList.add('active');
        }

        function changeTab(tabName) {
            // Update URL without reloading the page
            const newUrl = window.location.pathname + '?tab=' + tabName;
            window.history.pushState({path: newUrl}, '', newUrl);

            setActiveTab();
        }

        window.onload = setActiveTab;
    </script>
</head>
<body>
<main class="login-container">
    <div class="form-and-logo-container">
        <!-- Tabs and Forms -->
        <div class="form-container-wrapper">
            <div class="tabs">
                <div class="tab" data-tab="login" onclick="changeTab('login')">Sign In</div>
                <div class="tab" data-tab="register" onclick="changeTab('register')">Register</div>
            </div>

            <div class="form-container">
                <div id="login" class="tab-content">
                    <h2>Sign In</h2>
                    <?php session_start();
                    if (isset($_SESSION['login_error'])): ?>
                        <div class="alert"><?php echo htmlspecialchars($_SESSION['login_error']); ?></div>
                        <?php unset($_SESSION['login_error']); ?>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo BASE_URL; ?>features/login/login.php">
                        <input type="hidden" name="sign_in" value="1">
                        <div class="form-group">
                            <label for="sign-in-email">Email:</label>
                            <input type="email" id="sign-in-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="sign-in-password">Password:</label>
                            <input type="password" id="sign-in-password" name="password" required>
                        </div>
                        <button type="submit" class="btn">Sign In</button>
                    </form>
                </div>

                <div id="register" class="tab-content">
                    <h2>Register</h2>
                    <?php if (isset($_SESSION['register_error'])): ?>
                        <div class="alert"><?php echo htmlspecialchars($_SESSION['register_error']); ?></div>
                        <?php unset($_SESSION['register_error']); ?>
                    <?php endif; ?>

                    <form id="register-form" method="POST" action="<?php echo BASE_URL; ?>features/register/register.php" onsubmit="return validateForm()">
                        <input type="hidden" name="register" value="1">

                        <div class="form-group">
                            <label for="register-username">Full Name:</label>
                            <input type="text" id="register-username" name="username" value="<?php echo isset($_SESSION['pre_filled_username']) ? htmlspecialchars($_SESSION['pre_filled_username']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="register-email">Email:</label>
                            <input type="email" id="register-email" name="email" value="<?php echo isset($_SESSION['pre_filled_email']) ? htmlspecialchars($_SESSION['pre_filled_email']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="register-password">Password:
                                <span class="info-icon">i</span>
                            </label>
                            <input type="password" id="register-password" name="password" required onkeyup="checkPasswordStrength()" onfocus="showStrengthMeter()">

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
                            <input type="password" id="register-confirm-password" name="confirm_password" required onkeyup="checkPasswordMatch()">
                            <span id="password-match-message"></span>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn" id="submit-button" disabled>Register</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
        <div class="logo-image-container">
            <img src="<?php echo BASE_URL ?>narrative-logo-big.png" alt="Company Logo">
        </div>
    </div>
</main>
<script src="<?php echo BASE_URL?>account/js/passwordStrengthChecker.js"></script>
</body>
</html>
