<?php
include "../../../config/config.php";
include "../../../layouts/mastheads/quizzes/quiz-masthead.php";
include "quiz-functions/quiz-links.php"
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../../../public/css/styles-learning-home.css">
    <style>
        .nav-learn-home {
            border-bottom: white 6px solid !important;
        }
    </style>
    <title>Home | Narrative Learn</title>
    <script src="trivia-questions.js"></script>
</head>
<body>

<main class="main-container">
    <div class="header">
        <h3 class="main-title">Narrative Quizzes</h3>
    </div>

    <div class="search">
        <img class="header-links-img" src="../../../public/images/header-img/search.png">
        <form class="form-bar" method="get" action="quiz-search.php"> <!-- Set the action to quiz-search.php -->
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
        <a class="genKnowledgeQuiz" href="questionFunctions.php?category=generalKnowledge">General Knowledge Questions</a>
    </div>


    <div class="category-container">
        <h3 class="category-title">Choose a Quiz Category</h3>
        <ul class="category-list">
            <li class="category-item">
                <a href="quizzes-general-knowledge.php?category=film_and_tv">
                    <div class="category-image">
                        <img src="../../../public/images/The-Philadelphia-Story-009.webp" alt="Film & TV">
                    </div>
                    <p class="category-text">Film & TV</p>
                </a>
            </li>
            <li class="category-item">
                <a href="quizzes-general-knowledge.php?category=history">
                    <div class="category-image">
                        <img src="../../../public/images/Napoleon.jpg" alt="History">
                    </div>
                    <p class="category-text">History</p>
                </a>
            </li>
            <li class="category-item">
                <a href="quizzes-general-knowledge.php?category=literature">
                    <div class="category-image">
                        <img src="../../../public/images/shakespeare.jpg" alt="Literature">
                    </div>
                    <p class="category-text">Literature</p>
                </a>
            </li>
            <li class="category-item">
                <a href="quizzes-general-knowledge.php?category=geography">
                    <div class="category-image">
                        <img src="../../../public/images/geography.jpg" alt="Geography">
                    </div>
                    <p class="category-text">Geography</p>
                </a>
            </li>
            <li class="category-item">
                <a href="quizzes-general-knowledge.php?category=science">
                    <div class="category-image">
                        <img src="../../../public/images/science.jpg" alt="Science">
                    </div>
                    <p class="category-text">Science</p>
                </a>
            </li>
            <li class="category-item">
                <a href="quizzes-general-knowledge.php?category=compScience">
                    <div class="category-image">
                        <img src="../../../public/images/compScience.jpg" alt="Computer Science">
                    </div>
                    <p class="category-text">Computer Science</p>
                </a>
            </li>
        </ul>
    </div>
    <div class="main-content">
        <?php
        // Helper function to recursively gather quizzes and their session keys
        function extractQuizData($categories, &$quizData = [])
        {
            foreach ($categories as $key => $category) {
                if (isset($category['subcategories'])) {
                    extractQuizData($category['subcategories'], $quizData); // Recursively handle subcategories
                } else {
                    $quizData[] = [
                        'title' => $category['title'],
                        'sessionKey' => $key, // Session key corresponding to the quiz
                        'score' => $category['scoreVar'],
                        'link' => $category['link'] ?? null, // Quiz link
                        'lastUpdated' => isset($_SESSION['lastUpdated'][$key]) ? $_SESSION['lastUpdated'][$key] : 0, // Last updated timestamp
                    ];
                }
            }
        }

        // Initialize the quizData array
        $quizData = [];
        extractQuizData($generalKnowledgeCategories, $quizData);

        // Sort quizzes by the last updated timestamp (descending order)
        usort($quizData, function ($a, $b) {
            return $b['lastUpdated'] <=> $a['lastUpdated']; // Sort by lastUpdated in descending order
        });

        // Get the 4 most recent quizzes
        $recent_quizzes = array_slice($quizData, 0, 4); // Limit to 4 most recent quizzes
        ?>

        <div class="section recent-quizzes">
            <h3 class="section-title">Recently Played Quizzes</h3>
            <ul class="quiz-list">
                <?php if (!empty($recent_quizzes)) : ?>
                    <?php foreach ($recent_quizzes as $quiz) : ?>
                        <li class="quiz-item">
                            <?php if (!empty($quiz['link'])) : ?>
                            <a href="<?php echo htmlspecialchars($quiz['link']); ?>">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($quiz['title']) . ' - ' . htmlspecialchars(number_format($quiz['score'], 2) . '%'); ?>
                                <?php if (!empty($quiz['link'])) : ?>
                            </a>
                        <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li>No recent quizzes played.</li>
                <?php endif; ?>
            </ul>
        </div>


        <?php

