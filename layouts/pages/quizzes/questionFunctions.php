<?php
session_start(); // Start the session to store score data
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/narrative/config/config.php';

// Get category from URL query string, if available
$category = isset($_GET['category']) ? $_GET['category'] : 'random';

// Fetch quiz data from the 'quiz-quizzes' table based on the category
$sql = "SELECT * FROM `quiz-quizzes` WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category); // Bind the category parameter
$stmt->execute();
$result = $stmt->get_result();
$quiz = $result->fetch_assoc(); // Fetch the quiz details

// Fetch questions for the selected quiz from the 'Quiz-Questions' table
$quizId = $quiz['id'];
$sqlQuestions = "SELECT * FROM `Quiz-Questions` WHERE quiz_id = ?";
$stmt = $conn->prepare($sqlQuestions);
$stmt->bind_param("i", $quizId); // Bind the quiz ID parameter
$stmt->execute();
$questionsResult = $stmt->get_result();
$questions = [];

// Store all questions in an array
while ($row = $questionsResult->fetch_assoc()) {
    $questions[] = $row;
}

// Fetch answers for each question from the 'Quiz-Answers' table
foreach ($questions as &$question) {
    $questionId = $question['id'];
    $sqlAnswers = "SELECT * FROM `Quiz-Answers` WHERE question_id = ?";
    $stmt = $conn->prepare($sqlAnswers);
    $stmt->bind_param("i", $questionId); // Bind the question ID parameter
    $stmt->execute();
    $answersResult = $stmt->get_result();

    $answers = [];
    while ($row = $answersResult->fetch_assoc()) {
        $answers[] = $row; // Store answers for each question
    }
    $question['answers'] = $answers; // Add answers to the question
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8'); ?> Quiz</title>
    <script src="trivia-questions.js"></script>
    <link rel="stylesheet" href="../../../quiz/css/slides-quiz.css">
</head>
<body>

<nav class="breadcrumbs">
    <a href="quizzes-home.php">Home</a>
    <span>&gt;</span>
    <a href="quizzes-general-knowledge.php">General Knowledge</a>
    <span>&gt;</span>
    <span>Quiz</span>
</nav>

<main class="main-container">
    <div class="quiz-container">
        <h1><?php echo htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p id="question"></p>
        <form id="quiz-form">
            <input type="text" id="answer-input" placeholder="Type your answer here" autocomplete="off" required>
            <button type="submit">Submit</button>
        </form>
        <p id="feedback-message" class="feedback-message"></p>
        <p id="score-tracker" class="score-tracker">Score: 0/0</p>
    </div>
</main>

<script>
    // Use the questions fetched from the database
    const questions = <?php echo json_encode($questions); ?>;

    let currentQuestionIndex = 0;
    let correctTally = 0;
    const answeredQuestions = [];

    const questionElement = document.getElementById('question');
    const formElement = document.getElementById('quiz-form');
    const answerInput = document.getElementById('answer-input');
    const feedbackMessage = document.getElementById('feedback-message');
    const scoreTracker = document.getElementById('score-tracker');

    const updateScoreTracker = () => {
        scoreTracker.textContent = `Score: ${correctTally}/${questions.length}`;
    };

    const loadQuestion = () => {
        if (currentQuestionIndex < questions.length) {
            const question = questions[currentQuestionIndex];
            questionElement.textContent = `Question ${currentQuestionIndex + 1}: ${question.question_text}`;
            answerInput.value = '';
            feedbackMessage.textContent = '';
        } else {
            displayResults();
        }
        updateScoreTracker();
    };

    const checkAnswer = (userAnswer, correctAnswer) => {
        const normalizedUserAnswer = userAnswer.toLowerCase().trim();
        const normalizedCorrectAnswer = correctAnswer.toLowerCase().trim();

        const correctWords = normalizedCorrectAnswer.split(/\s+/);
        const isPartialMatch = correctWords.some(word => normalizedUserAnswer.includes(word));

        answeredQuestions.push({
            question: questions[currentQuestionIndex].question_text,
            userAnswer: userAnswer,
            correctAnswer: correctAnswer,
            isCorrect: isPartialMatch,
        });

        if (isPartialMatch) {
            correctTally++;
            feedbackMessage.textContent = "Correct!";
            feedbackMessage.className = "feedback-message correct";

            // Move to the next question after a delay
            setTimeout(() => {
                currentQuestionIndex++;
                loadQuestion();
            }, 1000); // 1000ms = 1 second delay
        } else {
            feedbackMessage.textContent = `Incorrect! The correct answer was: ${correctAnswer}`;
            feedbackMessage.className = "feedback-message incorrect";

            // Show next button after incorrect answer
            setTimeout(() => {
                currentQuestionIndex++;
                loadQuestion();
            }, 1000); // 1000ms = 1 second delay
        }
    };

    const displayResults = () => {
        const percentage = (correctTally / questions.length) * 100; // Calculate the percentage
        questionElement.textContent = `You've completed the quiz! Your final score: ${correctTally} out of ${questions.length} (${percentage.toFixed(2)}%)`;

        // Hide the quiz form and score tracker
        formElement.style.display = 'none';
        scoreTracker.style.display = 'none';

        // Hide feedback message
        feedbackMessage.style.display = 'none';

        // Show results table
        const resultsTable = document.createElement('table');
        resultsTable.style.width = '100%';
        resultsTable.style.borderCollapse = 'collapse';
        resultsTable.innerHTML = `
    <thead>
        <tr>
            <th>#</th>
            <th>Question</th>
            <th>Your Answer</th>
            <th>Correct Answer</th>
        </tr>
    </thead>
    <tbody>
        ${answeredQuestions.map(({ question, userAnswer, correctAnswer, isCorrect }, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${question}</td>
                <td style="color: ${isCorrect ? 'green' : 'red'};">${userAnswer}</td>
                <td style="color: green;">${correctAnswer}</td>
            </tr>
        `).join('')}
    </tbody>
    `;
        document.querySelector('.quiz-container').appendChild(resultsTable);

        // Add "Return to Homepage" button
        const returnButton = document.createElement('button');
        returnButton.textContent = "Return to Homepage";
        returnButton.style.marginTop = '20px';
        returnButton.style.padding = '10px 20px';
        returnButton.style.fontSize = '1rem';
        returnButton.style.border = 'none';
        returnButton.style.backgroundColor = '#007BFF';
        returnButton.style.color = '#fff';
        returnButton.style.cursor = 'pointer';

        returnButton.addEventListener('click', () => {
            window.location.href = 'quizzes-home.php';
        });

        document.querySelector('.quiz-container').appendChild(returnButton);
    };

    formElement.addEventListener('submit', (event) => {
        event.preventDefault();
        const userAnswer = answerInput.value.trim();
        const correctAnswer = questions[currentQuestionIndex].answers.find(ans => ans.is_correct === 1).answer_text;
        checkAnswer(userAnswer, correctAnswer);
    });

    loadQuestion();

</script>

</body>
</html>
