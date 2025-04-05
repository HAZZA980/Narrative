<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

$userId = $_SESSION['user_id'] ?? null;
$quizId = $_GET['id'] ?? null;

if (!$userId || !$quizId) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$ownershipCheck = $conn->prepare("SELECT id FROM `quiz-quizzes` WHERE id = ? AND user_id = ?");
$ownershipCheck->bind_param("ii", $quizId, $userId);
$ownershipCheck->execute();
$ownershipResult = $ownershipCheck->get_result();

if ($ownershipResult->num_rows === 0) {
    header("Location: " . BASE_URL . "profile.php?error=unauthorized");
    exit;
}

$conn->begin_transaction();

try {
    $questionIds = [];
    $stmt = $conn->prepare("SELECT id FROM `quiz-questions` WHERE quiz_id = ?");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questionIds[] = $row['id'];
    }

    if (!empty($questionIds)) {
        $ids = implode(',', array_map('intval', $questionIds));
        $conn->query("DELETE FROM `quiz-answers` WHERE question_id IN ($ids)");
        $deleteQuestions = $conn->prepare("DELETE FROM `quiz-questions` WHERE quiz_id = ?");
        $deleteQuestions->bind_param("i", $quizId);
        $deleteQuestions->execute();
    }

    $deleteAttempts = $conn->prepare("DELETE FROM `quiz-attempts` WHERE quiz_id = ?");
    $deleteAttempts->bind_param("i", $quizId);
    $deleteAttempts->execute();

    $deleteStats = $conn->prepare("DELETE FROM `quiz-stats` WHERE quiz_id = ?");
    $deleteStats->bind_param("i", $quizId);
    $deleteStats->execute();

    $deleteQuiz = $conn->prepare("DELETE FROM `quiz-quizzes` WHERE id = ?");
    $deleteQuiz->bind_param("i", $quizId);
    $deleteQuiz->execute();

    $conn->commit();

    $previousPage = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . "profile.php");
    header("Location: " . $previousPage);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error deleting quiz: " . $e->getMessage());
    header("Location: " . BASE_URL . "profile.php");
    exit;
}
?>
