<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'includes/quiz-header.php';

$quizId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

// Redirect to login page if the user is not logged in
if (!$userId) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

// Fetch quiz details from database
if ($quizId) {
    $quizQuery = $conn->prepare("SELECT * FROM `quiz-quizzes` WHERE id = ? AND user_id = ?");
    $quizQuery->bind_param("ii", $quizId, $userId);
    $quizQuery->execute();
    $quizResult = $quizQuery->get_result();

    if ($quizResult->num_rows === 0) {
        // If quiz not found or does not belong to user, redirect
        header("Location: " . BASE_URL . "profile.php?error=unauthorized");
        exit;
    }

    $quiz = $quizResult->fetch_assoc();
    $quizType = $quiz['quiz_type'] ?? 'classic';
    $quizTitle = $quiz['title'] ?? '';
    $quizDesc = $quiz['description'] ?? '';
    $quizCategory = $quiz['category'] ?? 'miscellaneous';
    $quizTags = $quiz['tags'] ?? '';
    $quizTimer = $quiz['timer'] ?? 60;
} else {
    // If no quiz ID is provided, redirect to profile
    header("Location: " . BASE_URL . "profile.php");
    exit;
}

// Fetch associated quiz questions and answers
$questionsQuery = $conn->prepare("SELECT * FROM `quiz-questions` WHERE quiz_id = ?");
$questionsQuery->bind_param("i", $quizId);
$questionsQuery->execute();
$questionsResult = $questionsQuery->get_result();
$questions = [];

while ($question = $questionsResult->fetch_assoc()) {
    $questionId = $question['id'];
    $answersQuery = $conn->prepare("SELECT * FROM `quiz-answers` WHERE question_id = ?");
    $answersQuery->bind_param("i", $questionId);
    $answersQuery->execute();
    $answersResult = $answersQuery->get_result();

    $answers = [];
    while ($answer = $answersResult->fetch_assoc()) {
        $answers[] = $answer;
    }
    $questions[] = ['question' => $question, 'answers' => $answers];
}

// If form is submitted, update quiz data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $quizType = $_POST['quizType'] ?? 'classic';
    $quizTitle = $_POST['quizTitle'] ?? '';
    $quizDesc = $_POST['quizDesc'] ?? '';
    $quizCategory = $_POST['quizCategory'] ?? 'miscellaneous';
    $quizTags = $_POST['quizTags'] ?? '';
    $quizTimer = isset($_POST['quizTimer']) ? intval($_POST['quizTimer']) : 60; // Ensure it's an integer

    // Update quiz data in the database
    $updateQuiz = $conn->prepare("UPDATE `quiz-quizzes` SET quiz_type = ?, title = ?, description = ?, category = ?, tags = ?, timer = ? WHERE id = ? AND user_id = ?");
    $updateQuiz->bind_param("ssssssii", $quizType, $quizTitle, $quizDesc, $quizCategory, $quizTags, $quizTimer, $quizId, $userId);
    $updateQuiz->execute();

    // Handle updating or deleting questions and answers here if needed

    // Redirect to the profile page after saving
    header("Location: " . BASE_URL . "profile.php?success=quiz_updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz | Narrative Quizzes</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>quiz/css/create-quiz.css">
    <link rel="stylesheet" href="<?php echo BASE_URL?>quiz/css/classic-quiz.css">

</head>
<body>

<div class="quiz-container">
    <?php
    // If user is not logged in, show login prompt
    if (!isset($_SESSION['user_id'])) {
        echo '
            <div class="login-section">
                <h1>Log in to edit a quiz!</h1>
                <a href="' . BASE_URL . 'user_auth.php" class="login-button">Log in</a>
            </div>
        ';
    } else {
    ?>
    <form action="<?php echo BASE_URL ?>quiz/model/save-quiz.php" method="post">
        <h2>Edit Quiz - <?php echo htmlspecialchars($quizTitle); ?></h2>

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
        <input type="text" id="quizTitle" name="quizTitle" class="form-input" placeholder="Enter quiz title"
               value="<?php echo htmlspecialchars($quizTitle); ?>" required>

        <!-- Quiz Description -->
        <label for="quizDesc">Description:</label>
        <textarea id="quizDesc" name="quizDesc" class="form-textarea" rows="3"
                  required><?php echo htmlspecialchars($quizDesc); ?></textarea>

        <!-- Quiz Tags -->
        <label for="quizTags">Tags (comma-separated):</label>
        <input type="text" id="quizTags" name="quizTags" class="form-input"
               placeholder="e.g., trivia, fun, general knowledge" value="<?php echo htmlspecialchars($quizTags); ?>"
               required>

        <div class="content">
            <h2>Edit Quiz - <?php echo htmlspecialchars($quizTitle); ?></h2>
            <input type="hidden" name="quizId" value="<?php echo htmlspecialchars($quizId); ?>">
            <input type="hidden" name="quizTitle" value="<?php echo htmlspecialchars($quizTitle); ?>">
            <input type="hidden" name="quizDesc" value="<?php echo htmlspecialchars($quizDesc); ?>">
            <input type="hidden" name="quizCategory" value="<?php echo htmlspecialchars($quizCategory); ?>">
            <input type="hidden" name="quizTags" value="<?php echo htmlspecialchars($quizTags); ?>">
            <input type="hidden" name="quizType" value="<?php echo htmlspecialchars($quizType); ?>">
            <input type="hidden" name="quizTimer" value="<?php echo htmlspecialchars($quizTimer); ?>">

            <table class="quiz-table">
                <thead id="quizDataHead"></thead>
                <tbody id="quizDataBody">
                <?php foreach ($questions as $index => $questionData): ?>
                    <tr>
                        <td style="display:none;"><?php echo $index + 1; ?></td>

                        <!-- Remove button -->
                        <td>
                            <button type="button" class="btn-remove" onclick="removeRow(this)">✖</button>
                        </td>

                        <!-- Question -->
                        <td><input type="text" name="question[]" class="form-input"
                                   value="<?php echo htmlspecialchars($questionData['question']['question_text']); ?>"
                                   required></td>

                        <!-- Answers -->
                        <td><input type="text" name="answer1[]" class="form-input"
                                   value="<?php echo isset($questionData['answers'][0]['answer_text']) ? htmlspecialchars($questionData['answers'][0]['answer_text']) : ''; ?>"
                                   required></td>

                        <td><input type="text" name="answer2[]" class="form-input"
                                   value="<?php echo isset($questionData['answers'][1]['answer_text']) ? htmlspecialchars($questionData['answers'][1]['answer_text']) : ''; ?>"></td>

                        <td><input type="text" name="answer3[]" class="form-input"
                                   value="<?php echo isset($questionData['answers'][2]['answer_text']) ? htmlspecialchars($questionData['answers'][2]['answer_text']) : ''; ?>"></td>

                        <td><input type="text" name="answer4[]" class="form-input"
                                   value="<?php echo isset($questionData['answers'][3]['answer_text']) ? htmlspecialchars($questionData['answers'][3]['answer_text']) : ''; ?>"></td>


                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <button type="button" class="btn-add" onclick="addRow()">Add Question</button>
            <button type="submit" class="btn-save">Save Quiz</button>
        </div>
    </form>
</div>

</body>
</html>
<?php } ?>