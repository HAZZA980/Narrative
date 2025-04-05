<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'includes/quiz-header.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "user_auth.php");
    exit();
}

$userId = $_SESSION['user_id'];
$quizId = $_GET['id'] ?? null;

if (!$quizId) {
    echo "Invalid quiz ID.";
    exit;
}

// Fetch quiz info
$stmt = $conn->prepare("SELECT * FROM `quiz-quizzes` WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $quizId, $userId);
$stmt->execute();
$quizResult = $stmt->get_result();

if ($quizResult->num_rows === 0) {
    echo "Quiz not found or access denied.";
    exit;
}

$quiz = $quizResult->fetch_assoc();
$quizTitle = $quiz['title'];
$quizDesc = $quiz['description'];
$quizCategory = $quiz['category'];
$quizTags = $quiz['tags'];
$quizTimer = $quiz['timer'];
$quizType = $quiz['quiz_type'] ?? 'classic';

// Fetch questions & answers
$stmt = $conn->prepare("SELECT * FROM `quiz-questions` WHERE quiz_id = ?");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$questionsResult = $stmt->get_result();

$questions = [];
while ($row = $questionsResult->fetch_assoc()) {
    $questionId = $row['id'];
    $questionText = $row['question_text'];

    $stmtA = $conn->prepare("SELECT * FROM `quiz-answers` WHERE question_id = ?");
    $stmtA->bind_param("i", $questionId);
    $stmtA->execute();
    $answersResult = $stmtA->get_result();

    $answers = [];
    while ($answerRow = $answersResult->fetch_assoc()) {
        $answers[] = [
            'id' => $answerRow['id'],
            'answer_text' => $answerRow['answer_text']
        ];
    }

    $questions[] = [
        'question' => [
            'id' => $questionId,
            'question_text' => $questionText
        ],
        'answers' => $answers
    ];
}

// =========================
// Handle form submission
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $quizTitle = $_POST['quizTitle'] ?? '';
    $quizDesc = $_POST['quizDesc'] ?? '';
    $quizCategory = $_POST['quizCategory'] ?? 'miscellaneous';
    $quizTags = $_POST['quizTags'] ?? '';
    $quizTimer = isset($_POST['quizTimer']) ? intval($_POST['quizTimer']) : 60;

    // Update quiz meta
    $updateQuiz = $conn->prepare("UPDATE `quiz-quizzes` SET title = ?, description = ?, category = ?, tags = ?, timer = ?, quiz_type = ? WHERE id = ? AND user_id = ?");
    $updateQuiz->bind_param("ssssssii", $quizTitle, $quizDesc, $quizCategory, $quizTags, $quizTimer, $quizType, $quizId, $userId);
    $updateQuiz->execute();

    $questions = $_POST['question'] ?? [];
    $questionIds = $_POST['questionId'] ?? [];
    $answers = $_POST['answers'] ?? [];
    $answerIds = $_POST['answerId'] ?? [];

    $processedQuestionIds = [];

    foreach ($questions as $qIndex => $questionText) {
        $questionText = trim($questionText);

        if (isset($questionIds[$qIndex]) && !empty($questionIds[$qIndex])) {
            $qid = intval($questionIds[$qIndex]);
            $processedQuestionIds[] = $qid;

            $updateQ = $conn->prepare("UPDATE `quiz-questions` SET question_text = ? WHERE id = ?");
            $updateQ->bind_param("si", $questionText, $qid);
            $updateQ->execute();
        } else {
            $insertQ = $conn->prepare("INSERT INTO `quiz-questions` (quiz_id, question_text, question_type) VALUES (?, ?, 'classic')");
            $insertQ->bind_param("is", $quizId, $questionText);
            $insertQ->execute();
            $qid = $conn->insert_id;
            $processedQuestionIds[] = $qid;
        }

        // Handle answers
        $questionAnswers = $answers[$qIndex] ?? [];
        $questionAnswerIds = $answerIds[$qIndex] ?? [];
        $processedAnswerIds = [];

        foreach ($questionAnswers as $aIndex => $answerText) {
            $answerText = trim($answerText);
            if ($answerText === '') continue;

            if (isset($questionAnswerIds[$aIndex]) && !empty($questionAnswerIds[$aIndex])) {
                $aid = intval($questionAnswerIds[$aIndex]);
                $processedAnswerIds[] = $aid;

                $updateA = $conn->prepare("UPDATE `quiz-answers` SET answer_text = ? WHERE id = ?");
                $updateA->bind_param("si", $answerText, $aid);
                $updateA->execute();
            } else {
                $insertA = $conn->prepare("INSERT INTO `quiz-answers` (question_id, answer_text, is_correct) VALUES (?, ?, 1)");
                $insertA->bind_param("is", $qid, $answerText);
                $insertA->execute();
                $processedAnswerIds[] = $conn->insert_id;
            }
        }

        // Delete removed answers
        if (!empty($processedAnswerIds)) {
            $inClause = implode(',', array_fill(0, count($processedAnswerIds), '?'));
            $types = str_repeat('i', count($processedAnswerIds));
            $stmt = $conn->prepare("DELETE FROM `quiz-answers` WHERE question_id = ? AND id NOT IN ($inClause)");
            $params = array_merge([$qid], $processedAnswerIds);
            $stmt->bind_param('i' . $types, ...$params);
            $stmt->execute();
        }
    }

    // Delete removed answers
    if (!empty($processedAnswerIds)) {
        // Ensure to delete answers that are not included in the processed list for the question being edited
        $inClause = implode(',', array_fill(0, count($processedAnswerIds), '?'));
        $types = str_repeat('i', count($processedAnswerIds));

        // Prepare SQL to delete answers that are NOT in the processed list for this specific question
        $stmt = $conn->prepare("DELETE FROM `quiz-answers` WHERE question_id = ? AND id NOT IN ($inClause)");
        $params = array_merge([$qid], $processedAnswerIds);
        $stmt->bind_param('i' . $types, ...$params);
        $stmt->execute();
    }

// Ensure questions without answers are removed
    if (!empty($processedQuestionIds)) {
        // Ensure to delete questions that are not included in the processed list
        $inClause = implode(',', array_fill(0, count($processedQuestionIds), '?'));
        $types = str_repeat('i', count($processedQuestionIds));

        // Prepare SQL to delete questions that are NOT in the processed list for the quiz
        $stmt = $conn->prepare("DELETE FROM `quiz-questions` WHERE quiz_id = ? AND id NOT IN ($inClause)");
        $params = array_merge([$quizId], $processedQuestionIds);
        $stmt->bind_param('i' . $types, ...$params);
        $stmt->execute();
    } else {
        // If no question IDs are processed, delete questions with no associated answers
        $stmt = $conn->prepare("DELETE FROM `quiz-questions` WHERE quiz_id = ? AND id NOT IN ($inClause) AND NOT EXISTS (SELECT 1 FROM `quiz-answers` WHERE question_id = `quiz-questions`.id)");
        $stmt->bind_param('i', $quizId);
        $stmt->execute();
    }



    header("Location: " . BASE_URL . "quiz/profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Quiz | Narrative Quizzes</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>public/css/styles-create-quiz.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>quiz/css/edit-quiz.css">
</head>
<body>

<div class="quiz-container">
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="login-section">
            <h1>Log in to create a quiz!</h1>
            <a href="<?php echo BASE_URL; ?>user_auth.php" class="login-button">Log in</a>
        </div>
    <?php else: ?>

    <h2>Create a New Quiz</h2>
    <form action="<?php echo BASE_URL ?>quiz/profile/edit-quiz.php?id=<?php echo $quizId; ?>" method="POST"
          autocomplete="off">
        <!-- Basic Info -->
        <h3>Basic Info</h3>
        <div class="form-row">
            <!-- Quiz Type -->
            <div class="form-group">
                <label for="quizType">Type:</label>
                <select id="quizType" name="quizType" class="form-select" required>
                    <option value="classic" <?php echo ($quizType === "classic") ? "selected" : ""; ?>>Classic</option>
                    <option value="slides" <?php echo ($quizType === "slides") ? "selected" : ""; ?>>Slides</option>
                </select>
            </div>

            <!-- Quiz Category -->
            <div class="form-group">
                <label for="quizCategory">Category:</label>
                <select id="quizCategory" name="quizCategory" class="form-select" required>
                    <?php
                    $categories = ["sports", "geography", "music", "movies", "tv", "history", "language", "science", "gaming", "literature", "entertainment", "miscellaneous"];
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
        $quizType = isset($_GET['type']) ? $_GET['type'] : 'text'; // or 'clickable'
        ?>


        <h3>Enter Your Questions</h3>
        <p>Each question can have four possible correct answers if you so choose. Leave the unused answer boxes
            blank. </p>

        <div id="quizContainer">
        </div>


        <button type="button" class="btn-add" onclick="addQuestion()">Add Question</button>
        <button type="submit" class="btn-add">Save Quiz</button>

    </form>

</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        initializeQuizForm();
    });

    function initializeQuizForm() {
        // Check if there are existing questions
        const existingQuestions = <?php echo json_encode($questions); ?>;

        // Loop through the existing questions and populate the form with them
        existingQuestions.forEach((questionData, index) => {
            if (questionData && questionData.question && questionData.answers.length > 0) {
                // Adding question from existing data
                addExistingQuestion(questionData, index);
            }
        });
    }

    function addExistingQuestion(questionData, index) {
        const quizContainer = document.getElementById('quizContainer');
    const questionBlock = document.createElement('div');
    questionBlock.className = 'question-block';
    questionBlock.setAttribute('draggable', 'true');

    const dragHandle = document.createElement('div');
    dragHandle.className = 'drag-handle';
    dragHandle.innerHTML = '☰';
    questionBlock.appendChild(dragHandle);
    addDragEvents(questionBlock);

    const questionNumber = document.createElement('div');
    questionNumber.className = 'question-number';
    questionNumber.textContent = `Question ${index + 1}`;
    questionBlock.appendChild(questionNumber);

    const removeBtn = document.createElement('button');
    removeBtn.className = 'btn-remove';
    removeBtn.textContent = '✖';
    removeBtn.onclick = () => removeQuestion(questionBlock);
    questionBlock.appendChild(removeBtn);

    const questionInput = document.createElement('input');
    questionInput.type = 'text';
    questionInput.name = 'question[]';
    questionInput.className = 'form-input';
    questionInput.placeholder = 'Enter question';
    questionInput.value = questionData.question['question_text'];
    questionBlock.appendChild(questionInput);

    const questionIdInput = document.createElement('input');
    questionIdInput.type = 'hidden';
    questionIdInput.name = 'questionId[]';
    questionIdInput.value = questionData.question['id'];
    questionBlock.appendChild(questionIdInput);

    const answersRow = document.createElement('div');
    answersRow.className = 'answers-row';

    for (let i = 0; i < 4; i++) {
        const answerInput = document.createElement('input');
        answerInput.type = 'text';
        answerInput.name = `answers[${index}][]`;
        answerInput.className = 'form-input';
        answerInput.placeholder = `Answer ${i + 1}`;
        answerInput.value = questionData.answers[i] ? questionData.answers[i]['answer_text'] : '';
        answersRow.appendChild(answerInput);

        if (questionData.answers[i]) {
            const answerIdInput = document.createElement('input');
            answerIdInput.type = 'hidden';
            answerIdInput.name = `answerId[${index}][]`;
            answerIdInput.value = questionData.answers[i]['id'];
            answersRow.appendChild(answerIdInput);
        }
    }

    questionBlock.appendChild(answersRow);
    quizContainer.appendChild(questionBlock);
    updateRemoveButtons();
}

    function addQuestion() {
        const quizContainer = document.getElementById('quizContainer');
        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.setAttribute('draggable', 'true');

        // Add drag handle (☰ icon)
        const dragHandle = document.createElement('div');
        dragHandle.className = 'drag-handle';
        dragHandle.innerHTML = '☰'; // You can change this to any icon or text
        questionBlock.appendChild(dragHandle);

        // Add drag events to the question block
        addDragEvents(questionBlock);

        // Question Number
        const questionNumber = document.createElement('div');
        questionNumber.className = 'question-number';
        questionNumber.textContent = `Question ${quizContainer.children.length + 1}`;
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

        // Answers - Create exactly 4 answer input fields
        const answersRow = document.createElement('div');
        answersRow.className = 'answers-row';

        // Create 4 answer input fields, all initially empty
        for (let i = 0; i < 4; i++) {
            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.name = `answers[${quizContainer.children.length}][]`;
            answerInput.className = 'form-input';
            answerInput.placeholder = `Answer ${i + 1}`;
            answersRow.appendChild(answerInput);
        }

        questionBlock.appendChild(answersRow);

        // Append new question block to the container
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
</script>

<?php endif; ?>
</body>
</html>
