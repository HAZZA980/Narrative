<?php
// Store form data in session when submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION['quizType'] = $_POST['quizType'] ?? 'classic';
    $_SESSION['quizTitle'] = $_POST['quizTitle'] ?? '';
    $_SESSION['quizDesc'] = $_POST['quizDesc'] ?? '';
    $_SESSION['quizCategory'] = $_POST['quizCategory'] ?? 'miscellaneous';
    $_SESSION['quizTags'] = $_POST['quizTags'] ?? '';
    $_SESSION['quizTimer'] = isset($_POST['quizTimer']) ? intval($_POST['quizTimer']) : 60; // Ensure it's an integer

    // Redirect to the appropriate page based on quizType
    if ($_SESSION['quizType'] === 'slides') {
        header("Location: views/create-slideshow.php");
    } else {
        header("Location: views/create-classic.php");
    }
    exit();
}

// Retrieve stored values (if available)
$quizType = $_SESSION['quizType'] ?? 'classic';
$quizTitle = $_SESSION['quizTitle'] ?? '';
$quizDesc = $_SESSION['quizDesc'] ?? '';
$quizCategory = $_SESSION['quizCategory'] ?? 'miscellaneous';
$quizTags = $_SESSION['quizTags'] ?? '';
$quizTimer = $_SESSION['quizTimer'] ?? 60; // Default to 60 seconds
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Basic Info</title>
</head>
<body>
<h2>Basic Info</h2>
<form action="" method="POST">
    <div class="form-row">
        <!-- Quiz Type -->
        <div class="form-group">
            <label for="quizType">Type:</label>
            <select id="quizType" name="quizType" class="form-select" required>
                <option value="classic" <?php echo ($quizType === "classic") ? "selected" : ""; ?>>Classic</option>
                <option value="slides" <?php echo ($quizType === "slides") ? "selected" : ""; ?>>Slides</option>
                <option value="clickable" <?php echo ($quizType === "clickable") ? "selected" : ""; ?>>Clickable</option>
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
</body>
</html>
