<?php
// Include the database connection
include "../../../config/config.php";

// Step 1: Ensure quiz_id is present
if (!isset($_POST['quiz_id'])) {
    die("Quiz ID is missing.");
}
$quiz_id = $_POST['quiz_id'];

// Step 2: Initialize the score
$score = 0;

// Step 3: Loop through all the submitted answers
foreach ($_POST as $key => $value) {
    // Skip the quiz_id field
    if ($key == 'quiz_id') {
        continue;
    }

    // Check if the answer is correct
    $question_id = substr($key, 9); // Extract question_id from the key (e.g., question_1, question_2)
    $answer_id = $value;

    // Fetch the correct answer for this question
    $query = "SELECT is_correct FROM tbl_quiz_answers WHERE id = ? AND question_id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ii", $answer_id, $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $answer_row = $result->fetch_assoc();
            if ($answer_row['is_correct'] == 1) {
                $score++; // Increment score if the answer is correct
            }
        }
    }
}

// Step 4: Display the result
echo "<h1>Quiz Results</h1>";
echo "<p>You scored $score out of " . count($_POST) - 1 . " questions.</p>";

// Optionally, you can redirect to another page or save the score to the database

?>

