<?php
// Include the database connection
include "../../../config/config.php";

// Ensure a quiz_id is passed via GET
if (!isset($_GET['quiz_id'])) {
    die("Quiz ID is missing. Please create a quiz first.");
}

$quiz_id = $_GET['quiz_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create MCQ Question</title>
</head>
<body>

<h1>Create Multiple-Choice Question for Quiz</h1>
<form action="mcq_process.php" method="POST">
    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">

    <label for="question_text">Question Text:</label><br>
    <textarea id="question_text" name="question_text" rows="4" cols="50" required></textarea><br><br>

    <label for="answer_1">Answer 1:</label><br>
    <input type="text" id="answer_1" name="answer_1" required><br><br>

    <label for="answer_2">Answer 2:</label><br>
    <input type="text" id="answer_2" name="answer_2" required><br><br>

    <label for="answer_3">Answer 3:</label><br>
    <input type="text" id="answer_3" name="answer_3" required><br><br>

    <label for="answer_4">Answer 4:</label><br>
    <input type="text" id="answer_4" name="answer_4" required><br><br>

    <label for="correct_answer">Correct Answer:</label><br>
    <select name="correct_answer" required>
        <option value="1">Answer 1</option>
        <option value="2">Answer 2</option>
        <option value="3">Answer 3</option>
        <option value="4">Answer 4</option>
    </select><br><br>

    <button type="submit">Submit Question</button>
</form>

</body>
</html>
