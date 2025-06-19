<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'quiz/views/pause-quiz-modal.php';

// Get quiz ID from URL
$quizId = $_GET['quiz_id'] ?? null;

$quizData = [];
$quizCategory = null;
$quizTitle = '';
$quizDesc = '';

// Fetch quiz data including timer
$stmt = $conn->prepare("
    SELECT id, title, description, category, timer FROM `quiz-quizzes` WHERE id = ?
");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$stmt->bind_result($quizId, $quizTitle, $quizDesc, $quizCategory, $quizTimer); // Added $quizTimer
$stmt->fetch();
$stmt->close();


$quizData = [];
// Fetch questions and answers
if ($quizId) {
    $stmt = $conn->prepare("
        SELECT q.id AS question_id, q.question_text, a.answer_text, a.is_correct
        FROM `quiz-questions` q
        LEFT JOIN `quiz-answers` a ON q.id = a.question_id
        WHERE q.quiz_id = ?
    ");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $questionId = $row['question_id'];
        $quizData[$questionId]['question'] = $row['question_text'];
        $quizData[$questionId]['answers'][] = [
            'text' => strtolower($row['answer_text']),
            'is_correct' => $row['is_correct']
        ];
    }
    $stmt->close();
}

// Set the total number of questions
$totalQuestions = count($quizData);
$questions = array_values($quizData);





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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst(str_replace('_', ' ', $quizTitle)); ?> | Slides Quiz</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>quiz/css/slides-quiz.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>quiz/css/breadcrumbs.css">
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





        /* Container for recommended quizzes */
        #recommended-quizzes {
            margin-top: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
        }

        #recommended-quizzes h3 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
            font-weight: bold;
        }

        /* Container for the quiz boxes */
        .quiz-box-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            justify-items: center;
            margin-top: 10px;
        }

        /* Individual quiz box */
        .quiz-box {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 300px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Hover effect for quiz boxes */
        .quiz-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        /* Title styling */
        .quiz-box h4 {
            font-size: 18px;
            color: #2c3e50;
            margin: 15px;
            font-weight: 600;
            text-align: center;
            line-height: 1.3;
        }

        /* Description styling */
        .quiz-box p.quiz-description {
            font-size: 14px;
            color: #555;
            margin: 0 15px 15px;
            line-height: 1.5;
            text-align: center;
            height: 60px; /* Fixed height for description */
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Link styling */
        .quiz-box a {
            display: block;
            text-decoration: none;
        }

        /* Hover effect for quiz box link */
        .quiz-box a:hover h4 {
            color: #1abc9c;
        }

        .quiz-box a:hover p.quiz-description {
            color: #2c3e50;
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
    <!-- Quiz Info (Now positioned to the left) -->
    <div class="quiz-info">
        <h1><?php echo htmlspecialchars($quizTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars($quizDesc) ?></p>
    </div>

    <button id="start-quiz-btn" class="start-quiz-btn">Start Quiz</button>

    <!-- Quiz Question Area (Initially hidden) -->
    <div class="quiz-container" style="display:none;">
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

        <div class="question-area">
            <p id="question" class="question"></p>
        </div>
        <input type="text" id="answer-input" class="answer-input" placeholder="Type your answer" autocomplete="off">

        <div class="button-container">
            <button id="prev-btn" class="prev-btn btn">Previous</button>
            <button id="reveal-answer" class="reveal-btn btn">Reveal Answer</button>
            <button id="next-btn" class="next-btn btn">Next</button>
        </div>

        <p id="feedback" class="feedback"></p>
    </div>

    <!-- Results Table (Initially hidden) -->
    <div id="quiz-results" style="display:none;">
        <h2>Quiz Results</h2>
        <table id="results-table" border="1" style="width:100%; text-align:left; margin-top:20px;">
            <thead>
            <tr>
                <th>Question</th>
                <th>Answer</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>



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
    document.addEventListener("DOMContentLoaded", function () {
        const questions = <?php echo json_encode($questions); ?>;
        const questionElement = document.getElementById("question");
        const answerInput = document.getElementById("answer-input");
        const prevBtn = document.getElementById("prev-btn");
        const nextBtn = document.getElementById("next-btn");
        const feedbackElement = document.getElementById("feedback");
        const scoreTracker = document.getElementById("score-tracker");
        const timerElement = document.getElementById("time-remaining");
        const startQuizBtn = document.getElementById("start-quiz-btn");
        const revealAnswerBtn = document.getElementById("reveal-answer");
        const resultsTableBody = document.querySelector('#results-table tbody');
        const quizResultsDiv = document.getElementById('quiz-results');

        let revealedQuestions = [];
        let currentQuestionIndex = 0;
        let correctTally = 0;
        let answeredCorrectly = [];
        let timerInterval;
        let timeRemaining = <?php echo $quizTimer; ?>;
        let isPaused = false;

        // Function to shuffle questions using Fisher-Yates algorithm
        function shuffleQuestions(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

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

        // Start Quiz functionality
        startQuizBtn.addEventListener("click", () => {
            startQuizBtn.style.display = "none"; // Hide the start button
            document.querySelector('.quiz-container').style.display = "block"; // Show the quiz container

            shuffleQuestions(questions); // Shuffle questions before starting
            startTimer(); // Start the timer
            loadQuestion(); // Load the first question
        });

        // Load the first question
        function loadQuestion() {
            if (currentQuestionIndex >= questions.length) {
                showResults();
                return;
            }

            let question = questions[currentQuestionIndex];
            questionElement.textContent = `${currentQuestionIndex + 1}. ${question.question}`;
            answerInput.value = "";
            feedbackElement.textContent = "";
            feedbackElement.className = "feedback";

            prevBtn.disabled = currentQuestionIndex === 0 || answeredCorrectly.includes(currentQuestionIndex);
            nextBtn.disabled = answeredCorrectly.includes(currentQuestionIndex);
        }

        // Check if the user's answer is correct
        function checkAnswer() {
            let userAnswer = answerInput.value.trim().toLowerCase();
            let correctAnswers = questions[currentQuestionIndex].answers.filter(a => a.is_correct === 1).map(a => a.text);

            if (correctAnswers.includes(userAnswer)) {
                feedbackElement.textContent = "Correct!";
                feedbackElement.classList.add("correct");
                correctTally++;
                answeredCorrectly.push(currentQuestionIndex);
                scoreTracker.textContent = `Score: ${correctTally}/${questions.length}`;

                setTimeout(() => {
                    // If not last question, move to next question normally
                    if (currentQuestionIndex < questions.length - 1) {
                        currentQuestionIndex++;
                        // Skip already answered questions
                        while (answeredCorrectly.includes(currentQuestionIndex) && currentQuestionIndex < questions.length) {
                            currentQuestionIndex++;
                        }
                        loadQuestion();
                    } else {
                        // If last question, check for skipped questions
                        let nextUnanswered = null;
                        for (let i = 0; i < questions.length; i++) {
                            if (!answeredCorrectly.includes(i)) {
                                nextUnanswered = i;
                                break;
                            }
                        }

                        if (nextUnanswered !== null) {
                            currentQuestionIndex = nextUnanswered;
                            loadQuestion();
                        } else {
                            // No skipped questions left, show results
                            showResults();
                        }
                    }
                }, 1000);

            } else {
                feedbackElement.classList.add("incorrect");
            }
        }


        function submitQuizResults() {
            const totalTime = <?php echo (int) $quizTimer; ?>; // in seconds
            const timeTaken = totalTime - timeRemaining;       // ✅ calculate time used

            const data = {
                quiz_id: <?php echo (int) $quizId; ?>,
                score: correctTally,
                time_taken: timeTaken
            };

            fetch('<?php echo BASE_URL; ?>quiz/model/submit-quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            })
                .then(res => res.json())
                .then(data => console.log('Quiz saved:', data))
                .catch(err => console.error('Save error:', err));
        }


// Call submitQuizResults() after showing results
        function showResults() {
            document.querySelector('.quiz-container').style.display = "none";
            quizResultsDiv.style.display = "block";

            resultsTableBody.innerHTML = ""; // Clear previous results

            questions.forEach((question, index) => {
                let row = document.createElement('tr');
                let questionCell = document.createElement('td');
                let answerCell = document.createElement('td');

                questionCell.textContent = question.question;

                let correctAnswers = question.answers.filter(a => a.is_correct === 1).map(a => a.text);
                let answerText = correctAnswers.join(', ');

                if (answeredCorrectly.includes(index) && !revealedQuestions.includes(index)) {
                    answerCell.classList.add("correct-answer"); // Green only if correct and not revealed
                } else {
                    answerCell.classList.add("incorrect-answer"); // Red if wrong or revealed
                }

                answerCell.textContent = answerText;
                row.appendChild(questionCell);
                row.appendChild(answerCell);
                resultsTableBody.appendChild(row);
            });

            // Call submitQuizResults after showing results
            submitQuizResults();
        }



        // Reveal answer functionality
        function revealAnswer() {
            let correctAnswers = questions[currentQuestionIndex].answers
                .filter(a => a.is_correct === 1)
                .map(a => a.text);

            feedbackElement.textContent = `The correct answer(s): ${correctAnswers.join(', ')}`;
            feedbackElement.classList.add("incorrect");

            // Mark this question as revealed
            if (!revealedQuestions.includes(currentQuestionIndex)) {
                revealedQuestions.push(currentQuestionIndex);
            }

            // Prevent going back to this question
            if (!answeredCorrectly.includes(currentQuestionIndex)) {
                answeredCorrectly.push(currentQuestionIndex);
            }

            setTimeout(() => {
                for (let i = currentQuestionIndex + 1; i < questions.length; i++) {
                    if (!answeredCorrectly.includes(i)) {
                        currentQuestionIndex = i;
                        loadQuestion();
                        return;
                    }
                }

                for (let i = 0; i < questions.length; i++) {
                    if (!answeredCorrectly.includes(i)) {
                        currentQuestionIndex = i;
                        loadQuestion();
                        return;
                    }
                }

                showResults();
            }, 1000);
        }




        // Listen for user input to check answers
        answerInput.addEventListener("input", checkAnswer);
        nextBtn.addEventListener("click", () => {
            const totalQuestions = questions.length;

            // Case 1: Still more questions ahead
            for (let i = currentQuestionIndex + 1; i < totalQuestions; i++) {
                if (!answeredCorrectly.includes(i)) {
                    currentQuestionIndex = i;
                    loadQuestion();
                    return;
                }
            }

            // Case 2: We're at or near the end and there are skipped questions earlier
            for (let i = 0; i < totalQuestions; i++) {
                if (!answeredCorrectly.includes(i)) {
                    currentQuestionIndex = i;
                    loadQuestion();
                    return;
                }
            }

            // Case 3: All questions have been answered
            showResults();
        });

        prevBtn.addEventListener("click", () => {
            let found = false;
            for (let i = currentQuestionIndex - 1; i >= 0; i--) {
                if (!answeredCorrectly.includes(i)) {
                    currentQuestionIndex = i;
                    found = true;
                    break;
                }
            }

            if (found) {
                loadQuestion();
            } else {
                feedbackElement.textContent = "No skipped questions before this.";
                feedbackElement.className = "feedback";
            }
        });


        revealAnswerBtn.addEventListener("click", revealAnswer);
    });


</script>


</body>
</html>
