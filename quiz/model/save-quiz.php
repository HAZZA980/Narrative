<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'] ?? 1; // Use logged-in user or default to 1
    $quizType = $_POST['quizType'] ?? 'classic'; // Default to 'classic' if not set
    $title = $_POST['quizTitle'] ?? 'Untitled Quiz'; // Use provided title or fallback to default
    $description = $_POST['quizDesc'] ?? 'No description'; // Fallback to a default description
    $category = $_POST['quizCategory'] ?? 'miscellaneous'; // Fallback to 'miscellaneous' if not selected
    $tags = $_POST['quizTags'] ?? ''; // Fallback to empty string if no tags provided
    $quizTimer = intval($_POST['quizTimer'] ?? 60); // ✅ Ensure integer value for timer


    // Insert the quiz into `quiz-quizzes`
    $stmt = $conn->prepare("INSERT INTO `quiz-quizzes` (user_id, title, description, category, tags, timer, quiz_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssis", $user_id, $title, $description, $category, $tags, $quizTimer, $quizType);
    $stmt->execute();
    $quiz_id = $stmt->insert_id; // Get the new quiz ID
    $stmt->close();

    // Insert questions and answers
    foreach ($_POST['question'] as $index => $question_text) {
        if (is_array($question_text)) {
            continue; // Skip if it's an array instead of a string
        }

        $question_text = trim($question_text); // Ensure it's a string before trimming
        if (empty($question_text)) continue; // Skip empty questions

        $stmt = $conn->prepare("INSERT INTO `quiz-questions` (quiz_id, question_text, question_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $quiz_id, $question_text, $quizType);
        $stmt->execute();
        $question_id = $stmt->insert_id; // Get the question ID
        $stmt->close();

        // Insert answers
        for ($i = 1; $i <= 4; $i++) {
            if (!isset($_POST["answer{$i}"][$index]) || empty(trim($_POST["answer{$i}"][$index]))) {
                continue; // Skip empty answers
            }

            $answer_text = $_POST["answer{$i}"][$index];
            $is_correct = 0; // Default to incorrect

            // If the quiz is clickable, set only the selected answer as correct
            if ($quizType === "multiple-choice") {
                $is_correct = ($_POST['correct_answer'][$index] === "answer{$i}") ? 1 : 0;
            } else {
                // For non-clickable quizzes, every answer with text is considered correct
                $is_correct = 1;
            }

            $stmt = $conn->prepare("INSERT INTO `quiz-answers` (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $question_id, $answer_text, $is_correct);
            $stmt->execute();
            $stmt->close();
        }
    }

//    // Clear session data after quiz is successfully saved
//    session_unset(); // Unset all session variables
//    session_destroy(); // Optionally destroy the session to clear everything

    // Notify user and redirect to home page
    echo "<script>
        alert('Quiz created successfully!');
        window.location.href = '../home.php';
    </script>";
} else {
    echo "<script>
        alert('Invalid request!');
        window.location.href = '../home.php';
    </script>";
}
?>
