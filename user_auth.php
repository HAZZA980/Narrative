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
//
//// Check if the form was submitted for login
//if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//    $email = trim($_POST['email']);
//    $password = trim($_POST['password']);
//
//    if (!empty($email) && !empty($password)) {
//        // Prepare the SQL query
//        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
//        if ($stmt) {
//            $stmt->bind_param("s", $email);
//            $stmt->execute();
//            $result = $stmt->get_result();
//            $user = $result->fetch_assoc();
//
//            if ($user && password_verify($password, $user['password'])) {
//                // Login successful, set session variables
//                $_SESSION['logged_in'] = true;
//                $_SESSION['user_id'] = $user['user_id']; // Adjust column name if necessary
//                $_SESSION['username'] = $user['username'];
//
//                // Check if user has tags in `user_preferences`
//                $stmt_pref = $conn->prepare("SELECT COUNT(*) AS tag_count FROM user_preferences WHERE user_id = ?");
//                $stmt_pref->bind_param("i", $user['user_id']);
//                $stmt_pref->execute();
//                $result_pref = $stmt_pref->get_result();
//                $tag_data = $result_pref->fetch_assoc();
//
//                if ($tag_data['tag_count'] == 0) {
//                    // User has no preferences set, redirect to recommendations
//                    header("Location: " . BASE_URL . "settings/recommendations.php");
//                    exit;
//                } else {
//                    // User has preferences, redirect to usual homepage
//                    header("Location: " . BASE_URL . "forYou.php");
//                    exit;
//                }
//            } else {
//                $error = "Invalid email or password.";
//            }
//        } else {
//            $error = "Database error: " . $conn->error;
//        }
//    } else {
//        $error = "Please fill in all fields.";
//    }
//}
//
//// Register Logic
//$registerError = null;
//if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//    $username = trim($_POST['username']);
//    $email = trim($_POST['email']);
//    $password = trim($_POST['password']);
//    $confirm_password = trim($_POST['confirm_password']);
//
//    // Check if the email already exists
//    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
//    $stmt->bind_param("s", $email);
//    $stmt->execute();
//    $stmt->store_result();
//
//    if ($stmt->num_rows > 0) {
//        $error = "The email address is already registered. Please use a different email or log in.";
//    } else {
//        if ($password === $confirm_password) {
//            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
//
//            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
//            $stmt->bind_param("sss", $username, $email, $hashed_password);
//
//            if ($stmt->execute()) {
//                // Store the email in the session to pre-fill the login form
//                $_SESSION['pre_filled_email'] = $email;
//                header("Location: " . BASE_URL . "layouts/pages/user/user_auth.php");
//                exit;
//            } else {
//                $error = "Error: " . $stmt->error;
//            }
//        } else {
//            $error = "Passwords do not match.";
//        }
//    }
//}
//
//// Fetch the blog articles and join with the users table to get the author's name
//$query = "SELECT b.id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, b.Tags, b.Image, u.username AS Author
//          FROM tbl_blogs b
//          JOIN users u ON b.user_id = u.user_id
//          ORDER BY b.datePublished DESC
//          LIMIT ? OFFSET ?";
//
//$limit = 10;  // Example value for limit (adjust as needed)
//$offset = 0;  // Example value for offset (adjust as needed)
//$stmt = $conn->prepare($query);
//$stmt->bind_param("ii", $limit, $offset);
//$stmt->execute();
//$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/styles-signIn-register.css">
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


                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var passwordField = document.getElementById("register-password");
                        var confirmPasswordField = document.getElementById("register-confirm-password");
                        var passwordMatchMessage = document.getElementById("password-match-message");
                        var passwordStrengthContainer = document.getElementById("password-strength-container");
                        var passwordRequirementsBox = document.getElementById("requirements-box");
                        var strengthBar = document.getElementById("strength-bar");
                        var strengthText = document.getElementById("strength-text");
                        var submitButton = document.getElementById("submit-button");

                        // Show password strength container and requirements box when the user starts typing
                        passwordField.addEventListener("focus", function () {
                            passwordStrengthContainer.classList.remove("hidden");
                            passwordStrengthContainer.classList.add("fade-in");
                            if (!isPasswordValid()) {
                                passwordRequirementsBox.classList.remove("hidden");
                                passwordRequirementsBox.classList.add("fade-in");
                            }
                        });

                        // Update password strength meter and check requirements
                        passwordField.addEventListener("input", function () {
                            checkPasswordStrength();
                            checkPasswordMatch(); // Also check password match when typing
                            if (isPasswordValid()) {
                                passwordRequirementsBox.classList.add("fade-out");
                                setTimeout(function () {
                                    passwordRequirementsBox.classList.add("hidden");
                                    passwordRequirementsBox.classList.remove("fade-out");
                                }, 500);  // Delay to match fade-out duration
                            } else {
                                passwordRequirementsBox.classList.remove("hidden");
                                passwordRequirementsBox.classList.add("fade-in");
                            }
                            enableSubmitButton(); // Check if the form can be submitted
                        });

                        // Check password validity based on length, uppercase letter, and number
                        function isPasswordValid() {
                            var password = passwordField.value;
                            return password.length >= 8 && /[A-Z]/.test(password) && /\d/.test(password);
                        }

                        // Function to check password strength
                        function checkPasswordStrength() {
                            let password = passwordField.value;
                            let strength = 0;

                            // Password strength calculation
                            if (password.length >= 4) strength++;
                            if (password.length >= 8) strength++;
                            if (/[A-Z]/.test(password)) strength++;
                            if (/\d/.test(password)) strength++;

                            // Update strength bar and text
                            let progress = 0;
                            let color = "red";
                            let text = "Weak";

                            switch (strength) {
                                case 0:
                                case 1:
                                    progress = 25;
                                    color = "red";
                                    text = "Weak";
                                    break;
                                case 2:
                                    progress = 50;
                                    color = "orange";
                                    text = "Getting There";
                                    break;
                                case 3:
                                    progress = 75;
                                    color = "orange";
                                    text = "Getting There";
                                    break;
                                case 4:
                                    progress = 100;
                                    color = "green";
                                    text = "Secure";
                                    break;
                            }

                            // Smooth loading of the bar
                            strengthBar.style.transition = "width 0.5s ease-in-out";
                            strengthBar.style.width = `${progress}%`;
                            strengthBar.style.backgroundColor = color;
                            strengthText.innerText = text;
                            strengthText.style.color = color;

                            // Show/hide the requirements box based on validity
                            if (isPasswordValid()) {
                                passwordRequirementsBox.classList.add("hidden");
                            } else {
                                passwordRequirementsBox.classList.remove("hidden");
                            }
                        }

                        // Check if passwords match only when the user types in confirm password field
                        confirmPasswordField.addEventListener("input", function () {
                            checkPasswordMatch();
                            enableSubmitButton(); // Check if the form can be submitted
                        });

                        // Function to check if passwords match
                        function checkPasswordMatch() {
                            var password = passwordField.value;
                            var confirmPassword = confirmPasswordField.value;

                            // Show "Passwords don't match" only when the user starts typing into the confirm password box
                            if (confirmPassword !== "" && password !== confirmPassword) {
                                passwordMatchMessage.textContent = "Passwords don't match";
                                passwordMatchMessage.style.color = "red";
                            } else if (confirmPassword !== "") {
                                passwordMatchMessage.textContent = "Passwords match";
                                passwordMatchMessage.style.color = "green";
                            } else {
                                passwordMatchMessage.textContent = ""; // Clear the message if the user clears the confirm password field
                            }
                        }

                        // Enable submit button only when the form is valid
                        function enableSubmitButton() {
                            var password = passwordField.value;
                            var confirmPassword = confirmPasswordField.value;

                            // Enable the submit button only if the password is valid and passwords match
                            if (isPasswordValid() && password === confirmPassword) {
                                submitButton.disabled = false; // Enable button
                            } else {
                                submitButton.disabled = true; // Disable button
                            }
                        }

                        // Validate form before submission
                        function validateForm() {
                            var password = passwordField.value;
                            var confirmPassword = confirmPasswordField.value;

                            // Ensure passwords match
                            if (password !== confirmPassword) {
                                alert("Passwords do not match.");
                                return false; // Prevent form submission
                            }

                            // Ensure password meets the strength requirements
                            if (!isPasswordValid()) {
                                alert("Password does not meet strength requirements.");
                                return false; // Prevent form submission
                            }

                            return true; // Allow form submission
                        }

                        // Hide password strength and requirements box when clicking outside the input
                        document.addEventListener("click", function (event) {
                            if (!passwordField.contains(event.target) && passwordField.value.trim() === "") {
                                passwordStrengthContainer.classList.add("hidden");
                                passwordRequirementsBox.classList.add("hidden");
                            }
                        });
                    });


                </script>


                <style>
                    .hidden {
                        display: none;
                    }

                    .password-strength {
                        width: 100%;
                        height: 10px;
                        background: #ccc;
                        margin-top: 5px;
                        border-radius: 5px;
                        position: relative;
                        overflow: hidden;
                    }

                    #strength-bar {
                        height: 100%;
                        width: 0%;
                        background: red;
                        border-radius: 5px;
                        transition: width 0.3s ease-in-out, background-color 0.3s ease-in-out;
                    }

                    #strength-text {
                        display: block;
                        margin-top: 5px;
                        font-size: 14px;
                        font-weight: bold;
                    }

                    /* Form footer to align button and error */
                    .form-footer {
                        display: flex;
                        align-items: center;
                        gap: 10px; /* Adds space between button and error */
                    }

                    /* Password Error Box */
                    #password-error {
                        background-color: #ffdddd;
                        color: #d8000c;
                        border: 1px solid #d8000c;
                        padding: 5px 10px;
                        border-radius: 5px;
                        font-size: 13px;
                        white-space: nowrap;
                    }

                    /* Info Icon Style */
                    .info-icon {
                        display: inline-block;
                        background-color: #888;
                        color: #fff;
                        font-size: 14px;
                        border-radius: 50%;
                        width: 20px;
                        height: 20px;
                        text-align: center;
                        line-height: 20px;
                        margin-left: 5px;
                        cursor: pointer;
                        font-weight: bold;
                    }

                    /* Tooltip Style */
                    .info-icon:hover::after {
                        content: "Password must be at least 8 characters long, include 1 number, and 1 uppercase letter.";
                        position: absolute;
                        top: 300px;
                        left: 420px;
                        background-color: #f9f9f9;
                        color: #333;
                        padding: 5px;
                        border: 1px solid black;
                        border-radius: 5px;
                        font-size: 12px;
                        white-space: normal;
                        width: 300px;
                    }


                    .password-requirements {
                        display: block;
                        margin-top: 10px;
                        padding: 10px;
                        background-color: #f9f9f9;
                        border: 1px solid #ddd;
                        border-radius: 5px;
                        font-size: 12px;
                        color: #333;
                        white-space: normal;
                        max-width: 300px; /* Optional: limit the width */
                    }

                    .password-requirements br {
                        content: "\A";
                        white-space: pre;
                    }

                    #requirements-box {
                        opacity: 1;
                        visibility: visible;
                        transition: opacity 0.5s ease, visibility 0.5s ease;
                    }

                    #requirements-box.hidden {
                        opacity: 0;
                        visibility: hidden;
                    }


                    .fade-in {
                        opacity: 1 !important;
                        visibility: visible !important;
                        transition: opacity 0.5s ease, visibility 0.5s ease;
                    }

                    .fade-out {
                        opacity: 0 !important;
                        visibility: hidden !important;
                        transition: opacity 0.5s ease, visibility 0.5s ease;
                    }

                </style>

            </div>
        </div>
        <div class="logo-image-container">
            <img src="<?php echo BASE_URL ?>narrative-logo-big.png" alt="Company Logo">
        </div>
    </div>
</main>
</body>
</html>
