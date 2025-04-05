<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !isset($_POST['quiz_id'], $_POST['score'], $_POST['time_taken'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$quizId = (int) $_POST['quiz_id'];
$score = (int) $_POST['score'];
$timeTaken = (int) $_POST['time_taken'];
$now = date('Y-m-d H:i:s');

// 1. Insert attempt
$stmt = $conn->prepare("
    INSERT INTO `quiz-attempts` (user_id, quiz_id, score, time_taken, attempted_at)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iiiis", $userId, $quizId, $score, $timeTaken, $now);
$stmt->execute();
$stmt->close();

// 2. Update or insert stats
$stmt = $conn->prepare("
    SELECT best_score, worst_score, best_time
    FROM `quiz-stats`
    WHERE user_id = ? AND quiz_id = ?
");
$stmt->bind_param("ii", $userId, $quizId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $bestScore = $row['best_score'];
    $worstScore = $row['worst_score'];
    $bestTime = $row['best_time'];

    // Update if the new score is better
    if ($score > $row['best_score']) {
        $bestScore = $score;
        $bestTime = $timeTaken;
    } elseif ($score == $row['best_score']) {
        $bestScore = $score;
        $bestTime = min($row['best_time'], $timeTaken);
    } else {
        $bestScore = $row['best_score'];
        $bestTime = $row['best_time'];
    }


    $worstScore = min($worstScore, $score);

    $updateStmt = $conn->prepare("
        UPDATE `quiz-stats`
        SET best_score = ?, worst_score = ?, best_time = ?, last_played = ?
        WHERE user_id = ? AND quiz_id = ?
    ");
    $updateStmt->bind_param("iiiisi", $bestScore, $worstScore, $bestTime, $now, $userId, $quizId);
    $updateStmt->execute();
    $updateStmt->close();

} else {
    // Insert new stats
    $insertStats = $conn->prepare("
        INSERT INTO `quiz-stats` (user_id, quiz_id, best_score, worst_score, best_time, last_played)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insertStats->bind_param("iiiiss", $userId, $quizId, $score, $score, $timeTaken, $now);
    $insertStats->execute();
    $insertStats->close();
}

echo json_encode(['status' => 'success']);
?>
