<?php
// quiz-home.php - Main Quiz Homepage
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /../user_auth.php");
    exit();
}
$user_id = $_SESSION['user_id'];
require_once "model/quiz-database.php";
$quizzes = getAllQuizzes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Home</title>
<!--    <link rel="stylesheet" href="/quiz/css/quiz-styles.css">-->
</head>
<body>
<h1>Available Quizzes</h1>
<a href="views/create-quiz.php">Create New Quiz</a>
<div id="quizList">
    <?php if (!empty($quizzes)): ?>
        <ul>
            <?php foreach ($quizzes as $quiz): ?>
                <li>
                    <a href="play-quiz.php?id=<?= $quiz['id'] ?>">
                        <?= htmlspecialchars($quiz['title']) ?>
                    </a>
                    <p>Created by: <?= htmlspecialchars($quiz['creator']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No quizzes available. <a href="create-quiz.php">Create one now!</a></p>
    <?php endif; ?>
</div>
</body>
</html>