//        // Dynamically set $currentQuizKey based on the URL parameter 'category'
//        $currentQuizKey = isset($_GET['category']) ? $_GET['category'] : null; // Get the category from URL
//
//        if ($currentQuizKey) {
//            // Update score based on the quiz being taken
//            $newScore = 75.00; // Example score, replace with actual quiz result
//
//            // Update session with the new score for the specific quiz
//            $_SESSION['scores'][$currentQuizKey] = $newScore;
//
//            // Update the lastUpdated timestamp
//            $_SESSION['lastUpdated'][$currentQuizKey] = time(); // Current timestamp
//        } else {
//            // Handle the case when no category is provided, maybe show an error or default
//            echo "No category Provided";
//        }

        // Step 1: Gather all the quizzes and their associated scores from the session
        $improveScores = [];

        // Iterate through the categories and subcategories to gather quiz scores
        function extractQuizDataForImprovement($categories, &$improveScores = [])
        {
            foreach ($categories as $key => $category) {
                if (isset($category['subcategories'])) {
                    // Recursively handle subcategories
                    extractQuizDataForImprovement($category['subcategories'], $improveScores);
                } else {
                    // Add the quiz to the list if it has a score in the session
                    if (isset($_SESSION['scores'][$key])) {
                        $improveScores[] = [
                            'title' => $category['title'],
                            'description' => $category['description'],
                            'score' => $_SESSION['scores'][$key],  // Score from session
                            'link' => $category['link'] ?? null,   // Quiz link
                        ];
                    }
                }
            }
        }

        // Initialize the improveScores array by gathering data from all categories
        extractQuizDataForImprovement($generalKnowledgeCategories, $improveScores);

        // Step 2: Sort quizzes by score (ascending, so lowest scores come first)
        usort($improveScores, function ($a, $b) {
            return $a['score'] <=> $b['score']; // Sort in ascending order of scores
        });

        // Step 3: Limit to a certain number of quizzes (e.g., the 4 lowest scores)
        $improveScores = array_slice($improveScores, 0, 4); // Change the number as needed
        ?>

        <!-- Improve Your Score Section -->
        <div class="section improve-section">
            <h3 class="section-title">Improve Your Score</h3>
            <ul class="quiz-list">
                <?php if (!empty($improveScores)) : ?>
                    <?php foreach ($improveScores as $quiz) : ?>
                        <li class="quiz-item">
                            <a href="<?php echo htmlspecialchars($quiz['link']); ?>" class="quiz-link">
                                <?php echo htmlspecialchars($quiz['title']); ?>
                            </a>
                            <p class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></p>
                            <p class="quiz-score">Your Score: <?php echo number_format($quiz['score'], 2); ?>%</p>
                        </li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li>No quizzes with recorded scores.</li>
                <?php endif; ?>
            </ul>
        </div>

    </div>
    <!-- Resources Section -->
    <div class=" resources">
        <h3 class="section-title">Explore More Resources</h3>
        <div class="resources-links">
            <a href="movies/movie-database.php" class="resource-link">Movie Database</a>
            <a href="linguistics/db-lexicon.php" class="resource-link">Lexicon Database</a>
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
