<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Get quiz ID from URL
$quizId = $_GET['quiz_id'] ?? null;

$quizData = [];
$quizCategory = null;

// Fetch quiz data
$stmt = $conn->prepare("SELECT id, title, category FROM `quiz-quizzes` WHERE id = ?");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$stmt->bind_result($quizId, $quizTitle, $quizCategory);
$stmt->fetch();
$stmt->close();

if ($quizId) {
    $stmt = $conn->prepare("
        SELECT q.id AS question_id, q.question_text, a.answer_text 
        FROM `quiz-questions` q
        LEFT JOIN `quiz-answers` a ON q.id = a.question_id AND a.is_correct = 1
        WHERE q.quiz_id = ?
    ");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $questionId = $row['question_id'];
        $quizData[$questionId]['question'] = $row['question_text'];
        $quizData[$questionId]['answers'][] = strtolower($row['answer_text']); // Store correct answers as lowercase
    }

    $stmt->close();
}


$questions = array_values($quizData);
$totalQuestions = count($questions);
$columns = 2;

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
                <button id="pauseButton" style="display:none;">Pause</button>
                <div id="rightControls">
                    <div id="timer">30:00</div>
                    <div class="score-container">
                        <div id="score">0/<span id="totalQuestions"><?php echo $totalQuestions; ?></span></div>
                    </div>
                    <button id="repeatButton" style="display:none;" onclick="restartQuiz()">Repeat Quiz</button>
                    <button id="giveUpButton" style="display:none;" onclick="giveUp()">Give Up</button>
                </div>
            </div>

            <input type="text" id="answerInput" autocomplete="off" placeholder="Type your answer here...">
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

        <script>
            let totalQuestions = <?php echo $totalQuestions; ?>;
            let score = 0;
            let timeLeft = 30 * 60; // 30 minutes
            let timer;
            let correctAnswers = {};
            let quizStarted = false;

            document.getElementById('startButton').onclick = function () {
                if (!quizStarted) {
                    quizStarted = true;
                    this.style.display = 'none';
                    document.getElementById('pauseButton').style.display = 'inline';
                    document.getElementById('answerInput').style.display = 'inline-block';
                    document.getElementById('giveUpButton').style.display = 'block';
                    startTimer();
                }
            };

            function startTimer() {
                timer = setInterval(function () {
                    timeLeft--;
                    updateTimerDisplay();
                    if (timeLeft <= 0 || score === totalQuestions) {
                        endQuiz();
                    }
                }, 1000);
            }

            function updateTimerDisplay() {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                document.getElementById('timer').innerHTML = minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
            }

            document.getElementById('answerInput').onkeyup = function (event) {
                const userAnswer = event.target.value.trim().toLowerCase().replace(/\s+/g, ' ');
                const answerElements = document.querySelectorAll(".answer");

                answerElements.forEach((answerElement, index) => {
                    const correctAnswers = JSON.parse(answerElement.getAttribute("data-answers"));

                    if (correctAnswers.includes(userAnswer)) {
                        answerElement.textContent = correctAnswers[0];
                        answerElement.style.visibility = 'visible';
                        event.target.value = ''; // Clear input
                        score++;
                        document.getElementById('score').textContent = `${score}/${totalQuestions}`;
                    }
                });
            };

            document.querySelectorAll(".answer").forEach((answerElement, index) => {
                console.log(`Q${index + 1} Answers: `, JSON.parse(answerElement.getAttribute("data-answers")));
            });


            function endQuiz() {
                clearInterval(timer);
                alert("Quiz Over! Final Score: " + Math.round((score / totalQuestions) * 100) + "%");
            }

            function giveUp() {
                clearInterval(timer);
                document.querySelectorAll('.answer').forEach(answer => {
                    answer.style.visibility = 'visible';
                });
            }

            function restartQuiz() {
                score = 0;
                timeLeft = 30 * 60;
                document.getElementById('score').textContent = "0/" + totalQuestions;
                document.getElementById('timer').textContent = "30:00";
                document.querySelectorAll('.answer').forEach(answer => {
                    answer.style.visibility = 'hidden';
                });
                document.getElementById('answerInput').value = '';
                startTimer();
            }
        </script>

    </div>
