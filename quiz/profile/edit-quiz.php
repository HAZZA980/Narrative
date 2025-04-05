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

    // Get current question IDs in DB for this quiz
    $existingQuestionIds = [];
    $res = $conn->query("SELECT id FROM `quiz-questions` WHERE quiz_id = $quizId");
    while ($row = $res->fetch_assoc()) {
        $existingQuestionIds[] = $row['id'];
    }

    // Delete questions removed in form
    $toDelete = array_diff($existingQuestionIds, array_filter($submittedQuestionIds, 'is_numeric'));
    foreach ($toDelete as $qid) {
        $conn->query("DELETE FROM `quiz-answers` WHERE question_id = $qid");
        $conn->query("DELETE FROM `quiz-questions` WHERE id = $qid");
    }

    // Loop through submitted questions
    foreach ($_POST['question'] as $index => $qText) {
        $qText = trim($qText);
        $questionId = $_POST['questionId'][$index] ?? null;

        if (!$qText) continue; // Skip empty questions

        // Insert or update
        if (is_numeric($questionId)) {
            // Update existing
            $stmt = $conn->prepare("UPDATE `quiz-questions` SET question_text=? WHERE id=? AND quiz_id=?");
            $stmt->bind_param("sii", $qText, $questionId, $quizId);
            $stmt->execute();
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO `quiz-questions` (quiz_id, question_text, question_type, created_at) VALUES (?, ?, 'classic', NOW())");
            $stmt->bind_param("is", $quizId, $qText);
            $stmt->execute();
            $questionId = $stmt->insert_id;
        }

        // Delete all answers for this question
        $conn->query("DELETE FROM `quiz-answers` WHERE question_id = $questionId");

        // Now add updated answers
        $qidKey = is_numeric($_POST['questionId'][$index]) ? $_POST['questionId'][$index] : $_POST['questionId'][$index]; // e.g., 'new_123456'
        $answers = $_POST['answers'][$qidKey] ?? [];

        foreach ($answers as $aText) {
            $aText = trim($aText);
            if ($aText === '') continue;

            $stmt = $conn->prepare("INSERT INTO `quiz-answers` (question_id, answer_text, is_correct) VALUES (?, ?, 1)");
            $stmt->bind_param("is", $questionId, $aText);
            $stmt->execute();
        }
    }

    // Redirect or success
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
        const existingQuestions = <?php echo json_encode($questions); ?>;
        existingQuestions.forEach((questionData) => {
            if (questionData && questionData.question && questionData.answers.length > 0) {
                addExistingQuestion(questionData);
            }
        });
    }

    function addExistingQuestion(questionData) {
        const quizContainer = document.getElementById('quizContainer');
        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.setAttribute('draggable', 'true');

        const questionId = questionData.question['id']; // Use real ID from DB

        const dragHandle = document.createElement('div');
        dragHandle.className = 'drag-handle';
        dragHandle.innerHTML = '☰';
        questionBlock.appendChild(dragHandle);
        addDragEvents(questionBlock);

        const questionNumber = document.createElement('div');
        questionNumber.className = 'question-number';
        questionNumber.textContent = `Question ${quizContainer.children.length + 1}`;
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
        questionIdInput.value = questionId;
        questionBlock.appendChild(questionIdInput);

        const answersRow = document.createElement('div');
        answersRow.className = 'answers-row';

        for (let i = 0; i < 4; i++) {
            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.name = `answers[${questionId}][]`; // 🛠️ Use question ID as key
            answerInput.className = 'form-input';
            answerInput.placeholder = `Answer ${i + 1}`;
            answerInput.value = questionData.answers[i] ? questionData.answers[i]['answer_text'] : '';
            answersRow.appendChild(answerInput);

            if (questionData.answers[i]) {
                const answerIdInput = document.createElement('input');
                answerIdInput.type = 'hidden';
                answerIdInput.name = `answerId[${questionId}][]`; // 🛠️ Match ID-based grouping
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

        const newQuestionId = 'new_' + Date.now(); // 🛠️ Temporary ID for new questions

        const dragHandle = document.createElement('div');
        dragHandle.className = 'drag-handle';
        dragHandle.innerHTML = '☰';
        questionBlock.appendChild(dragHandle);
        addDragEvents(questionBlock);

        const questionNumber = document.createElement('div');
        questionNumber.className = 'question-number';
        questionNumber.textContent = `Question ${quizContainer.children.length + 1}`;
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
        questionInput.required = true;
        questionBlock.appendChild(questionInput);

        const questionIdInput = document.createElement('input');
        questionIdInput.type = 'hidden';
        questionIdInput.name = 'questionId[]';
        questionIdInput.value = newQuestionId; // 🛠️ Use temporary ID
        questionBlock.appendChild(questionIdInput);

        const answersRow = document.createElement('div');
        answersRow.className = 'answers-row';

        for (let i = 0; i < 4; i++) {
            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.name = `answers[${newQuestionId}][]`; // 🛠️ Match temp ID
            answerInput.className = 'form-input';
            answerInput.placeholder = `Answer ${i + 1}`;
            answersRow.appendChild(answerInput);
        }

        questionBlock.appendChild(answersRow);
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
            }
            block.classList.remove('drag-over');
            updateQuestionNumbers();
        });
    }

</script>

<?php endif; ?>
</body>
</html>
