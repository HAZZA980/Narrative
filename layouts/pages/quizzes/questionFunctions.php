x<?php
session_start(); // Start the session to store score data
// Check if the score is sent
if (isset($_POST['score'])) {
    // Save the score in the session
    $_SESSION['score_percentage'] = $_POST['score'];
}
include "../../../config/config.php";
include "../../../layouts/mastheads/quizzes/quiz-masthead.php";

// Get category from URL query string, if available
$category = isset($_GET['category']) ? $_GET['category'] : 'random';

//Quiz Title Display
$quizTitles = [
    "science" => "Science Quiz",        //URL from quiz-links.php : What I want displayed
    "literature" => "Literature Quiz",
    "geography" => "Geography Quiz",
    "history" => "History Quiz",
    "art" => "Art Quiz",
    "films" => "Hollywood Golden Era Quiz",
    "filmsYear" => "Name the Year",
    "random" => "General Trivia Quiz",
    "paris" => "Paris Quiz",
    "generalKnowledge" => "General Knowledge Quiz",
    "years_to_remember" => "Years To Remember",
];

$quizTitle = isset($quizTitles[$category]) ? $quizTitles[$category] : "Trivia Quiz";


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $quizTitle ?> Quiz</title>
    <script src="trivia-questions.js"></script>
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

        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            margin: auto;
            width: 73%;
        }

        .quiz-container {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .quiz-container h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 20px;
        }

        .quiz-container p {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 20px;
        }

        .quiz-container input[type="text"] {
            width: 80%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .quiz-container input[type="text"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        .quiz-container button {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #007BFF;
            color: #fff;
        }

        .quiz-container button:hover {
            background-color: #0056b3;
        }

        .feedback-message {
            font-size: 1.2rem;
            margin-top: 15px;
        }

        .feedback-message.correct {
            color: green;
        }

        .feedback-message.incorrect {
            color: red;
        }

        .feedback-message.final {
            color: #333;
        }

        .score-tracker {
            font-size: 1.2rem;
            color: #333;
            margin-top: 10px;
        }


        /* Style for the table container */
        .table-container {
            width: 90%; /* Adjust width as needed */
            margin: 20px auto; /* Center the table */
            padding: 20px;
            background-color: #f9f9f9; /* Light background for better contrast */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            overflow-x: auto; /* Enable horizontal scrolling for small screens */
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        /* Header styles */
        thead th {
            background-color: #007BFF;
            color: white;
            text-align: left;
            padding: 10px;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }

        /* Body styles */
        tbody tr:nth-child(even) {
            background-color: #f2f2f2; /* Alternating row color */
        }

        tbody td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        /* Add responsive design for small screens */
        @media (max-width: 768px) {
            .table-container {
                width: 100%;
                padding: 10px;
            }

            table {
                font-size: 12px;
            }
        }


    </style>
    <script>
        // Get the category passed from the URL
        const category = "<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>";

        let questions = [];

        // Based on the category, load the appropriate questions
        switch (category) {
            case "science":                     //quiz id from the URL
                questions = scienceQuestions;   //Name of the Quiz from trivia-questions.js
                break;
            case "literature":
                questions = literatureQuestions;
                break;
            case "geography":
                questions = geographyQuestions;
                break;
            case "history":
                questions = historyQuestions;
                break;
            case "films":
                questions = hollywoodGoldenEraQuestions;
                break;
            case "filmsYear":
                questions = yearReleased;
                break;
            case "Software_Development_Methodologies":
                questions = software_Development_Methodologies;
                break;
            case "paris":
                questions = parisQuestions;
                break;
            case "generalKnowledge":
                questions = allQuestions;
                break;
            case "years_to_remember":
                questions = yearsToRemember;
                break;
            case "empires":
                questions = empires;
                break;
            default:
                questions = allQuestions; // Use allQuestions as a fallback
        }

        console.log(questions); // Output questions to the console for debugging
    </script>
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
    <div class="quiz-container">
        <h1><?php echo htmlspecialchars($quizTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
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
    // Shuffle questions and select the first 20
    const shuffledQuestions = questions.sort(() => Math.random() - 0.5).slice(0, 20);

    let currentQuestionIndex = 0;
    let correctTally = 0;
    const answeredQuestions = [];

    const questionElement = document.getElementById('question');
    const formElement = document.getElementById('quiz-form');
    const answerInput = document.getElementById('answer-input');
    const feedbackMessage = document.getElementById('feedback-message');
    const scoreTracker = document.getElementById('score-tracker');

    const updateScoreTracker = () => {
        scoreTracker.textContent = `Score: ${correctTally}/${shuffledQuestions.length}`;
    };

    const loadQuestion = () => {
        if (currentQuestionIndex < shuffledQuestions.length) {
            questionElement.textContent = `Question ${currentQuestionIndex + 1}: ${shuffledQuestions[currentQuestionIndex].question}`;
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
            question: shuffledQuestions[currentQuestionIndex].question,
            userAnswer: userAnswer,
            correctAnswer: correctAnswer,
            isCorrect: isPartialMatch,
        });

        if (isPartialMatch) {
            correctTally++;
            feedbackMessage.textContent = "Correct!";
            feedbackMessage.className = "feedback-message correct";
        } else {
            feedbackMessage.textContent = `Incorrect! The correct answer was: ${correctAnswer}`;
            feedbackMessage.className = "feedback-message incorrect";
        }
    };
    const displayResults = () => {
        const percentage = (correctTally / shuffledQuestions.length) * 100; // Calculate the percentage
        questionElement.textContent = `You've completed the quiz! Your final score: ${correctTally} out of ${shuffledQuestions.length} (${percentage.toFixed(2)}%)`;

        // Hide the quiz form and score tracker
        formElement.style.display = 'none';
        scoreTracker.style.display = 'none';

        // Hide feedback message
        feedbackMessage.style.display = 'none';

        // Show results table (same as existing code)
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

        // Get the category from the URL
        const category = "<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>";

        // Send the score to the server via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "save-score.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                // Optionally, you can handle the server response here if needed
                console.log('Score saved successfully');
            }
        };
        xhr.send("score=" + encodeURIComponent(percentage.toFixed(2)) + "&category=" + encodeURIComponent(category));

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
            // Get the category from the URL query string (e.g., ?category=US_Presidents)
            const urlParams = new URLSearchParams(window.location.search);
            const category = urlParams.get('category'); // Fetches the 'category' parameter from the URL

            // Define the mapping for broad categories (to avoid repeating the return to specific quizzes)
            const categoryMapping = {
                'generalKnowledge': 'film_and_tv', //URL from quiz-links.php : category
                'films': 'film_and_tv',
                'filmsYear': 'film_and_tv',
                'history': 'history',
                'literature': 'literature',
                'science': 'science',
                'geography': 'geography',
                'paris': 'geography',
                'Software_Development_Methodologies': 'compScience',
                'empires': 'history',
                'years_to_remember': 'history',
            };

            // If a category is selected, redirect to the specific page
            if (category) {
                const formattedCategory = categoryMapping[category] || category;

                // Redirect to the appropriate category page (e.g., learning-general-knowledge.php?category=film_and_tv)
                window.location.href = 'quizzes-knowledge.php?category=${formattedCategory}`;
            } else {
                // Default redirect to homepage if no category is selected
                window.location.href = 'quizzes-general-knowledge.php';
            }
        });

        document.querySelector('.quiz-container').appendChild(returnButton);
    };



    formElement.addEventListener('submit', (event) => {
        event.preventDefault();
        const userAnswer = answerInput.value.trim();
        const correctAnswer = shuffledQuestions[currentQuestionIndex].answer;
        checkAnswer(userAnswer, correctAnswer);
        currentQuestionIndex++;
        setTimeout(loadQuestion, 700);
    });

    loadQuestion();
</script>

</body>
</html>
