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
$quizType = isset($quiz['quiz_type']) ? strtolower(trim($quiz['quiz_type'])) : 'classic';

// Fetch questions & answers ordered by question_order
$stmt = $conn->prepare("SELECT * FROM `quiz-questions` WHERE quiz_id = ? ORDER BY question_order ASC");
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
            'answer_text' => $answerRow['answer_text'],
            'is_correct' => (int)$answerRow['is_correct'],  // cast to int to be safe
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

// === Handle POST ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update quiz metadata
    $title = $_POST['quizTitle'];
    $desc = $_POST['quizDesc'];
    $category = $_POST['quizCategory'];
    $tags = $_POST['quizTags'];
    $timer = $_POST['quizTimer'];
    $quizType = $_POST['quizType'];

    $stmt = $conn->prepare("UPDATE `quiz-quizzes` SET title=?, description=?, category=?, tags=?, timer=?, quiz_type=?, last_updated=NOW() WHERE id=? AND user_id=?");
    $stmt->bind_param("ssssssii", $title, $desc, $category, $tags, $timer, $quizType, $quizId, $userId);
    $stmt->execute();

    // === Handle questions ===
    $submittedQuestionIds = $_POST['questionId'] ?? [];
    $questionOrderList = $_POST['question_order'] ?? [];

    // Get current question IDs in DB for this quiz
    $existingQuestionIds = [];
    $res = $conn->query("SELECT id FROM `quiz-questions` WHERE quiz_id = $quizId");
    while ($row = $res->fetch_assoc()) {
        $existingQuestionIds[] = $row['id'];
    }

    // Delete removed questions
    $toDelete = array_diff($existingQuestionIds, array_filter($submittedQuestionIds, 'is_numeric'));
    foreach ($toDelete as $qid) {
        $conn->query("DELETE FROM `quiz-answers` WHERE question_id = $qid");
        $conn->query("DELETE FROM `quiz-questions` WHERE id = $qid");
    }

    // Loop through submitted questions
    foreach ($_POST['question'] as $index => $qText) {
        $qText = trim($qText);
        $questionId = $_POST['questionId'][$index] ?? null;

        if (!$qText) continue;

        // Determine the order based on question_order[]
        $currentId = $_POST['questionId'][$index];
        $order = array_search($currentId, $questionOrderList);
        if ($order === false) $order = $index; // fallback

        // Insert or update question
        if (is_numeric($questionId)) {
            // Update existing
            $stmt = $conn->prepare("UPDATE `quiz-questions` SET question_text=?, question_type=?, question_order=? WHERE id=? AND quiz_id=?");
            $stmt->bind_param("ssiii", $qText, $quizType, $order, $questionId, $quizId);
            $stmt->execute();
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO `quiz-questions` (quiz_id, question_text, question_type, question_order, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("issi", $quizId, $qText, $quizType, $order);
            $stmt->execute();
            $questionId = $stmt->insert_id;
        }

        // Delete existing answers
        $conn->query("DELETE FROM `quiz-answers` WHERE question_id = $questionId");

        // Insert answers
        $qidKey = $_POST['questionId'][$index];
        $answers = $_POST['answers'][$qidKey] ?? [];

        foreach ($answers as $i => $aText) {
            $aText = trim($aText);
            if ($aText === '') continue;

            // Determine if this answer is the selected correct one
            $correctIndex = $_POST['correct'][$qidKey] ?? null;
            $isCorrect = ($correctIndex == ($i + 1)) ? 1 : 0;

            $stmt = $conn->prepare("INSERT INTO `quiz-answers` (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $questionId, $aText, $isCorrect);
            $stmt->execute();
        }
    }

        // Redirect
    header("Location: " . BASE_URL . "quiz/profile.php");
    exit();
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

    <h2>Create a New Quiz <?php echo "quiz_type: '$quizType'";?>
    </h2>
    <form action="<?php echo BASE_URL ?>quiz/profile/edit-quiz.php?id=<?php echo $quizId; ?>" method="POST"
          autocomplete="off">
        <!-- Basic Info -->
        <h3>Basic Info
        </h3>
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
                    $categories = ["sports", "geography", "music", "movies", "tv", "history", "language", "science", "IT", "literature", "entertainment", "miscellaneous"];
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

        // Make sure order is updated before form is submitted
        const form = document.querySelector('form');
        form.addEventListener('submit', updateQuestionOrderInputs);
    });

    function getQuizType() {
        return document.getElementById('quizType')?.value;
    }


    function initializeQuizForm() {
        const existingQuestions = <?php echo json_encode($questions); ?>;
        window.quizType = "<?php echo $quizType; ?>";
        existingQuestions.forEach((questionData) => {
            if (questionData && questionData.question && questionData.answers.length > 0) {
                addExistingQuestion(questionData, quizType);
            }
        });
    }


    function addExistingQuestion(questionData, quizType) {
        const quizContainer = document.getElementById('quizContainer');
        const questionBlock = createQuestionBlock(
            questionData.question['id'],
            questionData.question['question_text'],
            questionData.answers,
            quizType
        );
        quizContainer.appendChild(questionBlock);
        updateRemoveButtons();
    }

    function addQuestion() {
        const quizContainer = document.getElementById('quizContainer');
        const newId = 'new_' + Date.now();
        const quizType = getQuizType(); // dynamically detect current type
        const questionBlock = createQuestionBlock(newId, '', [], quizType);
        quizContainer.appendChild(questionBlock);

        updateRemoveButtons();
    }


    function createQuestionBlock(questionId, questionText = '', answers = [], quizType = window.quizType || '') {
        const block = document.createElement('div');
        block.className = 'question-block';
        block.setAttribute('draggable', 'true');

        const dragHandle = document.createElement('div');
        dragHandle.className = 'drag-handle';
        dragHandle.innerHTML = '☰';
        block.appendChild(dragHandle);
        addDragEvents(block);

        const numberDiv = document.createElement('div');
        numberDiv.className = 'question-number';
        block.appendChild(numberDiv);

        const removeBtn = document.createElement('button');
        removeBtn.className = 'btn-remove';
        removeBtn.textContent = '✖';
        removeBtn.onclick = () => removeQuestion(block);
        block.appendChild(removeBtn);

        const questionInput = document.createElement('input');
        questionInput.type = 'text';
        questionInput.name = 'question[]';
        questionInput.className = 'form-input';
        questionInput.placeholder = 'Enter question';
        questionInput.value = questionText;
        block.appendChild(questionInput);

        const questionIdInput = document.createElement('input');
        questionIdInput.type = 'hidden';
        questionIdInput.name = 'questionId[]';
        questionIdInput.value = questionId;
        block.appendChild(questionIdInput);

        const answersRow = document.createElement('div');
        answersRow.className = 'answers-row';

        for (let i = 0; i < 4; i++) {
            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.name = `answers[${questionId}][]`;
            answerInput.className = 'form-input';
            answerInput.placeholder = `Answer ${i + 1}`;
            answerInput.value = answers[i] ? answers[i]['answer_text'] : '';
            answersRow.appendChild(answerInput);

            if (answers[i]) {
                const answerIdInput = document.createElement('input');
                answerIdInput.type = 'hidden';
                answerIdInput.name = `answerId[${questionId}][]`;
                answerIdInput.value = answers[i]['id'];
                answersRow.appendChild(answerIdInput);
            }
        }

        block.appendChild(answersRow);

        if (quizType === 'multiple-choice') {
            const correctIndex = answers.findIndex(ans => ans.is_correct === 1); // zero-based index
            const correctRow = document.createElement('div');
            correctRow.className = 'correct-answer-row';
            correctRow.innerHTML = `
        <label>Select correct answer:</label><br>
        ${[1, 2, 3, 4].map(i => {
                const checked = (correctIndex === i - 1) ? 'checked' : '';
                return `<label><input type="radio" name="correct[${questionId}]" value="${i}" ${checked}> ${i}</label>`;
            }).join(' ')}
    `;
            block.appendChild(correctRow);
        }


        return block;
    }

    document.getElementById('quizType').addEventListener('change', () => {
        const quizType = getQuizType();
        const allBlocks = document.querySelectorAll('.question-block');

        allBlocks.forEach(block => {
            const existingRadios = block.querySelector('.correct-answer-row');
            if (quizType === 'multiple-choice') {
                if (!existingRadios) {
                    const qId = block.querySelector('input[name="questionId[]"]').value;
                    const correctRow = document.createElement('div');
                    correctRow.className = 'correct-answer-row';
                    correctRow.innerHTML = `
                    <label>Select correct answer:</label><br>
                    ${[1, 2, 3, 4].map(i =>
                        `<label><input type="radio" name="correct[${qId}]" value="${i}"> ${i}</label>`
                    ).join(' ')}
                `;
                    block.appendChild(correctRow);
                }
            } else {
                if (existingRadios) {
                    existingRadios.remove();
                }
            }
        });
    });

    function removeQuestion(block) {
        const container = document.getElementById('quizContainer');
        if (container.children.length > 1) {
            container.removeChild(block);
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

    function updateQuestionOrderInputs() {
        // Remove existing inputs (to avoid duplicates)
        document.querySelectorAll('input[name="question_order[]"]').forEach(e => e.remove());

        const container = document.getElementById('quizContainer');
        const form = container.closest('form');

        const blocks = container.querySelectorAll('.question-block');
        blocks.forEach((block, idx) => {
            const questionId = block.querySelector('input[name="questionId[]"]').value;

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'question_order[]';
            input.value = questionId;
            form.appendChild(input);
        });
    }

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


    let draggedBlock = null;

    function addDragEvents(block) {
        block.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', '');
            block.classList.add('dragging');
            draggedBlock = block;
        });

        block.addEventListener('dragend', () => {
            block.classList.remove('dragging');
            draggedBlock = null;
            updateQuestionNumbers();
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
                updateQuestionNumbers();
            }
            block.classList.remove('drag-over');
        });
    }

    let formDirty = false;

    // Detect changes in form fields
    document.addEventListener('input', (event) => {
        if (
            event.target.matches('#quizTitle, #quizDesc, #quizTags, input[name="question[]"], input[name^="answers"]')
        ) {
            formDirty = true;
        }
    });

    // Warn user before leaving the page with unsaved changes
    window.addEventListener('beforeunload', function (e) {
        if (formDirty) {
            const confirmationMessage = 'Unsaved data. Are you sure you want to leave?';
            (e || window.event).returnValue = confirmationMessage; // For legacy browsers
            return confirmationMessage; // For modern browsers
        }
    });

    // If the form is submitted, clear the dirty flag
    document.querySelector('form').addEventListener('submit', function () {
        formDirty = false;
    });
</script>

<?php endif; ?>
</body>
</html>
