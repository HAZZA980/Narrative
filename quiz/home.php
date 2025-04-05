<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "layouts/mastheads/quizzes/quiz-masthead.php";
include BASE_PATH . 'includes/quiz-header.php';


$userId = $_SESSION['user_id'] ?? null;
$mostPlayedStmt = $conn->prepare("
    SELECT qq.id, qq.title, COUNT(qa.id) AS attempts
    FROM `quiz-attempts` qa
    JOIN `quiz-quizzes` qq ON qa.quiz_id = qq.id
    WHERE qa.attempted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY qa.quiz_id
    ORDER BY attempts DESC
    LIMIT 5
");

$mostPlayedStmt->execute();
$mostPlayedResult = $mostPlayedStmt->get_result();

// Get quizzes where the user scored the lowest
$userLowestResult = null;
if ($userId) {
    $lowestScoreStmt = $conn->prepare("
        SELECT qq.id, qq.title, qa.score
        FROM `quiz-attempts` qa
        JOIN `quiz-quizzes` qq ON qa.quiz_id = qq.id
        WHERE qa.user_id = ?
        ORDER BY qa.score ASC
        LIMIT 5
    ");
    $lowestScoreStmt->bind_param("i", $userId);
    $lowestScoreStmt->execute();
    $userLowestResult = $lowestScoreStmt->get_result();
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?php echo BASE_URL?>public/css/styles-learning-home.css">
    <style>
        .nav-home {
            border-bottom: white 6px solid !important;
        }


        .quiz-summary-boxes {
            display: flex;
            gap: 2rem;
            margin: 3rem 0;
            flex-wrap: wrap;
        }

        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 1.5rem;
            flex: 1 1 45%;
            min-width: 300px;
        }

        .summary-box h3 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .summary-box ul {
            list-style: none;
            padding-left: 0;
        }

        .summary-box ul li {
            margin-bottom: 0.5rem;
        }

        .summary-box a {
            text-decoration: none;
            color: #0077cc;
        }

        .summary-box a:hover {
            text-decoration: underline;
        }

    </style>
    <title>Home | Narrative Quizzes</title>
<body>

<main class="main-container">
    <div class="header">
        <h3 class="main-title">Narrative Quizzes</h3>
    </div>

    <div class="search">
        <img class="header-links-img" src="<?php echo BASE_URL?>public/images/badges/bookmarkCollector.png">
        <form class="form-bar" method="get" action="<?php echo BASE_URL?>quiz/quiz-search.php"> <!-- Set the action to quiz-search.php -->
            <input id="text-search-bar" type="text" name="txt-search" autocomplete="off"
                   placeholder="Search for a Quiz">
            <input id="btn-search" type="submit" value="Search">
        </form>
    </div>

    <div class="desc">
        <p><em>Welcome to the Random Knowledge Trivia website! Challenge yourself with a variety of fun and engaging
                trivia questions across different categories. Test your knowledge, learn something new, and see how well
                you can score on topics ranging from history to pop culture. Perfect for trivia enthusiasts looking for
                a quick and enjoyable way to challenge their minds!</em></p>
    </div>

    <div class="generalKnowledge">
        <a class="genKnowledgeQuiz" href="<?php echo BASE_URL?>quiz/categories.php">Browse Quizzes</a>
    </div>
    <div>
        <a href="<?php echo BASE_URL?>quiz/createQuiz.php">Create your quizzes here</a>
    </div>

    <div class="category-container">
        <h3 class="category-title">Popular Categories</h3>
        <ul class="category-list">
            <li class="category-item">
                <a href=<?php echo BASE_URL?>"layouts/pages/quizzes/quizzes-general-knowledge.php?category=film_and_tv">
                    <div class="category-image">
                        <img src="<?php echo BASE_URL?>public/images/users/52/The-Philadelphia-Story-009.webp" alt="Film & TV">
                    </div>
                    <p class="category-text">Film & TV</p>
                </a>
            </li>
            <li class="category-item">
                <a href="<?php echo BASE_URL?>layouts/pages/quizzes/quizzes-general-knowledge.php?category=history">
                    <div class="category-image">
                        <img src="<?php echo BASE_URL?>public/images/users/52/Napoleon.jpg" alt="History">
                    </div>
                    <p class="category-text">History</p>
                </a>
            </li>
            <li class="category-item">
                <a href="<?php echo BASE_URL?>layouts/pages/quizzes/quizzes-general-knowledge.php?category=literature">
                    <div class="category-image">
                        <img src="<?php echo BASE_URL?>public/images/users/52/shakespeare.jpg" alt="Literature">
                    </div>
                    <p class="category-text">Literature</p>
                </a>
            </li>
            <li class="category-item">
                <a href="<?php echo BASE_URL?>layouts/pages/quizzes/quizzes-general-knowledge.php?category=geography">
                    <div class="category-image">
                        <img src="<?php echo BASE_URL?>public/images/users/52/geography.jpg" alt="Geography">
                    </div>
                    <p class="category-text">Geography</p>
                </a>
            </li>
            <li class="category-item">
                <a href="<?php echo BASE_URL?>layouts/pages/quizzes/quizzes-general-knowledge.php?category=science">
                    <div class="category-image">
                        <img src="<?php echo BASE_URL?>public/images/users/52/science.jpg" alt="Science">
                    </div>
                    <p class="category-text">Science</p>
                </a>
            </li>
        </ul>
    </div>
    <div class="main-content">


    </div>





    <div class="quiz-summary-boxes">
        <div class="summary-box">
            <h3>🔥 Most Played Quizzes This Week</h3>
            <ul>
                <?php while ($row = $mostPlayedResult->fetch_assoc()): ?>
                    <li>
                        <a href="<?php echo BASE_URL . 'quiz/quiz.php?quiz_id=' . $row['id']; ?>">
                            <?php echo htmlspecialchars($row['title']); ?> (<?php echo $row['attempts']; ?> plays)
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="summary-box">
            <h3>📉 Improve Your Score</h3>
            <?php if (!$userId): ?>
                <p><a href="<?php echo BASE_URL; ?>auth/login.php">Log in</a> to start recording your scores and track your progress!</p>
            <?php elseif ($userLowestResult && $userLowestResult->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $userLowestResult->fetch_assoc()): ?>
                        <li>
                            <a href="<?php echo BASE_URL . 'quiz/quiz.php?quiz_id=' . $row['id']; ?>">
                                <?php echo htmlspecialchars($row['title']); ?> - Scored: <?php echo $row['score']; ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You've got no attempts logged yet! Go crush some quizzes 💪</p>
            <?php endif; ?>
        </div>
    </div>



</main>

</body>
</html>



<script>
    // JavaScript to handle menu interactions
    const menuItems = document.querySelectorAll('.category-item');
    const quizCategories = document.querySelectorAll('.quiz-category');

    // When a menu item is clicked, store the category in localStorage and scroll to the corresponding section
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            // Store the selected category in localStorage
            localStorage.setItem('selectedCategory', item.getAttribute('data-category'));

            // Hide all categories
            quizCategories.forEach(category => category.classList.remove('active'));

            // Show the selected category
            const selectedCategory = document.getElementById(item.getAttribute('data-category'));
            selectedCategory.classList.add('active');

            // Scroll to the selected category section
            selectedCategory.scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Show the last selected category when the page loads
    window.addEventListener('DOMContentLoaded', () => {
        const selectedCategory = localStorage.getItem('selectedCategory');

        if (selectedCategory) {
            // Find the menu item corresponding to the stored category
            const categoryMenuItem = document.querySelector(`.category-item[data-category='${selectedCategory}']`);

            // If a valid category is found in localStorage, show it
            if (categoryMenuItem) {
                // Hide all categories
                quizCategories.forEach(category => category.classList.remove('active'));

                // Show the selected category
                const selectedCategoryDiv = document.getElementById(selectedCategory);
                selectedCategoryDiv.classList.add('active');

                // Scroll to the selected category section
                selectedCategoryDiv.scrollIntoView({ behavior: 'smooth' });
            }
        } else {
    // If no category is selected in localStorage, show the first category b

</script>
