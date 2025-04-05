<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'quiz/views/pause-quiz-modal.php';

// Get quiz ID from URL
$quizId = $_GET['quiz_id'] ?? null;

$quizData = [];
$quizCategory = null;
$quizTimer = 30; // Default value for timer (in minutes)

// Fetch quiz data (quiz title, category, and timer)
$stmt = $conn->prepare("SELECT id, title, category, timer FROM `quiz-quizzes` WHERE id = ?");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$stmt->bind_result($fetchedQuizId, $quizTitle, $quizCategory, $quizTimer);
$stmt->fetch();
$stmt->close();

// Ensure quiz exists
if ($fetchedQuizId) {
    // Fetch questions and their correct answers (filtered to not include NULL answers)
    $stmt = $conn->prepare("
        SELECT q.id AS question_id, q.question_text, a.answer_text
        FROM `quiz-questions` q
        LEFT JOIN `quiz-answers` a 
            ON q.id = a.question_id AND a.is_correct = 1
        WHERE q.quiz_id = ?
        ORDER BY q.id ASC
    ");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $questionId = $row['question_id'];

        // Initialize the question if it doesn't exist yet
        if (!isset($quizData[$questionId])) {
            $quizData[$questionId] = [
                'question' => $row['question_text'],
                'answers' => []
            ];
        }

        // Only add the answer if it's not null (and not empty)
        if (!empty($row['answer_text'])) {
            $quizData[$questionId]['answers'][] = strtolower($row['answer_text']);
        }
    }

    $stmt->close();
}

var_dump($quizData);


