<?php
session_start(); // Start the session
// Check if the user is logged in

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score']) && isset($_POST['category'])) {
    $scorePercentage = $_POST['score']; // Get the score from the POST request
    $category = $_POST['category']; // Get the quiz category

    // Save the score in the session under the correct category
    if (!isset($_SESSION['scores'])) {
        $_SESSION['scores'] = [];
    }
    $_SESSION['scores'][$category] = $scorePercentage;

    // Return a response (optional, if using AJAX)
    echo json_encode(['success' => true, 'score' => $scorePercentage]);
    exit;
}

include "../../../config/config.php";
include "../../../layouts/mastheads/quizzes/quiz-masthead.php";

// Get the quiz type from the query parameter (default to "US Presidents" if not specified)
$quizType = $_GET['category'] ?? 'Error 404: Quiz Not Found';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test your knowledge | Narrative</title>
    <link rel="stylesheet" href="../../../public/css/Quiz-layout/column.css">
    <link href="trivia-questions.js">
    <style>
        .nav-general-knowledge {
            border-bottom: white 6px solid !important;
        }

        .breadcrumbs {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            margin: 10px 20px;
        }

        .breadcrumbs a {
            text-decoration: none;
            color: #007BFF;
            margin-right: 5px;
        }

        .breadcrumbs a:hover {
            text-decoration: underline;
        }

        .breadcrumbs span {
            margin: 0 5px;
            color: #333;
        }

    </style>
</head>
<body>
<!-- Breadcrumbs Section -->
<nav class="breadcrumbs">
    <a href="quizzes-home.php">Home</a>
    <span>&gt;</span>
    <a href="quizzes-general-knowledge.php">General Knowledge</a>
    <span>&gt;</span>
    <span>Quiz</span>
</nav>

<main class="main-container">
    <div class="main-content">

        <div id="quizContainer">
            <div id="quizHeader">
                <h1><?php echo ucfirst(str_replace('_', ' ', $quizType)); ?> Quiz</h1>
            </div>

            <div id="topControls">
                <button id="startButton">Start Quiz</button>
                <button id="pauseButton" style="display:none;">Pause</button>
                <input type="text" id="answerInput" autocomplete="off" placeholder="Type your answer here..." style="display:none;"/>
                <div id="rightControls">
                    <div id="timer">30:00</div>
                    <div class="score-container">
                        <div id="score">0/<span id="totalQuestions">0</span></div>
                    </div>
                    <button id="repeatButton" style="display:none;" onclick="restartQuiz()">Repeat Quiz</button>
                    <button id="giveUpButton" style="display:none;" onclick="giveUp()">Give Up</button>
                </div>
            </div>

            <div id="pauseModal" class="modal">
                <div class="modal-content">
                    <h2>Quiz Paused</h2>
                    <p>Click the button below to resume the quiz.</p>
                    <button id="resumeButton">Resume</button>
                </div>
            </div>

            <form id="quizForm">
                <table id="quizTable"></table>
            </form>
        </div>
        <div id="back">
            <a id="returnLink" href="quizzes-general-knowledge.php">Return to quizzes</a>
        </div>


    </div>
</main>

