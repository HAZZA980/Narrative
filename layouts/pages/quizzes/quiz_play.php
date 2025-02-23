<?php
// Include the database connection
include "../../../config/config.php";

// Step 1: Get the quiz_id from the query string
if (!isset($_GET['quiz_id'])) {
    die("Quiz ID is missing.");
}
$quiz_id = $_GET['quiz_id'];

// Step 2: Fetch all questions for this quiz
$query = "SELECT id, question_text FROM tbl_quiz_questions WHERE quiz_id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    die("Error fetching questions: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz</title>
</head>
<body>

<h1>Take Quiz: Quiz <?php echo $quiz_id; ?></h1>

<form action="quiz_submit.php" method="POST">
    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">

    <?php
    // Step 3: Display the questions for this quiz
    if ($result->num_rows > 0) {
        $question_number = 1;
        while ($row = $result->fetch_assoc()) {
            $question_id = $row['id'];
            $question_text = htmlspecialchars($row['question_text']);
            echo "<p><strong>Question $question_number:</strong> $question_text</p>";

            // Fetch and display answers for this question
            $answer_query = "SELECT id, answer_text FROM tbl_quiz_answers WHERE question_id = ?";
            if ($answer_stmt = $conn->prepare($answer_query)) {
                $answer_stmt->bind_param("i", $question_id);
                $answer_stmt->execute();
                $answer_result = $answer_stmt->get_result();
                $answer_stmt->close();

                echo "<ul>";
                while ($answer_row = $answer_result->fetch_assoc()) {
                    $answer_id = $answer_row['id'];
                    $answer_text = htmlspecialchars($answer_row['answer_text']);
                    echo "<li><input type='radio' name='question_$question_id' value='$answer_id' required> $answer_text</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Error fetching answers for question $question_number.</p>";
            }

            $question_number++;
        }

        // Submit button for the form
        echo "<button type='submit'>Submit Quiz</button>";

    } else {
        echo "<p>No questions available for this quiz.</p>";
    }
    ?>

</form>

</body>
</html>