// Convert $quizData to a sequential array of questions for rendering
$questions = array_values($quizData);
$totalQuestions = count($questions);
$columns = 2;

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


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst(str_replace('_', ' ', $quizTitle)); ?> Quiz | Narrative</title>
    <link rel="stylesheet" href="<?php echo BASE_URL?>quiz/css/classic-quiz.css">
    <link rel="stylesheet" href="<?php echo BASE_URL?>quiz/css/breadcrumbs.css">
    <style>
        .question { font-weight: bold; }
        .answer { font-style: italic; color: green; visibility: hidden; }

        #pauseButton {
            display: none; /* Hide until quiz starts */
        }

        #startButton, #repeatButton, #giveUpButton {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
            border: none;
            font-size: 16px;
            cursor: pointer;
        }

        #repeatButton, #giveUpButton {
            display: none;
        }

        #modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px;
            color: white;
            text-align: center;
            display: none;
            z-index: 9999;
        }

        #modal.active {
            display: block;
        }

        #modal .btn {
            background-color: #3498db;
            padding: 10px 20px;
            margin-top: 10px;
            border: none;
            cursor: pointer;
        }

        /* Update the #topControls to use Flexbox */
        #topControls {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Add styling to align the answer input properly */
        #answerInput {
            display: none; /* Hide initially */
            margin-left: 10px;
            padding: 5px;
            font-size: 16px;
            width: 60%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        /* Adjust display when quiz is started */
        #answerInput.active {
            display: inline-block;
        }






    /*    LEADERBOARD*/
        #leaderboardContainer {
            margin-top: 30px;
            padding: 15px;
            border-top: 2px solid #ccc;
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
    <div class="main-content">
        <div id="quizContainer">
            <h1><?php echo htmlspecialchars($quizTitle); ?> Quiz</h1>

            <div id="topControls">
                <button id="startButton">Start Quiz</button>
                <div class="pause-container" id="pauseButton">
                    <img src="<?php echo BASE_URL ?>public/images/quiz/pause.png" id="pause-btn" class="pause-btn"
                         alt="Pause Button">
                </div>
                <input type="text" id="answerInput" autocomplete="off" placeholder="Type your answer here...">
                <div id="finalScore" style="display: none; font-size: 18px; font-weight: bold;"></div>
                <div id="rightControls">
                    <div id="timer">30:00</div>
                    <div class="score-container">
                        <div id="score">0/<span id="totalQuestions"><?php echo $totalQuestions; ?></span></div>
                    </div>
                    <button id="restartButton" style="display:none;" onclick="restartQuiz()">Restart</button>
                    <button id="giveUpButton" style="display:none;" onclick="giveUp()">Give Up</button>
                </div>

            </div>

            <div id="quizTableContainer">
                <table id="quizTable">
                    <?php

                    for ($i = 0; $i < ceil($totalQuestions / $columns); $i++) {
                        echo "<tr>";
                        for ($j = 0; $j < $columns; $j++) {
                            $index = $i * $columns + $j;
                            if (isset($questions[$index])) {
                                echo "<td class='question'>{$questions[$index]['question']}</td>";
                                echo "<td class='answer' id='answer_$index' data-answers='" . json_encode($questions[$index]['answers'], JSON_HEX_APOS | JSON_HEX_QUOT) . "'></td>";
                            } else {
                                echo "<td></td><td></td>";
                            }
                        }
                        echo "</tr>";
                    }
                    ?>
                </table>

            </div>
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



        <script>
            // Global variables
            let quizPaused = false;
            let quizStarted = false;
            let timer;
            let score = 0;
            let totalQuestions = <?php echo $totalQuestions; ?>;
            let answeredQuestions = new Set(); // To track answered questions
            const quizDuration = <?php echo (int) $quizTimer; ?>; // in seconds

            // Get timer from PHP (Assuming timer is stored as seconds in the database)
            let timeLeft = <?php echo $quizTimer; ?>; // This value is already in seconds (e.g., 600 seconds)
            let isPaused = false;



            // Convert seconds into minutes for display (600 seconds -> 10 minutes)
            timeLeft = timeLeft;

            // Event listeners for quiz control buttons
            document.getElementById('pauseButton').onclick = function () {
                pauseQuiz();
            };

            document.getElementById('startButton').onclick = function () {
                startQuiz();
            };

            document.getElementById('giveUpButton').onclick = function () {
                giveUp();
            };

            document.getElementById('restartButton').onclick = function () {
                restartQuiz();
            };

            // Start the quiz and timer
            function startQuiz() {
                if (!quizStarted) {
                    quizStarted = true;
                    document.getElementById('startButton').style.display = 'none';
                    document.getElementById('pauseButton').style.display = 'flex'; // show pause button
                    document.getElementById('answerInput').style.display = 'inline-block';
                    document.getElementById('giveUpButton').style.display = 'block';
                    startTimer();
                }
            }


            function submitQuizResults() {
                const data = {
                    quiz_id: <?php echo (int) $quizId; ?>,
                    score: score,
                    time_taken: quizDuration - timeLeft // time used in seconds
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



            // Timer functionality
            function startTimer() {
                timer = setInterval(function () {
                    if (!quizPaused) {
                        timeLeft--;
                        updateTimerDisplay();
                        if (timeLeft <= 0 || score === totalQuestions) {
                            endQuiz();
                        }
                    }
                }, 1000);
            }

            // Stop the timer
            function stopTimer() {
                clearInterval(timer);
            }


            // Function to pause the quiz
            function pauseQuiz() {
                quizPaused = true;  // Set quiz state to paused
                stopTimer();  // Stop the timer

                // Show the pause modal
                document.getElementById('pause-modal').classList.remove('hidden');

                // Display the remaining time in the pause modal
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                document.getElementById('paused-time').textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            }

            // Function to resume the quiz
            function resumeQuiz() {
                quizPaused = false;  // Set quiz state to active
                startTimer();  // Restart the timer

                // Hide the pause modal
                document.getElementById('pause-modal').classList.add('hidden');
            }

            // Event listener for the play button in the pause modal to resume the quiz
            document.getElementById('play-btn').onclick = function () {
                resumeQuiz();
            };


            function endQuiz() {
                stopTimer(); // Stop the countdown
                submitQuizResults(); // Save the results

                // Reveal all answers and mark correct/incorrect
                document.querySelectorAll('.answer').forEach((answer, index) => {
                    const answers = JSON.parse(answer.getAttribute('data-answers'));
                    answer.textContent = answers[0]; // Show the first correct answer
                    answer.style.visibility = 'visible';

                    // Mark styling based on whether it was answered
                    if (answeredQuestions.has(index)) {
                        answer.classList.add('correct');
                    } else {
                        answer.classList.add('incorrect');
                    }
                });

                // Hide the answer input
                document.getElementById('answerInput').style.display = 'none';
                document.getElementById('pauseButton').style.display = 'none';

                // Show final score
                const finalScoreEl = document.getElementById('finalScore');
                finalScoreEl.textContent = `💯 Your Final Score: ${score}/${totalQuestions} (${Math.round((score / totalQuestions) * 100)}%)`;
                finalScoreEl.style.display = 'block';

                // Adjust button visibility
                document.getElementById('pauseButton').style.display = 'none';
                document.getElementById('giveUpButton').style.display = 'none';
                document.getElementById('restartButton').style.display = 'inline';
            }



            // Update the timer display function
            function updateTimerDisplay() {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                document.getElementById('timer').innerHTML = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            }

            function giveUp() {
                clearInterval(timer);
                submitQuizResults();

                // Reveal all the answers
                document.querySelectorAll('.answer').forEach((answer, index) => {
                    const answers = JSON.parse(answer.getAttribute('data-answers'));

                    // Show answer
                    answer.textContent = answers.join(', ');
                    answer.style.visibility = 'visible';

                    // If question wasn't answered, mark it in red
                    if (!answeredQuestions.has(index)) {
                        answer.classList.add('incorrect');
                    } else {
                        answer.classList.add('correct');
                    }
                });

                // Hide input field and show final score
                document.getElementById('answerInput').style.display = 'none';
                const finalScoreEl = document.getElementById('finalScore');
                finalScoreEl.textContent = `Your Final Score: ${score}/${totalQuestions}`;
                finalScoreEl.style.display = 'block';

                // Hide Give Up, show Restart
                document.getElementById('giveUpButton').style.display = 'none';
                document.getElementById('restartButton').style.display = 'inline';
            }


            // Restart the quiz
            function restartQuiz() {
                location.reload(); // Refresh the page
            }

            // Handle user input and check answers
            document.getElementById('answerInput').onkeyup = function (event) {
                checkAnswer(event.target.value.trim().toLowerCase());
            };

            // Check if the answer is correct
            function checkAnswer(userAnswer) {
                const answerElements = document.querySelectorAll(".answer");

                answerElements.forEach((answerElement, index) => {
                    // Skip if the question has already been answered correctly
                    if (answeredQuestions.has(index)) {
                        return;
                    }

                    // Retrieve the correct answers from the data-answers attribute
                    const correctAnswers = JSON.parse(answerElement.getAttribute("data-answers"));

                    // Compare the user input with the correct answers
                    if (correctAnswers.some(answer => answer.toLowerCase().trim() === userAnswer)) {
                        answerElement.textContent = correctAnswers[0]; // Set the first correct answer
                        answerElement.style.visibility = 'visible';  // Make it visible
                        answerElement.style.opacity = 1;  // Ensure it's visible

                        // Mark as answered correctly
                        answeredQuestions.add(index);

                        // Update score
                        score++;
                        document.getElementById('score').textContent = `${score}/${totalQuestions}`;
                        document.getElementById('answerInput').value = '';  // Clear input field
                    }
                });
            }

            // Initialize the timer on page load
            window.onload = function () {
                initializeTimerDisplay();
            }

            // Function to initialize timer display (initial)
            function initializeTimerDisplay() {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                document.getElementById('timer').innerHTML = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            }
        </script>


    </div>
</main>
</body>
</html>