<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'quiz/views/pause-quiz-modal.php';

// Get quiz ID
$quizId = $_GET['quiz_id'] ?? null;
$quizData = [];
$quizTitle = '';
$quizDesc = '';
$quizCategory = '';
$quizTimer = 0;

// Fetch quiz details
$stmt = $conn->prepare("SELECT id, title, description, category, timer FROM `quiz-quizzes` WHERE id = ?");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$stmt->bind_result($quizId, $quizTitle, $quizDesc, $quizCategory, $quizTimer);
$stmt->fetch();
$stmt->close();

// Fetch questions & answers
$stmt = $conn->prepare("
    SELECT q.id AS question_id, q.question_text, a.id AS answer_id, a.answer_text, a.is_correct
    FROM `quiz-questions` q
    LEFT JOIN `quiz-answers` a ON q.id = a.question_id
    WHERE q.quiz_id = ?
    ORDER BY q.id, a.id
");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$result = $stmt->get_result();

$quizData = [];
while ($row = $result->fetch_assoc()) {
    $qId = $row['question_id'];
    $quizData[$qId]['question'] = $row['question_text'];
    $quizData[$qId]['answers'][] = [
        'id' => $row['answer_id'],
        'text' => $row['answer_text'],
        'is_correct' => $row['is_correct']
    ];
}
$stmt->close();

$questions = array_values($quizData);
$totalQuestions = count($questions);





$leaderboardQuery = $conn->prepare("
    SELECT 
        u.username,
        a.user_id,
        a.score AS best_score,
        a.time_taken AS best_time
    FROM `quiz-attempts` a
    INNER JOIN (
        SELECT user_id, MAX(score) AS max_score
        FROM `quiz-attempts`
        WHERE quiz_id = ?
        GROUP BY user_id
    ) AS max_scores
    ON a.user_id = max_scores.user_id AND a.score = max_scores.max_score
    JOIN users u ON a.user_id = u.user_id
    WHERE a.quiz_id = ?
    GROUP BY a.user_id
    ORDER BY a.score DESC, a.time_taken ASC
    LIMIT 10
");

$leaderboardQuery->bind_param("ii", $quizId, $quizId);
$leaderboardQuery->execute();
$leaderboardResult = $leaderboardQuery->get_result();


$timeStmt = $conn->prepare("SELECT timer FROM `quiz-quizzes` WHERE id = ?");
$timeStmt->bind_param("i", $quizId);
$timeStmt->execute();
$timeStmt->bind_result($quizTimer);
$timeStmt->fetch();
$timeStmt->close();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo ucfirst(str_replace('_', ' ', $quizTitle)); ?> | Multiple Choice Quiz</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>quiz/css/slides-quiz.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL ?>quiz/css/breadcrumbs.css" />
    <style>
        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            gap: 40px;
            padding: 20px;
            flex: 1;
            width: 100%;
        }

        .quiz-info {
            width: 100%; /* Full width */
            text-align: left; /* Left-align the text */
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd; /* Optional: Adds a divider */
        }

        .quiz-info h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .quiz-info p {
            font-size: 1.2rem;
            color: #555;
        }

        .quiz-container {
            flex: 2;
            width: 50rem;
            min-height: 27rem;
            text-align: center;
            display: none; /* Hide the quiz container initially */
        }

        .question-container {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .question {
            font-size: 1.5rem;
            font-weight: bold;
            min-height: 4rem;
        }

        .answer-input {
            padding: 10px;
            font-size: 1rem;
            margin-top: 15px;
        }

        .btn {
            margin: 0 20px;
        }

        .reveal-btn {
            background-color: orange !important;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 10px;
        }

        .prev-btn, .next-btn {
            padding: 10px 15px;
            font-size: 1rem;
            border: none;
            background-color: #007BFF;
            color: #fff;
            cursor: pointer;
            width: 45%;
        }

        .prev-btn:hover, .next-btn:hover {
            background-color: #0056b3;
        }

        .feedback {
            font-size: 1.2rem;
            margin-top: 15px;
        }

        .correct {
            color: green;
        }

        .incorrect {
            color: red;
        }

        .hidden {
            display: none;
        }

        /* Start Quiz button styling */
        .start-quiz-btn {
            padding: 10px 20px;
            font-size: 1.2rem;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .start-quiz-btn:hover {
            background-color: #218838;
        }

        .question-timer-row {
            display: flex;
            width: 100%;
            justify-content: center;
            margin-bottom: 15px;
        }

        .score-timer {
            display: flex;
            justify-content: space-around;
            align-items: center;
            width: 100%;
            max-width: 500px;
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 8px;
            /*border: 2px solid #007BFF;*/
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
        }

        .timer-container, .score-container {
            text-align: center;
        }

        .timer-container p, .score-container p {
            font-size: 1rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        #time-remaining {
            font-size: 2rem;
            font-weight: bold;
            color: #0056b3; /* Red color for urgency */
        }

        #score-tracker {
            font-size: 2rem;
            font-weight: bold;
            color: #0056b3; /* Green color for score */
        }



        /* Results Section */
        #quiz-results {
            display: none;
            text-align: center;
            margin-top: 30px;
            padding: 25px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
        }

        #quiz-results h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Results Section */
        #quiz-results {
            display: none;
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Results Table */
        #results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Remove all borders */
        #results-table th,
        #results-table td {
            padding: 15px;
            text-align: left;
            border: none; /* ✅ Removes table borders */
        }

        /* Header Styling */
        #results-table thead {
            background: #007BFF;
            color: white;
        }

        /* Hover effect */
        #results-table tbody tr:hover {
            background: #f1f1f1;
            transition: 0.3s;
        }

        /* Correct Answers (Green Text) */
        .correct-answer {
            color: #28a745 !important;
            font-weight: bold;
        }

        /* Incorrect or Revealed Answers (Red Text) */
        .incorrect-answer {
            color: #dc3545 !important;
            font-weight: bold;
        }



        /*    LEADERBOARD*/
        #leaderboardContainer {
            margin-top: 30px;
            padding: 15px;
            border-top: 2px solid #ccc;
            width: 70%;
        }

        #leaderboardContainer h2 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        #leaderboardTable {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        #leaderboardTable th, #leaderboardTable td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        #leaderboardTable th {
            background-color: #f4f4f4;
            font-weight: bold;
            color: black;
        }






        /* === Recommended Quizzes Section === */
        #recommended-quizzes {
            margin-top: 60px;
            padding: 0 20px;
        }

        #recommended-quizzes h3 {
            font-size: 24px;
            margin-bottom: 24px;
            color: #333;
            text-align: center;
            font-weight: 700;
        }

        /* Horizontal scroll container */
        .quiz-box-container {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding-bottom: 10px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
        }

        .quiz-box-container::-webkit-scrollbar {
            display: none; /* Chrome, Safari */
        }

        /* Individual quiz cards */
        .quiz-box {
            flex: 0 0 auto;
            width: 260px;
            background-color: #fff;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            scroll-snap-align: start;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .quiz-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        /* Quiz box link wraps full box */
        .quiz-box a {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 20px;
        }

        .choice-btn {
            display: block;
            width: 60%;
            padding: 14px 20px;
            margin: 12px 0;
            font-size: 1.05rem;
            font-weight: 600;
            color: #333;
            background-color: #f0f4f8;
            border: 2px solid #007BFF;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.2s ease;
            text-align: left;
        }

        /* Quiz title */
        .quiz-box h4 {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 12px;
            text-align: left;
            line-height: 1.3;
        }

        /* Quiz description */
        .quiz-box p.quiz-description {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
            margin: 0;
            text-align: left;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

    </style>
</head>
<body>
<nav class="breadcrumbs">
    <a href="<?php echo BASE_URL; ?>">Home</a> &gt;
    <a href="<?php echo BASE_URL; ?>quiz/categories.php">Categories</a> &gt;
    <?php if ($quizCategory): ?>
        <a href="<?php echo BASE_URL; ?>quiz/quiz-search.php?txt-search=&category=<?php echo urlencode($quizCategory); ?>">
            <?php echo htmlspecialchars(ucfirst($quizCategory)); ?>
        </a> &gt;
    <?php endif; ?>
    <span><?php echo htmlspecialchars($quizTitle); ?></span>
</nav>

<main class="main-container">
    <div class="quiz-info">
        <h1><?php echo htmlspecialchars($quizTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars($quizDesc); ?></p>
    </div>

    <button id="start-quiz-btn" class="start-quiz-btn">Start Quiz</button>

    <div class="quiz-container" id="quiz-container">
        <div class="question-timer-row">
            <div class="score-timer">
                <div class="timer-container">
                    <p>Time Left:</p>
                    <span id="time-remaining"><?php echo gmdate("i:s", $quizTimer); ?></span>
                </div>

                <div class="score-container">
                    <p>Score:</p>
                    <span id="score-tracker">0/<?php echo $totalQuestions; ?></span>
                </div>

                <div class="pause-container">
                    <img src="<?php echo BASE_URL ?>public/images/quiz/pause.png" id="pause-btn" class="pause-btn"
                         alt="Pause Button">
                </div>
            </div>
        </div>


        <div class="question-container">
            <div class="question-area">
                <p id="question" class="question"></p>
                <div id="choices-container" class="choices-container"></div>
            </div>
            <div class="feedback" id="feedback"></div>
        </div>

        <div class="button-container">
            <button id="prev-btn" class="prev-btn btn">Previous</button>
            <button id="reveal-answer" class="reveal-btn btn">Reveal Answer</button>
            <button id="next-btn" class="next-btn btn">Next</button>
        </div>
    </div>

    <div id="quiz-results"></div>



    <div id="leaderboardContainer">
        <h2>🏆 Leaderboard</h2>
        <table id="leaderboardTable">
            <thead>
            <tr>
                <th>Rank</th>
                <th>User</th>
                <th>Best Score</th>
                <th>Best Time</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $rank = 1;
            while ($row = $leaderboardResult->fetch_assoc()):
                $minutes = floor($row['best_time'] / 60);
                $seconds = $row['best_time'] % 60;
                $formattedTime = sprintf("%d:%02d", $minutes, $seconds);
                ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo (int) $row['best_score']; ?></td>
                    <td><?php echo $formattedTime; ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>




    <!-- Quizzes from the same category -->
    <div id="recommended-quizzes">
        <h3>If you liked this, you may like these</h3>
        <div class="quiz-box-container">
            <?php
            // Fetch quizzes in the same category with descriptions
            $stmt = $conn->prepare("
            SELECT id, title, description
            FROM `quiz-quizzes`
            WHERE category = ? AND id != ?
            ORDER BY title ASC
            LIMIT 5
        ");
            $stmt->bind_param("si", $quizCategory, $quizId);
            $stmt->execute();
            $result = $stmt->get_result();

            // Display the quizzes in boxes
            while ($row = $result->fetch_assoc()): ?>
                <div class="quiz-box">
                    <a href="<?php echo BASE_URL . 'quiz/quiz.php?quiz_id=' . $row['id']; ?>">
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                        <p class="quiz-description"><?php echo htmlspecialchars($row['description']); ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>



</main>
<script>

    const startQuizBtn = document.getElementById("start-quiz-btn");
    const quizContainer = document.getElementById("quiz-container");
    const questionElement = document.getElementById("question");
    const choicesContainer = document.getElementById("choices-container");
    const feedbackElement = document.getElementById("feedback");
    const prevBtn = document.getElementById("prev-btn");
    const nextBtn = document.getElementById("next-btn");
    const revealBtn = document.getElementById("reveal-answer");
    const scoreTracker = document.getElementById("score-tracker");
    const timerElement = document.getElementById("time-remaining");
    let startTime;

    let currentQuestionIndex = 0;
    let correctTally = 0;
    let timerInterval;
    let timeRemaining = <?php echo $quizTimer; ?>;
    const answeredCorrectly = new Set();
    const revealedAnswers = new Set();
    const skippedQuestions = new Set();
    const userAnswers = {}; // Track user’s first selected answer text per question index

    const questions = <?php echo json_encode($questions); ?>;

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
    }

    shuffleArray(questions);
    questions.forEach(q => shuffleArray(q.answers)); // Optional


    // Function to convert seconds into MM:SS format
    function formatTime(seconds) {
        let minutes = Math.floor(seconds / 60);
        let sec = seconds % 60;
        return `${minutes}:${sec < 10 ? '0' : ''}${sec}`;
    }

    // Function to update the timer display
    function updateTimerDisplay() {
        timerElement.innerText = formatTime(timeRemaining);
    }

    // Function to start the timer
    function startTimer() {
        if (timerInterval) clearInterval(timerInterval); // Prevent multiple intervals
        timerInterval = setInterval(() => {
            if (!isPaused && timeRemaining > 0) {
                timeRemaining--;
                updateTimerDisplay();
            } else if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                showResults(); // Show results when time runs out
            }
        }, 1000);
    }


    startQuizBtn.addEventListener("click", () => {
        startQuizBtn.style.display = "none";
        quizContainer.style.display = "block";
        scoreTracker.textContent = `0/${questions.length}`;
        updateTimerDisplay(); // Show initial timer
        startTimer(); // ⬅️ Start the countdown
        loadQuestion();
        startTime = Date.now(); // Capture start time in ms

    });


    prevBtn.addEventListener("click", () => {
        let found = false;
        for (let i = currentQuestionIndex - 1; i >= 0; i--) {
            if (!answeredCorrectly.has(i) && !revealedAnswers.has(i)) {
                currentQuestionIndex = i;
                found = true;
                break;
            }
        }
        if (found) {
            loadQuestion();
        }
    });


    nextBtn.addEventListener("click", () => {
        if (currentQuestionIndex === questions.length - 1) {
            goToNextOrSkippedOrEnd();
        } else {
            do {
                currentQuestionIndex++;
            } while ((answeredCorrectly.has(currentQuestionIndex) || revealedAnswers.has(currentQuestionIndex)) && currentQuestionIndex < questions.length - 1);
            loadQuestion();
        }
    });

    revealBtn.addEventListener("click", () => {
        revealAnswer();
    });

    function loadQuestion() {
        if (currentQuestionIndex >= questions.length) {
            showResults();
            return;
        }

        if (revealedAnswers.has(currentQuestionIndex)) {
            currentQuestionIndex++;
            loadQuestion();
            return;
        }

        let question = questions[currentQuestionIndex];
        questionElement.textContent = `${currentQuestionIndex + 1}. ${question.question}`;
        feedbackElement.textContent = "";
        feedbackElement.className = "feedback";
        choicesContainer.innerHTML = "";

        question.answers.forEach(answer => {
            const btn = document.createElement("button");
            btn.textContent = answer.text;
            btn.className = "choice-btn";
            btn.addEventListener("click", () => checkAnswer(answer.is_correct, btn, answer.text));
            choicesContainer.appendChild(btn);
        });

        prevBtn.disabled = currentQuestionIndex === 0;
        nextBtn.disabled = answeredCorrectly.has(currentQuestionIndex) || revealedAnswers.has(currentQuestionIndex);
    }

    function checkAnswer(isCorrect, selectedButton, answerText) {
        if (answeredCorrectly.has(currentQuestionIndex) || revealedAnswers.has(currentQuestionIndex)) return;

        // Record user’s first selected answer
        if (!userAnswers.hasOwnProperty(currentQuestionIndex)) {
            userAnswers[currentQuestionIndex] = answerText;
        }

        if (isCorrect) {
            feedbackElement.textContent = "Correct!";
            feedbackElement.classList.add("correct");
            selectedButton.classList.add("correct");
            answeredCorrectly.add(currentQuestionIndex);
            skippedQuestions.delete(currentQuestionIndex);
            correctTally++;
            scoreTracker.textContent = `${correctTally}/${questions.length}`;

            setTimeout(() => {
                goToNextOrSkippedOrEnd();
            }, 1000);

        } else {
            feedbackElement.textContent = "Incorrect. Try again or reveal the answer.";
            feedbackElement.classList.add("incorrect");
            selectedButton.classList.add("incorrect");
            skippedQuestions.add(currentQuestionIndex);
        }
    }

    function revealAnswer() {
        let question = questions[currentQuestionIndex];
        if (!question || revealedAnswers.has(currentQuestionIndex)) return;

        const buttons = choicesContainer.querySelectorAll("button");
        const correctAnswers = question.answers.filter(a => a.is_correct == 1).map(a => a.text);

        feedbackElement.textContent = `Correct answer(s): ${correctAnswers.join(", ")}`;
        feedbackElement.classList.add("incorrect");

        buttons.forEach(btn => {
            if (correctAnswers.includes(btn.textContent)) {
                btn.classList.add("correct");
            }
        });

        revealedAnswers.add(currentQuestionIndex);
        skippedQuestions.add(currentQuestionIndex);

        setTimeout(() => {
            goToNextOrSkippedOrEnd();
        }, 1500);

    }

    function goToNextOrSkippedOrEnd() {
        // Case 1: If user is on the last question
        if (currentQuestionIndex === questions.length - 1) {
            // Go to first unanswered (skipped or incorrect and not revealed)
            for (let i = 0; i < questions.length; i++) {
                if (!answeredCorrectly.has(i) && !revealedAnswers.has(i)) {
                    currentQuestionIndex = i;
                    loadQuestion();
                    return;
                }
            }
            showResults(); // If all are answered or revealed
            return;
        }

        // Case 2: Just go to the next unanswered or un-revealed question from current position
        let nextIndex = currentQuestionIndex + 1;
        while (nextIndex < questions.length) {
            if (!answeredCorrectly.has(nextIndex) && !revealedAnswers.has(nextIndex)) {
                currentQuestionIndex = nextIndex;
                loadQuestion();
                return;
            }
            nextIndex++;
        }

        // If no next unanswered question, show results
        showResults();
    }

    function showResults() {
        quizContainer.style.display = "none";
        const results = document.getElementById("quiz-results");
        results.style.display = "block";

        let html = `<h2>Quiz Results</h2>`;
        html += `<p>You answered ${correctTally} out of ${questions.length} questions correctly.</p>`;
        html += `<table id="results-table"><thead><tr><th>Question</th><th>Your Answer</th><th>Correct Answer</th><th>Status</th></tr></thead><tbody>`;

        questions.forEach((q, i) => {
            const correctAnswerTexts = q.answers
                .filter(a => a.is_correct == 1)
                .map(a => a.text.trim());

            const userAnswer = userAnswers.hasOwnProperty(i) ? userAnswers[i].trim() : "Not Answered";
            const wasRevealed = revealedAnswers.has(i);

            // Determine correctness only based on first answer (and not revealed)
            const isCorrect =
                !wasRevealed &&
                userAnswers.hasOwnProperty(i) &&
                correctAnswerTexts.includes(userAnswer);

            const statusText = isCorrect ? "Correct" : "Incorrect";

            html += `<tr>
            <td>${i + 1}. ${q.question}</td>
            <td>${userAnswer}</td>
            <td>${correctAnswerTexts.join(", ")}</td>
            <td class="${isCorrect ? 'correct-answer' : 'incorrect-answer'}">${statusText}</td>
        </tr>`;
        });

        html += `</tbody></table>`;
        results.innerHTML = html;
        const endTime = Date.now();
        const totalTimeTaken = Math.floor((endTime - startTime) / 1000); // in seconds

    }

    fetch('save_quiz.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            quiz_id: <?php echo json_encode($quizId); ?>,
            score: correctTally,
            time_taken: totalTimeTaken
        })
    })



</script>


</body>
</html>