<script src="trivia-questions.js"></script> <!-- Include the external question file -->
<script>
    // BACK BUTTON
    window.addEventListener('DOMContentLoaded', () => {
        // Get the category from the URL query string (e.g., ?category=US_Presidents)
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category'); // This fetches the 'category' parameter from the URL

        // Define the mapping for broad categories (to avoid repeating the return to specific quizzes)
        const categoryMapping = {
            'film&tv': 'film_and_tv',       //Get the quiz ID from the URL :: Category is the group it belongs to quiz-links.php
            'US_Presidents': 'history',
            'Famous_Wars': 'history',
            'Doctor_Who_Episodes': 'film_and_tv',
            'caryGrantFilms': 'film_and_tv',
            'Shakespeare_Plays': 'literature',
            '40s_Films': 'film_and_tv',
            'greek_mythology': 'history',
            'shakespearePlayQuiz': 'literature'
        };

        // If a category is selected, format and update the link text
        if (category) {
            // Check the category in the mapping
            const formattedCategory = categoryMapping[category] || category;

            // Format the category for display: remove underscores and capitalize each word
            const displayCategory = formattedCategory
                .replace(/_/g, ' ') // Replace underscores with spaces
                .replace(/\b\w+/g, word => {
                    if (word.toLowerCase() === 'and') {
                        return 'and'; // Keep 'and' lowercase
                    } else if (word.toLowerCase() === 'tv') {
                        return 'TV'; // Capitalize 'TV'
                    } else {
                        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase(); // Capitalize the first letter of other words
                    }
                });

            // Get the return link element
            const returnLink = document.getElementById('returnLink');

            // Set the link text dynamically (e.g., 'Return to Film And Tv quizzes')
            returnLink.textContent = `Return to ${displayCategory} quizzes`;

            // Set the link dynamically
            returnLink.href = `quizzes-general-knowledge.php?category=${formattedCategory}`;
        }
    });



    let totalQuestions = 0;
    let score = 0;
    let timer;
    let timeLeft = 30 * 60; // 30 minutes in seconds
    let questions = []; // Question set will be loaded dynamically
    let timerPaused = false; // Flag for paused timer
    let correctAnswers = {}; // Object to store correctly answered questions

    // Function to load questions dynamically with layout type
    function loadQuestions(quizType) {
        if (quizType === "US_Presidents") { //Quiz type is retrieved from the URL in quiz-links.php
            questions = usPresidents; // Loaded from trivia-questions.js
            setQuizLayout('two-column'); // Specify layout type
        }
        if (quizType === "Famous_Wars") {
            questions = famousWars;
            setQuizLayout('two-column');
        }
        if (quizType === 'caryGrantFilms') {
            questions = caryGrantFilmography;
            setQuizLayout('two-column'); // Specify layout type
        }

        if (quizType === 'Doctor_Who_Episodes') {
            questions = doctorWhoEpisodes;
            setQuizLayout('four-column'); // Specify layout type
        }

        if (quizType === 'Shakespeare_Plays') {
            questions = shakespearePlays
            setQuizLayout('shakespeare-three-custom-columns')
        }

        if (quizType === '40s_Films') {
            questions = fourtiesFilms
            setQuizLayout('two-column')
        }

        if (quizType === 'greek_mythology') {
            questions = greekMythology
            setQuizLayout('three-column')
        }

        if (quizType === 'shakespearePlayQuiz') {
            questions = shakespearePlayQuestions
            setQuizLayout('two-column')
        }


        totalQuestions = questions.length;
        document.getElementById('totalQuestions').textContent = totalQuestions;
        populateQuizTable();
    }

    // Function to set the layout type dynamically
    let quizLayout = 'two-column'; // Default layout
    function setQuizLayout(layoutType) {
        quizLayout = layoutType;
    }

    // Populate the quiz table dynamically
    function populateQuizTable() {
        const quizTable = document.getElementById('quizTable');
        quizTable.innerHTML = ''; // Clear any existing rows

        if (quizLayout === 'two-column') {
            const halfPoint = Math.ceil(totalQuestions / 2);

            for (let i = 0; i < halfPoint; i++) {
                const row = document.createElement('tr');
                const question1 = questions[i];
                const question2 = questions[i + halfPoint] || null;

                row.innerHTML = `
                <td class="question">${question1.question}</td>
                <td class="answer"><span id="answer_${i}" data-question="${question1.question}"></span></td>
                ${question2 ? `<td class="question">${question2.question}</td>
                <td class="answer"><span id="answer_${i + halfPoint}" data-question="${question2.question}"></span></td>` : ''}
            `;
                quizTable.appendChild(row);
            }
        } else if (quizLayout === 'three-column') {
            const thirdPoint = Math.ceil(totalQuestions / 3);

            for (let i = 0; i < thirdPoint; i++) {
                const row = document.createElement('tr');
                const question1 = questions[i];
                const question2 = questions[i + thirdPoint] || null;
                const question3 = questions[i + 2 * thirdPoint] || null;

                row.innerHTML = `
                <td class="question">${question1.question}</td>
                <td class="answer"><span id="answer_${i}" data-question="${question1.question}"></span></td>
                ${question2 ? `<td class="question">${question2.question}</td>
                <td class="answer"><span id="answer_${i + thirdPoint}" data-question="${question2.question}"></span></td>` : ''}
                ${question3 ? `<td class="question">${question3.question}</td>
                <td class="answer"><span id="answer_${i + 2 * thirdPoint}" data-question="${question3.question}"></span></td>` : ''}
            `;
                quizTable.appendChild(row);
            }
        } else if (quizLayout === 'four-column') {
            const quarterPoint = Math.ceil(totalQuestions / 4);

            for (let i = 0; i < quarterPoint; i++) {
                const row = document.createElement('tr');
                const question1 = questions[i];
                const question2 = questions[i + quarterPoint] || null;
                const question3 = questions[i + 2 * quarterPoint] || null;
                const question4 = questions[i + 3 * quarterPoint] || null;

                row.innerHTML = `
                <td class="question">${question1.question}</td>
                <td class="answer"><span id="answer_${i}" data-question="${question1.question}"></span></td>
                ${question2 ? `<td class="question">${question2.question}</td>
                <td class="answer"><span id="answer_${i + quarterPoint}" data-question="${question2.question}"></span></td>` : ''}
                ${question3 ? `<td class="question">${question3.question}</td>
                <td class="answer"><span id="answer_${i + 2 * quarterPoint}" data-question="${question3.question}"></span></td>` : ''}
                ${question4 ? `<td class="question">${question4.question}</td>
                <td class="answer"><span id="answer_${i + 3 * quarterPoint}" data-question="${question4.question}"></span></td>` : ''}
            `;
                quizTable.appendChild(row);
            }
        } else if (quizLayout === 'shakespeare-three-custom-columns') {
            const customLimits = [16, 10, 12]; // Limits for each column
            let questionIndex = 0;

            // Add headers for Shakespeare categories
            const headerRow = document.createElement('tr');
            headerRow.innerHTML = `
        <th class="column-header" style="background-color: #f4f4f4" colspan="2">Tragedies</th>
        <th class="column-header" style="background-color: #f4f4f4" colspan="2">Comedies</th>
        <th class="column-header" style="background-color: #f4f4f4" colspan="2">Histories</th>
    `;
            quizTable.appendChild(headerRow);

            // Initialize arrays to hold questions for each column
            const tragedies = questions.slice(0, customLimits[0]);
            const comedies = questions.slice(customLimits[0], customLimits[0] + customLimits[1]);
            const histories = questions.slice(customLimits[0] + customLimits[1], customLimits[0] + customLimits[1] + customLimits[2]);

            // Find the maximum number of rows needed
            const maxRows = Math.max(customLimits[0], customLimits[1], customLimits[2]);

            for (let i = 0; i < maxRows; i++) {
                const row = document.createElement('tr');

                // Tragedies column (first column)
                const tragedy = i < tragedies.length ? tragedies[i] : null;
                row.innerHTML += tragedy ? `<td class="question">${tragedy.question}</td>
                                     <td class="answer"><span id="answer_${questionIndex++}" data-question="${tragedy.question}"></span></td>`
                    : '<td></td><td></td>';

                // Comedies column (second column)
                const comedy = i < comedies.length ? comedies[i] : null;
                row.innerHTML += comedy ? `<td class="question">${comedy.question}</td>
                                    <td class="answer"><span id="answer_${questionIndex++}" data-question="${comedy.question}"></span></td>`
                    : '<td></td><td></td>';

                // Histories column (third column)
                const history = i < histories.length ? histories[i] : null;
                row.innerHTML += history ? `<td class="question">${history.question}</td>
                                    <td class="answer"><span id="answer_${questionIndex++}" data-question="${history.question}"></span></td>`
                    : '<td></td><td></td>';

                // Append the row to the table
                quizTable.appendChild(row);
            }
        }

    }


    document.addEventListener("DOMContentLoaded", function () {
        const quizType = "<?php echo $quizType; ?>"; // PHP variable
        loadQuestions(quizType);
    });

    // Start Button Event Listener
    document.getElementById('startButton').onclick = function () {
        this.style.display = 'none';  // Hide the Start button
        document.getElementById('pauseButton').style.display = 'inline'; // Show the Pause button

        // Show the Answer Input box when the quiz starts
        document.getElementById('answerInput').style.display = 'inline-block';  // or 'block', depending on layout

        document.getElementById('giveUpButton').style.display = 'block'; // Show the Give Up button
        startTimer(); // Start the timer
    };


    // Pause Button Event Listener
    document.getElementById('pauseButton').onclick = function () {
        if (!timerPaused) {
            pauseTimer();
            this.textContent = "Resume";
            answerInput.style.display = "none"; // Hide the input bar
        } else {
            resumeTimer();
            this.textContent = "Pause";
            answerInput.style.display = "block"; // Show the input bar
        }
    };

    // Start Timer
    function startTimer() {
        timer = setInterval(function () {
            timeLeft--;
            updateTimerDisplay();

            if (timeLeft <= 0 || score === totalQuestions) {
                endQuiz();
            }
        }, 1000);
    }

    // Pause Timer
    function pauseTimer() {
        timerPaused = true;
        clearInterval(timer);
    }

    // Resume Timer
    function resumeTimer() {
        timerPaused = false;
        startTimer();
    }

    // Update Timer Display
    function updateTimerDisplay() {
        var minutes = Math.floor(timeLeft / 60);
        var seconds = timeLeft % 60;
        document.getElementById('timer').innerHTML = minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
    }


    function giveUp() {
        clearInterval(timer); // Stop the timer
        const category = "<?php echo $quizType; ?>";
        const scorePercentage = Math.round((score / totalQuestions) * 100); // Calculate the score as a percentage
        saveScore(category, scorePercentage); // Save the score

        // Reveal the correct answers for all questions
        questions.forEach((q, i) => {
            const span = document.getElementById(`answer_${i}`);
            if (!span.innerHTML) {
                span.innerHTML = q.answer; // Show the correct answer
                span.style.color = 'red'; // Highlight as "revealed"
            }
        });

        // Hide the "Give Up" button
        document.getElementById('giveUpButton').style.display = 'none';

        // Show the "Repeat" button
        document.getElementById('repeatButton').style.display = 'block';

        // Hide the answer input field and the pause button
        document.getElementById('answerInput').style.display = 'none'; // Hide the answer input field
        document.getElementById('pauseButton').style.display = 'none'; // Hide the pause button
    }


    function normalizeAnswer(answer, quizType) {
        if (!answer) return '';

        // General normalization for all quizzes
        let normalized = answer
            .replace(/[^\w\s]|_/g, '') // Remove all punctuation
            .toLowerCase()             // Convert to lowercase
            .trim();                   // Remove extra spaces

        // Remove "the" from the start or middle of the answer
        normalized = normalized.replace(/\bthe\b/g, '').trim();
        normalized = normalized.replace(/\b(the|a)\b/g, '').trim();

        // Quiz-specific normalization
        if (quizType === 'US_Presidents') {
            const nameParts = normalized.split(' '); // Split into words
            if (nameParts.length > 1) {
                // If more than one word, return full normalized name
                return normalized;
            } else {
                // If only one word, assume it's a surname
                return nameParts[0]; // Return the single word (surname)
            }

        } else if (quizType === 'Famous_Wars') {
            // Remove "war" or "wars" from the normalized answer
            normalized = normalized.replace(/\b(war|wars|of|)\b/g, '').trim();

            // Special case for "Revolutionary" -> "Revolution"
            if (normalized.includes('revolutionary')) {
                normalized = normalized.replace('revolutionary', 'revolution');
            }

            return normalized;
        }

        return normalized; // Default normalization for other quizzes
    }

    function isValidMatch(userAnswer, correctAnswer, quizType) {
        const normalizedUserAnswer = normalizeAnswer(userAnswer, quizType);
        const normalizedCorrectAnswer = normalizeAnswer(correctAnswer, quizType);

        // Match logic for "US_Presidents" quiz
        if (quizType === 'US_Presidents') {
            const surname = normalizedCorrectAnswer.split(' ').pop(); // Get the surname
            return normalizedUserAnswer === normalizedCorrectAnswer || normalizedUserAnswer === surname;
        }

        // Exact match logic for "Famous_Wars"
        if (quizType === 'Famous_Wars') {
            return normalizedUserAnswer === normalizedCorrectAnswer;
        }

        // Default matching for other quizzes
        return normalizedUserAnswer === normalizedCorrectAnswer;
    }

    document.getElementById('answerInput').onkeyup = function (event) {
        const userAnswer = event.target.value.trim();

        questions.forEach((q, i) => {
            if (isValidMatch(userAnswer, q.answer, "<?php echo $quizType; ?>")) {
                const span = document.getElementById(`answer_${i}`);
                if (!span.innerHTML) { // Only proceed if the question isn't already answered
                    span.innerHTML = q.answer; // Display the full correct answer
                    correctAnswers[q.question] = true; // Mark as answered
                    score++;
                    document.getElementById('score').textContent = `${score}/${totalQuestions}`;
                    event.target.value = ''; // Clear input
                }
            }
        });
    };


    // Additional debugging to check what's happening
    console.log("User Input: ", normalizeAnswer(userAnswer));
    console.log("Correct Answer: ", normalizeAnswer(correctAnswer));


    // Update Scoreboard Function
    function updateScoreboard(correctAnswer) {
        const scoreList = document.getElementById('scoreList');
        const scoreboard = document.getElementById('scoreboard');

        // Add the correct answer to the scoreboard
        const listItem = document.createElement('li');
        listItem.textContent = correctAnswer;
        scoreList.appendChild(listItem);

        // Show scoreboard if hidden
        scoreboard.style.display = 'block';
    }

    var isPaused = false;

    document.getElementById('pauseButton').onclick = function () {
        if (!isPaused) {
            pauseQuiz();
        }
    };

    function pauseQuiz() {
        clearInterval(timer); // Stop the timer
        isPaused = true;
        document.getElementById('pauseModal').style.display = 'flex'; // Show the modal
        document.getElementById('answerInput').style.display = 'none'; // Hide the answer input bar
    }

    document.getElementById('resumeButton').onclick = function () {
        resumeQuiz();
    };

    function resumeQuiz() {
        isPaused = false;
        document.getElementById('pauseModal').style.display = 'none'; // Hide the modal
        document.getElementById('answerInput').style.display = 'inline-block'; // Show the answer input bar
        startTimer(); // Restart the timer
    }

    // Function to restart the quiz
    function restartQuiz() {
        // Reset score and other variables
        score = 0;
        timeLeft = 30 * 60; // Reset the timer to 30 minutes
        document.getElementById('score').innerHTML = score + "/" + totalQuestions; // Update score display
        document.getElementById('timer').innerHTML = "30:00"; // Reset the timer display
        correctAnswers = {}; // Reset correct answers
        document.getElementById('answerInput').value = ''; // Clear the input field

        // Reset all answers in the table
        var answerSpans = document.querySelectorAll('.answer span');
        answerSpans.forEach(span => {
            span.innerHTML = ''; // Clear answers from the table
        });

        // Show the user input bar and the pause button
        document.getElementById('answerInput').style.display = 'block'; // Make the input field visible
        document.getElementById('pauseButton').style.display = 'inline'; // Show the Pause button

        // Hide Repeat button and show Give Up button
        document.getElementById('repeatButton').style.display = 'none'; // Hide Repeat button
        document.getElementById('giveUpButton').style.display = 'block'; // Show Give Up button

        // Restart the quiz by starting the timer again
        startTimer();
    }


    function saveScore(category, score) {
        const formData = new FormData();
        formData.append('score', score);
        formData.append('category', category);

        fetch('columnQuestionFunctions.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log('Score saved:', data);
            })
            .catch(error => {
                console.error('Error saving score:', error);
            });
    }

    function endQuiz() {
        clearInterval(timer);
        const category = "<?php echo $quizType; ?>";
        const scorePercentage = Math.round((score / totalQuestions) * 100); // Calculate the score as a percentage
        saveScore(category, scorePercentage); // Save the score
        alert("Quiz Over! Final Score: " + scorePercentage + "%");
        giveUp();
    }

</script>

</body>
</html>
