<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/phpProjects/narrative/config/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'] ?? 1; // Use logged-in user or default to 1
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $tags = $_POST['tags'];
    $quizType = $_POST['quizType'];

    // Insert quiz
    $stmt = $conn->prepare("INSERT INTO `quiz-quizzes` (user_id, title, description, category, tags) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $title, $description, $category, $tags);
    $stmt->execute();
    $quiz_id = $stmt->insert_id; // Get new quiz ID
    $stmt->close();

    // Insert questions & answers
    foreach ($_POST['question'] as $index => $question_text) {
        $stmt = $conn->prepare("INSERT INTO `quiz-questions` (quiz_id, question_text, question_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $quiz_id, $question_text, $quizType);
        $stmt->execute();
        $question_id = $stmt->insert_id; // Get question ID
        $stmt->close();

        // Insert answers
        for ($i = 1; $i <= 4; $i++) {
            $answer_text = $_POST["answer{$i}"][$index];
            $is_correct = isset($_POST['correct_answer'][$index]) && $_POST['correct_answer'][$index] === "answer{$i}" ? 1 : 0;

            $stmt = $conn->prepare("INSERT INTO `quiz-answers` (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $question_id, $answer_text, $is_correct);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo "Quiz saved successfully!";
} else {
    echo "Invalid request!";
}
?>
