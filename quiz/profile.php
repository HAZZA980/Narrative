<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "layouts/mastheads/quizzes/quiz-masthead.php";
include BASE_PATH . 'includes/quiz-header.php';

$userId = $_SESSION['user_id'] ?? null;


// Fetch User-Created Quizzes
$createdQuizzesStmt = $conn->prepare("
    SELECT id, title, category, date_created
    FROM `quiz-quizzes`
    WHERE user_id = ?
    ORDER BY date_created DESC
");
$createdQuizzesStmt->bind_param("i", $userId);
$createdQuizzesStmt->execute();
$createdQuizzes = $createdQuizzesStmt->get_result();

// Fetch Most Attempted Quizzes
$mostAttemptedStmt = $conn->prepare("
    SELECT qq.id, qq.title, COUNT(qa.id) AS total_attempts
    FROM `quiz-attempts` qa
    JOIN `quiz-quizzes` qq ON qa.quiz_id = qq.id
    GROUP BY qa.quiz_id
    ORDER BY total_attempts DESC
    LIMIT 5
");
$mostAttemptedStmt->execute();
$mostAttempted = $mostAttemptedStmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile | Narrative Quizzes</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>public/css/styles-learning-home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>quiz/css/delete-quiz-modal.css">
    <style>
        .main-title {
            text-align: center;
            margin-top: 2rem;
        }

        .profile-section {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem auto;
            width: 90%;
            max-width: 800px;
        }

        .profile-section h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .profile-section ul {
            list-style: none;
            padding: 0;
        }

        .profile-section ul li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #ccc;
        }

        .profile-section ul li:last-child {
            border-bottom: none;
        }

        .profile-section a {
            text-decoration: none;
            color: #0077cc;
        }

        .profile-section a:hover {
            text-decoration: underline;
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

        .login-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<main class="main-container">
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="login-section">
            <h1>Log in to create a quiz!</h1>
            <a href="<?php echo BASE_URL; ?>user_auth.php" class="login-button">Log in</a>
        </div>
    <?php else: ?>


    <div class="header">
        <h3 class="main-title">👤 Your Profile</h3>
    </div>

    <!-- Section 1: User Created Quizzes -->
    <div class="profile-section">
        <h3>🛠️ Your Created Quizzes</h3>
        <?php if ($createdQuizzes->num_rows > 0): ?>
            <ul style="list-style: none; padding-left: 0;">
                <?php while ($quiz = $createdQuizzes->fetch_assoc()): ?>
                    <li style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #ddd;">
                        <div>
                            <a href="<?php echo BASE_URL . 'quiz/quiz.php?quiz_id=' . $quiz['id']; ?>" style="font-weight: bold;">
                                <?php echo htmlspecialchars($quiz['title']); ?>
                            </a>
                            <br>
                            <small>
                                (<?php echo htmlspecialchars($quiz['category']); ?> — Created on <?php echo date('M j, Y', strtotime($quiz['date_created'])); ?>)
                            </small>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <!-- Edit icon -->
                            <a href="<?php echo BASE_URL . 'quiz/profile/edit-quiz.php?id=' . $quiz['id']; ?>" title="Edit Quiz">✏️</a>

                            <!-- Delete icon -->
                            <a href="#" class="delete-btn" data-id="<?php echo $quiz['id']; ?>" title="Delete Quiz">🗑️</a>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>You haven't created any quizzes yet. <a href="<?php echo BASE_URL ?>quiz/createQuiz.php">Start one now</a>!</p>
        <?php endif; ?>
    </div>

    <!-- Section 2: Most Attempted Quizzes -->
    <div class="profile-section">
        <h3>🔥 Most Attempted Quizzes</h3>
        <?php if ($mostAttempted->num_rows > 0): ?>
            <ul>
                <?php while ($quiz = $mostAttempted->fetch_assoc()): ?>
                    <li>
                        <a href="<?php echo BASE_URL . 'quiz/quiz.php?quiz_id=' . $quiz['id']; ?>">
                            <?php echo htmlspecialchars($quiz['title']); ?>
                        </a>
                        (<?php echo $quiz['total_attempts']; ?> attempts)
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No quizzes have been attempted yet.</p>
        <?php endif; ?>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; padding: 2rem; border-radius: 10px; text-align: center;">
            <h2>Are you sure you want to delete this Quiz?</h2>
            <div class="modal-actions" style="margin-top: 20px;">
                <button id="confirmDelete" class="btn-confirm" style="margin-right: 10px;">Yes</button>
                <button id="cancelDelete" class="btn-cancel">No</button>
            </div>
        </div>
    </div>

    <?php endif?>
</main>

<!-- Delete Quiz Modal Logic -->
<script>
    let selectedQuizId = null;

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            selectedQuizId = this.getAttribute('data-id');
            document.getElementById('deleteModal').style.display = 'block';
        });
    });

    document.getElementById('cancelDelete').addEventListener('click', function () {
        document.getElementById('deleteModal').style.display = 'none';
        selectedQuizId = null;
    });

    document.getElementById('confirmDelete').addEventListener('click', function () {
        if (selectedQuizId) {
            fetch(`<?php echo BASE_URL ?>quiz/profile/deleteQuiz.php?id=${selectedQuizId}`)
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url; // Redirect to profile.php or wherever deleteQuiz.php sends us
                    } else {
                        window.location.reload(); // fallback
                    }
                });
        }
    });
</script>

</body>
</html>
