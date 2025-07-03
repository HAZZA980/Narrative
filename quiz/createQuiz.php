<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'includes/quiz-header.php';

// Defaults for the form fields
$quizType = 'classic';
$quizTitle = '';
$quizDesc = '';
$quizCategory = 'miscellaneous';
$quizTags = '';
$quizTimer = 60;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Quiz | Narrative Quizzes</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>public/css/styles-create-quiz.css">
    <style>
        /* Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        /* Container */
        .quiz-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Headings */
        h1, h3 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 20px;

        }

        /* Form Elements */
        form {
            width: 100%;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            background-color: #fff;
            transition: border-color 0.3s ease;
        }

        .form-input:hover,
        .form-input:focus,
        .form-textarea:hover,
        .form-textarea:focus,
        .form-select:hover,
        .form-select:focus {
            border-color: #007BFF;
            outline: none;
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Buttons */
        .btn,
        .form-button {
            display: inline-block;
            padding: 12px 18px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
        }


        .question-number {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }


        .btn-add {
            background-color: #007BFF;
            color: white;
        }

        .btn-add:hover {
            background-color: #0056b3;
        }

        .btn-save {
            background-color: #28a745;
            color: white;
        }

        .btn-save:hover {
            background-color: #218838;
        }

        .btn-remove {
            background: none;
            color: red;
            font-weight: bold;
            font-size: 18px;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            transition: 0.3s ease;
        }

        .btn-remove:hover {
            color: darkred;
            transform: scale(1.2);
        }

        /* Login Section */
        .login-section {
            text-align: center;
            padding: 50px 0;
        }

        .login-button {
            background-color: #007BFF;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }

        .login-section h1 {
            color: black;
        }

        .login-button:hover {
            background-color: #0056b3;
        }

        /* Quiz Table */
        .quiz-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .quiz-table th,
        .quiz-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .quiz-table th {
            background-color: #007BFF;
            color: white;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }

            .btn,
            .btn-add,
            .btn-save {
                width: 100%;
                margin-top: 10px;
            }

            .quiz-container {
                padding: 20px;
            }

            .quiz-table th,
            .quiz-table td {
                font-size: 14px;
                padding: 8px;
            }
        }


        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f4f4f4;
        }

        .question-block {
            border: 1px solid #ccc;
            padding: 15px 15px 15px 40px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #fff;
            position: relative;
            cursor: move;
        }

        .form-input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        .answers-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .answers-row .form-input {
            flex: 1;
        }

        .correct-answer-row {
            margin-bottom: 10px;
        }

        .btn-remove {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }

        .drag-handle {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 18px;
            cursor: grab;
            color: #888;
            user-select: none;
        }

        .drag-over {
            border: 2px dashed #3498db;
        }

        .btn-add {
            padding: 10px 15px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        h1 {
            margin-bottom: 20px;
        }

        form {
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="login-section">
        <h1>Log in to create a quiz!</h1>
        <a href="<?php echo BASE_URL; ?>user_auth.php" class="login-button">Log in</a>
    </div>
<?php else: ?>
<div class="quiz-container">


    <h2>Create a New Quiz</h2>
    <form action="<?php echo BASE_URL ?>quiz/model/save-quiz.php" method="POST" autocomplete="off">
        <!-- Basic Info -->
        <h3>Basic Info</h3>
        <div class="form-row">
            <!-- Quiz Type -->
            <div class="form-group">
                <label for="quizType">Type:</label>
                <select id="quizType" name="quizType" class="form-select" required>
                    <option value="classic" <?php echo ($quizType === "classic") ? "selected" : ""; ?>>Classic</option>
                    <option value="slides" <?php echo ($quizType === "slides") ? "selected" : ""; ?>>Slides</option>
                    <option value="multiple-choice" <?php echo ($quizType === "multiple-choice") ? "selected" : ""; ?>>Multiple Choice</option>
                </select>
            </div>

            <!-- Quiz Category -->
            <div class="form-group">
                <label for="quizCategory">Category:</label>
                <select id="quizCategory" name="quizCategory" class="form-select" required>
                    <?php
                    $categories = ["sports", "geography", "music", "movies", "TV", "history", "language", "science", "IT", "literature", "entertainment", "miscellaneous"];
                    foreach ($categories as $category) {
                        $selected = ($quizCategory === $category) ? "selected" : "";
                        echo "<option value='$category' $selected>" . ucfirst($category) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Timer -->
            <div class="form-group">
                <label for="quizTimer">Timer:</label>
                <select id="quizTimer" name="quizTimer" class="form-select" required>
                    <?php
                    $timers = [60, 120, 180, 300, 600, 900, 1200, 1500, 1800];
                    foreach ($timers as $time) {
                        $selected = ($quizTimer == $time) ? "selected" : "";
                        echo "<option value='$time' $selected>" . gmdate("i:s", $time) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Quiz Title -->
        <label for="quizTitle">Title:</label>
        <input type="text" id="quizTitle" name="quizTitle" class="form-input" placeholder="Enter quiz title"
               value="<?php echo htmlspecialchars($quizTitle); ?>" required>

        <!-- Quiz Description -->
        <label for="quizDesc">Description:</label>
        <textarea id="quizDesc" name="quizDesc" class="form-textarea" rows="3"
                  required><?php echo htmlspecialchars($quizDesc); ?></textarea>

        <!-- Quiz Tags -->
        <label for="quizTags">Tags (comma-separated):</label>
        <input type="text" id="quizTags" name="quizTags" class="form-input"
               placeholder="e.g., trivia, fun, general knowledge"
               value="<?php echo htmlspecialchars($quizTags); ?>" required>

        <!-- Questions Section -->


        <?php
        // Example: Set the quiz type
        $quizType = isset($_GET['type']) ? $_GET['type'] : 'classic'; // or 'clickable'
        ?>


        <h3>Enter Your Questions</h3>
        <p>Each question can have four possible correct answers if you so chose. Leave the unused answer boxes
            blank. </p>

        <div id="quizContainer"></div>

        <button type="button" class="btn-add" onclick="addQuestion()">Add Question</button>
        <br><br>
        <button type="submit" class="btn-add">Submit Quiz</button>
    </form>
    <?php endif; ?>

</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        initializeQuizForm();
    });

    function getQuizType() {
        return document.getElementById('quizType').value;
    }

    function initializeQuizForm() {
        addQuestion(); // Show one block by default
    }

    function addQuestion() {
        const quizContainer = document.getElementById('quizContainer');
        const index = quizContainer.children.length;
        updateRemoveButtons();
        updateQuestionNumbers();

        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.setAttribute('draggable', 'true');

        // Drag handle
        const dragHandle = document.createElement('div');
        dragHandle.className = 'drag-handle';
        dragHandle.innerHTML = '☰';
        questionBlock.appendChild(dragHandle);

        // Question Number
        const questionNumber = document.createElement('div');
        questionNumber.className = 'question-number';
        questionNumber.textContent = `Question ${index + 1}`;
        questionBlock.appendChild(questionNumber);

        // Remove button
        const removeBtn = document.createElement('button');
        removeBtn.className = 'btn-remove';
        removeBtn.textContent = '✖';
        removeBtn.onclick = () => removeQuestion(questionBlock);
        questionBlock.appendChild(removeBtn);

        // Question input
        const questionInput = document.createElement('input');
        questionInput.type = 'text';
        questionInput.name = 'question[]';
        questionInput.className = 'form-input';
        questionInput.placeholder = 'Enter question';
        questionInput.required = true;
        questionBlock.appendChild(questionInput);

        // Answers
        const answersRow = document.createElement('div');
        answersRow.className = 'answers-row';

        for (let i = 1; i <= 4; i++) {
            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.name = `answer${i}[]`;
            answerInput.className = 'form-input';
            answerInput.placeholder = `Answer ${i}`;
            if (i === 1) answerInput.required = true;
            answersRow.appendChild(answerInput);
        }

        questionBlock.appendChild(answersRow);

        // Correct answer radios (optional)
        if (getQuizType() === 'multiple-choice') {
            const correctRow = document.createElement('div');
            correctRow.className = 'correct-answer-row';
            correctRow.innerHTML = `
                    <label>Select correct answer:</label><br>
                    <label><input type="radio" name="correct_answer[${index}]" value="answer1" required> 1</label>
                    <label><input type="radio" name="correct_answer[${index}]" value="answer2"> 2</label>
                    <label><input type="radio" name="correct_answer[${index}]" value="answer3"> 3</label>
                    <label><input type="radio" name="correct_answer[${index}]" value="answer4"> 4</label>
                `;
            questionBlock.appendChild(correctRow);
        }

        // Drag events
        addDragEvents(questionBlock);

        quizContainer.appendChild(questionBlock);
        updateRemoveButtons();
    }

    function removeQuestion(block) {
        const quizContainer = document.getElementById('quizContainer');
        if (quizContainer.children.length > 1) {
            quizContainer.removeChild(block);
            updateRemoveButtons();
            updateQuestionNumbers();

        }
    }


    function updateQuestionNumbers() {
        const blocks = document.querySelectorAll('.question-block');
        blocks.forEach((block, idx) => {
            const numberDiv = block.querySelector('.question-number');
            if (numberDiv) {
                numberDiv.textContent = `Question ${idx + 1}`;
            }
        });
    }


    function updateRemoveButtons() {
        const blocks = document.querySelectorAll('.question-block');
        blocks.forEach((block) => {
            const btn = block.querySelector('.btn-remove');
            btn.style.display = blocks.length > 1 ? 'block' : 'none';
        });
    }

    let draggedBlock = null;

    function addDragEvents(block) {
        block.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', '');
            block.classList.add('dragging');
            draggedBlock = block;
            updateRemoveButtons();
            updateQuestionNumbers();

        });

        block.addEventListener('dragend', () => {
            block.classList.remove('dragging');
            draggedBlock = null;
        });

        block.addEventListener('dragover', (e) => {
            e.preventDefault();
            block.classList.add('drag-over');
        });

        block.addEventListener('dragleave', () => {
            block.classList.remove('drag-over');
        });

        block.addEventListener('drop', (e) => {
            e.preventDefault();
            const container = document.getElementById('quizContainer');
            if (draggedBlock && draggedBlock !== block) {
                container.insertBefore(draggedBlock, block.nextSibling === draggedBlock ? block : block);
            }
            block.classList.remove('drag-over');
        });
    }
    document.getElementById('quizType').addEventListener('change', () => {
        const quizContainer = document.getElementById('quizContainer');
        quizContainer.innerHTML = ''; // Clear all existing questions
        addQuestion(); // Add the first question with updated type
    });

</script>

</body>
</html>
