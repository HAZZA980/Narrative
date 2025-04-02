<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Assuming $searchResults is fetched from the database based on the search query
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'includes/quiz-header.php';


// Get quiz ID from URL
$quizId = $_GET['quiz_id'] ?? null;

// Default to classic type
$quizType = 'classic';

// Fetch the first question's type
if ($quizId) {
    $stmt = $conn->prepare("SELECT question_type FROM `quiz-questions` WHERE quiz_id = ? LIMIT 1");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $stmt->bind_result($quizType);
    $stmt->fetch();
    $stmt->close();
}

// Load the correct quiz type
switch ($quizType) {
    case 'slides':
        include 'play-quiz/quiz_slides.php';
        break;
    case 'multiple':
        include 'play-quiz/quiz_multiple.php';
        break;
    default:
        include 'play-quiz/quiz_classic.php';  // Loads the working quiz
        break;
}

