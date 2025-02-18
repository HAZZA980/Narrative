<?php
session_start();
include "../../../config/config.php";
include "../../../layouts/mastheads/quizzes/quiz-masthead.php";
include "quiz-functions/quiz-links.php";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>General Knowledge | Narrative Learn</title>
    <script src="trivia-questions.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Quiz-layout/generalKnowledgeHomepage.css">
    <style>
        .nav-general-knowledge {
            border-bottom: white 6px solid !important;
        }
    </style>
</head>
<body>
<main class="main-container">
    <!-- Left-Side Menu -->
    <div class="menu-container">
        <div class="menu-item" data-category="film_and_tv">Film & TV</div>
        <div class="menu-item" data-category="history">History</div>
        <div class="menu-item" data-category="literature">Literature</div>
        <div class="menu-item" data-category="science">Science</div>
        <div class="menu-item" data-category="geography">Geography</div>
        <div class="menu-item" data-category="compScience">Computer Science</div>
    </div>

    <!-- Main Content -->
    <div class="content-container" id="content-container">
        <?php

        foreach ($generalKnowledgeCategories as $key => $category) {
            echo "<section class='quiz-category' id='$key'>";
            echo "<h3>{$category['title']}</h3>";
            if (isset($category['subcategories'])) {
                foreach ($category['subcategories'] as $subKey => $subCategory) {
                    echo "<div class='quiz-header'>";
                    echo "<div class='quiz-info'>";
                    echo "<a class='quiz-link' href='{$subCategory['link']}'>{$subCategory['title']}</a>";
                    if ($subCategory['scoreVar'] !== null) {
                        echo "<div class='quiz-score'><span>Recent Score: " . round($subCategory['scoreVar']) . "</span>%</div>";
                    }
                    echo "</div>";
                    echo "<p><em>{$subCategory['description']}</em></p>";
                    echo "</div>";
                }
            } else {
                echo "<div class='quiz-header'>";
                echo "<div class='quiz-info'>";
                echo "<a class='quiz-link' href='{$category['link']}'>{$category['title']} Quiz</a>";
                if ($category['scoreVar'] !== null) {
                    echo "<div class='quiz-score'><span>Recent Score: {$category['scoreVar']}%</span></div>";
                }
                echo "</div>";
                echo "<p><em>{$category['description']}</em></p>";
                echo "</div>";
            }
            echo "</section>";
        }

        ?>
    </div>
</main>

<script>
    // JavaScript to handle menu interactions
    const menuItems = document.querySelectorAll('.menu-item');
    const quizCategories = document.querySelectorAll('.quiz-category');

    // When a menu item is clicked, update the URL with the selected category
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            // Get the selected category
            const selectedCategory = item.getAttribute('data-category');

            // Update the URL with the selected category without reloading the page
            history.pushState(null, null, `?category=${selectedCategory}`);

            // Hide all categories
            quizCategories.forEach(category => category.classList.remove('active'));

            // Show the selected category
            const selectedCategoryDiv = document.getElementById(selectedCategory);
            selectedCategoryDiv.classList.add('active');
        });
    });

    // Show the category based on the URL when the page loads
    window.addEventListener('DOMContentLoaded', () => {
        // Get the category from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const selectedCategory = urlParams.get('category');

        if (selectedCategory) {
            // Find the menu item corresponding to the selected category
            const categoryMenuItem = document.querySelector(`.menu-item[data-category='${selectedCategory}']`);

            // If a valid category is found, show it
            if (categoryMenuItem) {
                // Hide all categories
                quizCategories.forEach(category => category.classList.remove('active'));

                // Show the selected category
                const selectedCategoryDiv = document.getElementById(selectedCategory);
                selectedCategoryDiv.classList.add('active');
            }
        } else {
            // If no category is selected in the URL, show the first category by default
            if (quizCategories.length > 0) {
                quizCategories[0].classList.add('active');
            }
        }
    });

</script>
</body>
</html>
