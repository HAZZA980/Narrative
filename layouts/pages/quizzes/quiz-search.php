<?php
include "../../../config/config.php";  // Assuming your config file is in the same directory
include "../../../layouts/mastheads/quizzes/quiz-masthead.php";
include "quiz-functions/quiz-links.php"; // Make sure $generalKnowledgeCategories is properly included
function searchQuizzes($query, $generalKnowledgeCategories) {
    $results = [];

    // Clean the search query for better matching
    $query = trim($query);
    $query = strtolower($query); // Convert to lowercase for case-insensitive search

    // Iterate over the categories
    foreach ($generalKnowledgeCategories as $category) {
        // Check subcategories (quizzes)
        if (isset($category['subcategories']) && is_array($category['subcategories'])) {
            foreach ($category['subcategories'] as $subCategory) {
                $subCategoryTitle = strtolower(trim($subCategory['title'] ?? ''));
                $subCategoryDescription = strtolower(trim($subCategory['description'] ?? ''));

                // Check if the title or description matches the query
                if (
                    empty($query) || // If the query is empty, include all subcategories
                    stripos($subCategoryTitle, $query) !== false ||
                    stripos($subCategoryDescription, $query) !== false
                ) {
                    $results[] = [
                        'title' => $subCategory['title'],
                        'description' => $subCategory['description'],
                        'link' => $subCategory['link']
                    ];
                }
            }
        }
    }

    return $results;
}


// Get the search query from the URL
$searchQuery = $_GET['txt-search'] ?? ''; // Get the query or default to an empty string

// Call the search function with the query and the categories array
$searchResults = searchQuizzes($searchQuery, $generalKnowledgeCategories);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | Narrative Learn</title>
    <link rel="stylesheet" href="../../../public/css/Quiz-layout/styles-quiz-search.css">
</head>
<body>
<main class="main-container">
    <div class="main-content">

        <div class="search">
            <img class="header-links-img" src="../../../public/images/header-img/search.png">
            <form class="form-bar" method="get" action="quiz-search.php">
                <input id="text-search-bar" type="text" name="txt-search" autocomplete="off"
                       placeholder="Search for a Quiz">
                <input id="btn-search" type="submit" value="Search">
            </form>
        </div>

        <?php if (!empty($searchResults)): ?>
            <ul class="results-list">
                <?php foreach ($searchResults as $result): ?>
                    <li class="result-item">
                        <a href="<?php echo htmlspecialchars($result['link']); ?>" class="result-title"><?php echo htmlspecialchars($result['title']); ?></a>
                        <p class="result-description"><em><?php echo htmlspecialchars($result['description']); ?></em></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="no-results-message">No quizzes found matching your search.</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>