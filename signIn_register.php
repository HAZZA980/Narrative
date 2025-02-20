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

// Check if the form was submitted for login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Prepare the SQL query
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                // Login successful, set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id']; // Adjust column name if necessary
                $_SESSION['username'] = $user['username'];

                // Check if user has tags in `user_preferences`
                $stmt_pref = $conn->prepare("SELECT COUNT(*) AS tag_count FROM user_preferences WHERE user_id = ?");
                $stmt_pref->bind_param("i", $user['user_id']);
                $stmt_pref->execute();
                $result_pref = $stmt_pref->get_result();
                $tag_data = $result_pref->fetch_assoc();

                if ($tag_data['tag_count'] == 0) {
                    // User has no preferences set, redirect to recommendations
                    header("Location: " . BASE_URL . "settings/recommendations.php");
                    exit;
                } else {
                    // User has preferences, redirect to usual homepage
                    header("Location: " . BASE_URL . "forYou.php");
                    exit;
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Database error: " . $conn->error;
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

// Register Logic
$registerError = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "The email address is already registered. Please use a different email or log in.";
    } else {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                // Store the email in the session to pre-fill the login form
                $_SESSION['pre_filled_email'] = $email;
                header("Location: " . BASE_URL . "layouts/pages/user/signIn_register.php");
                exit;
            } else {
                $error = "Error: " . $stmt->error;
            }
        } else {
            $error = "Passwords do not match.";
        }
    }
}

// Fetch the blog articles and join with the users table to get the author's name
$query = "SELECT b.id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, b.Tags, b.Image, u.username AS Author
          FROM tbl_blogs b
          JOIN users u ON b.user_id = u.user_id
          ORDER BY b.datePublished DESC
          LIMIT ? OFFSET ?";

$limit = 10;  // Example value for limit (adjust as needed)
$offset = 0;  // Example value for offset (adjust as needed)
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/styles-signIn-register.css">
    <title>Sign In / Register</title>
</head>
<body>
<main class="login-container">
    <div class="form-and-logo-container">
        <!-- Tabs and Forms -->
        <div class="form-container-wrapper">
            <div class="tabs">
                <div class="tab active" onclick="showTab('sign-in')">Sign In</div>
                <div class="tab" onclick="showTab('register')">Register</div>
            </div>

            <div class="form-container">
                <div id="sign-in" class="tab-content active">
                    <h2>Sign In</h2>
                    <?php if ($error): ?>
                        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="sign_in" value="1">
                        <div class="form-group">
                            <label for="sign-in-email">Email:</label>
                            <input type="email" id="sign-in-email" name="email" required value="<?php echo htmlspecialchars($preFilledEmail); ?>">
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
                    <?php if ($registerError): ?>
                        <div class="alert"><?php echo htmlspecialchars($registerError); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="register" value="1">
                        <div class="form-group">
                            <label for="register-username">Full Name:</label>
                            <input type="text" id="register-username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="register-email">Email:</label>
                            <input type="email" id="register-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="register-password">Password:</label>
                            <input type="password" id="register-password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="register-confirm-password">Confirm Password:</label>
                            <input type="password" id="register-confirm-password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn">Register</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Logo -->
        <div class="logo-image-container">
            <img src="<?php echo BASE_URL?>narrative-logo-big.png" alt="Company Logo">
        </div>
    </div>
</main>

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        document.querySelector(`.tab[onclick="showTab('${tabId}')"]`).classList.add('active');
    }
</script>
</body>
</html>
