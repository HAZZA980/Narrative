<?php
// Include the database connection
include "../../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Validate the form data
    $quiz_id = $_POST['quiz_id'];
    $question_text = trim($_POST['question_text']);
    $answers = [
        1 => trim($_POST['answer_1']),
        2 => trim($_POST['answer_2']),
        3 => trim($_POST['answer_3']),
        4 => trim($_POST['answer_4']),
    ];
    $correct_answer = $_POST['correct_answer'];

    // Ensure all answers are filled
    if (empty($question_text) || empty($answers[1]) || empty($answers[2]) || empty($answers[3]) || empty($answers[4])) {
        die("All fields are required.");
    }

    // Step 2: Insert the question into tbl_quiz_questions
    $query = "INSERT INTO tbl_quiz_questions (quiz_id, question_text, question_type) VALUES (?, ?, 'MCQ')";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("is", $quiz_id, $question_text);
        if ($stmt->execute()) {
            $question_id = $stmt->insert_id; // Get the last inserted question ID
        } else {
            die("Error inserting question: " . $stmt->error);
        }
        $stmt->close();
    }

    // Step 3: Insert the answers into tbl_quiz_answers
    $correct_answer_index = 0;
    foreach ($answers as $index => $answer) {
        $is_correct = ($index == $correct_answer) ? 1 : 0;

        $query = "INSERT INTO tbl_quiz_answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("isi", $question_id, $answer, $is_correct);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Step 4: Redirect to the quiz page (or any page you want)
    header("Location: quiz_details.php?quiz_id=$quiz_id");
    exit();
}