</main>

<!---->
<!--<script>-->
<!--    let totalQuestions = 0;-->
<!--    let score = 0;-->
<!--    let timer;-->
<!--    let timeLeft = 30 * 60; // 30 minutes in seconds-->
<!--    let questions = []; // Questions will be loaded from the database-->
<!--    let timerPaused = false;-->
<!--    let correctAnswers = {};-->
<!--    let quizStarted = false; // Track if the quiz has started-->
<!---->
<!--    document.addEventListener("DOMContentLoaded", function () {-->
<!--        fetchQuestions();-->
<!--    });-->
<!---->
<!--    function fetchQuestions() {-->
<!--        const quizType = "--><?php //echo $quizType; ?><!--//"; // Get quiz type from PHP-->
<!--//        fetch(`fetch-questions.php?category=${quizType}`)-->
<!--//            .then(response => response.json())-->
<!--//            .then(data => {-->
<!--//                questions = data;-->
<!--//                totalQuestions = questions.length;-->
<!--//                document.getElementById('totalQuestions').textContent = totalQuestions;-->
<!--//                populateQuizTable();-->
<!--//            })-->
<!--//            .catch(error => console.error('Error loading questions:', error));-->
<!--//    }-->
<!--//-->
<!--//    function populateQuizTable() {-->
<!--//        const quizTable = document.getElementById('quizTable');-->
<!--//        quizTable.innerHTML = '';-->
<!--//-->
<!--//        questions.forEach((q, i) => {-->
<!--//            const row = document.createElement('tr');-->
<!--//            row.innerHTML = `-->
<!--//            <td class="question">${q.question}</td>-->
<!--//            <td class="answer"><span id="answer_${i}" data-question="${q.question}"></span></td>-->
<!--//        `;-->
<!--//            quizTable.appendChild(row);-->
<!--//        });-->
<!--//    }-->
<!--//-->
<!--//    document.getElementById('startButton').onclick = function () {-->
<!--//        if (!quizStarted) {-->
<!--//            quizStarted = true; // Mark quiz as started-->
<!--//            this.style.display = 'none';-->
<!--//            document.getElementById('pauseButton').style.display = 'inline';-->
<!--//            document.getElementById('answerInput').style.display = 'inline-block';-->
<!--//            document.getElementById('giveUpButton').style.display = 'block';-->
<!--//            startTimer();-->
<!--//        }-->
<!--//    };-->
<!--//-->
<!--//    document.getElementById('pauseButton').onclick = function () {-->
<!--//        if (!timerPaused) {-->
<!--//            pauseTimer();-->
<!--//            this.textContent = "Resume";-->
<!--//            document.getElementById('answerInput').style.display = "none";-->
<!--//        } else {-->
<!--//            resumeTimer();-->
<!--//            this.textContent = "Pause";-->
<!--//            document.getElementById('answerInput').style.display = "block";-->
<!--//        }-->
<!--//    };-->
<!--//-->
<!--//    function startTimer() {-->
<!--//        timer = setInterval(function () {-->
<!--//            timeLeft--;-->
<!--//            updateTimerDisplay();-->
<!--//            if (timeLeft <= 0 || score === totalQuestions) {-->
<!--//                endQuiz();-->
<!--//            }-->
<!--//        }, 1000);-->
<!--//    }-->
<!--//-->
<!--//    function pauseTimer() {-->
<!--//        timerPaused = true;-->
<!--//        clearInterval(timer);-->
<!--//        document.getElementById('pauseModal').style.display = 'flex';-->
<!--//    }-->
<!--//-->
<!--//    function resumeTimer() {-->
<!--//        timerPaused = false;-->
<!--//        document.getElementById('pauseModal').style.display = 'none';-->
<!--//        startTimer();-->
<!--//    }-->
<!--//-->
<!--//    function updateTimerDisplay() {-->
<!--//        let minutes = Math.floor(timeLeft / 60);-->
<!--//        let seconds = timeLeft % 60;-->
<!--//        document.getElementById('timer').innerHTML = minutes + ":" + (seconds < 10 ? '0' : '') + seconds;-->
<!--//    }-->
<!--//-->
<!--//    function giveUp() {-->
<!--//        clearInterval(timer);-->
<!--//        questions.forEach((q, i) => {-->
<!--//            const span = document.getElementById(`answer_${i}`);-->
<!--//            if (!span.innerHTML) {-->
<!--//                span.innerHTML = q.answer;-->
<!--//                span.style.color = 'red';-->
<!--//            }-->
<!--//        });-->
<!--//-->
<!--//        document.getElementById('giveUpButton').style.display = 'none';-->
<!--//        document.getElementById('repeatButton').style.display = 'block';-->
<!--//        document.getElementById('answerInput').style.display = 'none';-->
<!--//        document.getElementById('pauseButton').style.display = 'none';-->
<!--//    }-->
<!--//-->
<!--//    document.getElementById('answerInput').onkeyup = function (event) {-->
<!--//        const userAnswer = event.target.value.trim();-->
<!--//-->
<!--//        questions.forEach((q, i) => {-->
<!--//            if (userAnswer.toLowerCase() === q.answer.toLowerCase()) {-->
<!--//                const span = document.getElementById(`answer_${i}`);-->
<!--//                if (!span.innerHTML) {-->
<!--//                    span.innerHTML = q.answer;-->
<!--//                    correctAnswers[q.question] = true;-->
<!--//                    score++;-->
<!--//                    document.getElementById('score').textContent = `${score}/${totalQuestions}`;-->
<!--//                    event.target.value = '';-->
<!--//                }-->
<!--//            }-->
<!--//        });-->
<!--//    };-->
<!--//-->
<!--//    function restartQuiz() {-->
<!--//        score = 0;-->
<!--//        timeLeft = 30 * 60;-->
<!--//        document.getElementById('score').innerHTML = score + "/" + totalQuestions;-->
<!--//        document.getElementById('timer').innerHTML = "30:00";-->
<!--//        correctAnswers = {};-->
<!--//        document.getElementById('answerInput').value = '';-->
<!--//-->
<!--//        document.querySelectorAll('.answer span').forEach(span => {-->
<!--//            span.innerHTML = '';-->
<!--//        });-->
<!--//-->
<!--//        document.getElementById('answerInput').style.display = 'block';-->
<!--//        document.getElementById('pauseButton').style.display = 'inline';-->
<!--//        document.getElementById('repeatButton').style.display = 'none';-->
<!--//        document.getElementById('giveUpButton').style.display = 'block';-->
<!--//-->
<!--//        startTimer();-->
<!--//    }-->
<!--//-->
<!--//    function endQuiz() {-->
<!--//        clearInterval(timer);-->
<!--//        alert("Quiz Over! Final Score: " + Math.round((score / totalQuestions) * 100) + "%");-->
<!--//        giveUp();-->
<!--//    }-->
<!--//-->
<!--//</script>-->
<!--//-->
<!--<script>-->
<!--    document.addEventListener("DOMContentLoaded", function () {-->
<!--        const answerInput = document.getElementById("answerInput");-->
<!--        const answerElements = document.querySelectorAll(".answer");-->
<!---->
<!--        answerInput.addEventListener("input", function () {-->
<!--            const userAnswer = answerInput.value.trim().toLowerCase();-->
<!---->
<!--            answerElements.forEach(answerElement => {-->
<!--                const correctAnswers = JSON.parse(answerElement.getAttribute("data-answers"));-->
<!---->
<!--                if (correctAnswers.includes(userAnswer)) {-->
<!--                    answerElement.textContent = correctAnswers[0]; // Display the correct answer-->
<!--                    answerElement.classList.remove("hidden");-->
<!--                    answerInput.value = ""; // Clear input field-->
<!--                }-->
<!--            });-->
<!--        });-->
<!--    });-->
<!--</script>-->
