<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'includes/quiz-header.php';

// If form is submitted, store data and redirect
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION['quizType'] = $_POST['quizType'] ?? 'classic';
    $_SESSION['quizTitle'] = $_POST['quizTitle'] ?? '';
    $_SESSION['quizDesc'] = $_POST['quizDesc'] ?? '';
    $_SESSION['quizCategory'] = $_POST['quizCategory'] ?? 'miscellaneous';
    $_SESSION['quizTags'] = $_POST['quizTags'] ?? '';
    $_SESSION['quizTimer'] = isset($_POST['quizTimer']) ? intval($_POST['quizTimer']) : 60; // Ensure it's an integer

    // Redirect based on quiz type
    if ($_SESSION['quizType'] === 'slides') {
        header("Location: " . BASE_URL . "quiz/views/create-slideshow.php");
        exit();
    } else {
        header("Location: " . BASE_URL . "quiz/views/create-classic.php");
        exit();
    }
}

// Retrieve stored values for form repopulation
$quizType = $_SESSION['quizType'] ?? 'classic';
$quizTitle = $_SESSION['quizTitle'] ?? '';
$quizDesc = $_SESSION['quizDesc'] ?? '';
$quizCategory = $_SESSION['quizCategory'] ?? 'miscellaneous';
$quizTags = $_SESSION['quizTags'] ?? '';
$quizTimer = $_SESSION['quizTimer'] ?? 60;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz | Narrative Quizzes</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>public/css/styles-create-quiz.css">
</head>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz | Narrative Quizzes</title>
    <style>
        /* General Page Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Ensure full-page height */
        .quiz-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            min-height: 100vh;
            padding: 20px;
        }

        /* Page Heading */
        h1 {
            text-align: center;
            color: #007BFF;
            font-family: 'Poppins', sans-serif;
            font-size: 2rem;
            margin-bottom: 30px;
        }

        /* Form Styling */
        #quizForm {
            width: 100%; /* Makes it take up the full width */
            max-width: 1200px; /* Limits the maximum width to avoid it becoming too wide */
            margin: 0 auto; /* Centers the form horizontally */
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Labels */
        label {
            font-weight: bold;
            display: block;
            margin: 8px 0;
        }

        /* Form container styles */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%;
        }

        /* Input Fields */
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%; /* Ensures inputs and selects take full width */
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            background-color: white;
            transition: border-color 0.3s ease;
        }

        /* Textarea Adjustments */
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Styled Dropdowns */
        select {
            appearance: none;
            cursor: pointer;
        }

        /* Hover & Focus Effects */
        .form-input:hover,
        .form-select:hover,
        .form-textarea:hover,
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: #007BFF;
            outline: none;
        }

        /* Buttons */
        .form-button,
        button {
            margin-top: 8px;
            background-color: #007BFF;
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        .form-button:hover,
        button:hover {
            background-color: #0056b3;
        }

        .form {
            width: 100%;
        }

        /* Question Section */
        #questionsContainer {
            margin-top: 25px;
        }

        /* Individual Question Block */
        .question {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        /* Remove Question Button */
        .removeQuestion {
            background-color: #dc3545;
            color: white;
            margin-top: 10px;
        }

        .removeQuestion:hover {
            background-color: #c82333;
        }

        /* Extra spacing between sections */
        .section {
            margin-bottom: 30px;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            #quizForm {
                width: 95%;
                padding: 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 15px;
            }
        }

        /* Login Section */
        .login-section {
            text-align: center;
            width: 100%;
            padding: 50px 0;
        }

        .login-button {
            background-color: #007BFF;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }

        .login-button:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>

<div class="quiz-container">
    <?php
    // If user is not logged in, show login prompt
    if (!isset($_SESSION['user_id'])) {
        echo '
            <div class="login-section">
                <h1>Log in to create a quiz!</h1>
                <a href="' . BASE_URL . 'user_auth.php" class="login-button">Log in</a>
            </div>
        ';
    } else {
        ?>
        <h2>Basic Info</h2>
        <form action="" method="POST" class="form">
            <div class="form-row">
                <!-- Quiz Type -->
                <div class="form-group">
                    <label for="quizType">Type:</label>
                    <select id="quizType" name="quizType" class="form-select" required>
                        <option value="classic" <?php echo ($quizType === "classic") ? "selected" : ""; ?>>Classic</option>
                        <option value="slides" <?php echo ($quizType === "slides") ? "selected" : ""; ?>>Slides</option>
                    </select>
                </div>

                <!-- Quiz Category -->
                <div class="form-group">
                    <label for="quizCategory">Category:</label>
                    <select id="quizCategory" name="quizCategory" class="form-select" required>
                        <?php
                        $categories = ["sports", "geography", "music", "movies", "tv", "history", "language", "science", "gaming", "literature", "entertainment", "miscellaneous"];
                        foreach ($categories as $category) {
                            $selected = ($quizCategory === $category) ? "selected" : "";
                            echo "<option value='$category' $selected>" . ucfirst($category) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Timer -->
                <div class="form-group">
                    <label for="quizTimer">Timer:</label>
                    <select id="quizTimer" name="quizTimer" class="form-select" required>
                        <?php
                        $timers = [60, 120, 180, 300, 600, 900, 1200, 1500, 1800]; // Seconds
                        foreach ($timers as $time) {
                            $selected = ($quizTimer == $time) ? "selected" : "";
                            echo "<option value='$time' $selected>" . gmdate("i:s", $time) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Quiz Title -->
            <label for="quizTitle">Title:</label>
            <input type="text" id="quizTitle" name="quizTitle" class="form-input" placeholder="Enter quiz title" value="<?php echo htmlspecialchars($quizTitle); ?>" required>

            <!-- Quiz Description -->
            <label for="quizDesc">Description:</label>
            <textarea id="quizDesc" name="quizDesc" class="form-textarea" rows="3" required><?php echo htmlspecialchars($quizDesc); ?></textarea>

            <!-- Quiz Tags -->
            <label for="quizTags">Tags (comma-separated):</label>
            <input type="text" id="quizTags" name="quizTags" class="form-input" placeholder="e.g., trivia, fun, general knowledge" value="<?php echo htmlspecialchars($quizTags); ?>" required>

            <button type="submit" class="form-button">Save</button>
        </form>
        <?php
    }
    ?>
</div>

</body>
</html>
