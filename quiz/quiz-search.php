<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Assuming $searchResults is fetched from the database based on the search query
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'includes/quiz-header.php';


$searchTerm = isset($_GET['txt-search']) ? $_GET['txt-search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all'; // Default 'all' for all categories
$quizType = isset($_GET['quiz-type']) ? $_GET['quiz-type'] : ''; // Get quiz type filter (Classic, Slideshow, Clickable)

// Build SQL query based on the search term, category filter, and quiz type filter
$sql = "SELECT DISTINCT q.* FROM `quiz-quizzes` q
        JOIN `quiz-questions` qq ON q.id = qq.quiz_id";

$conditions = [];
$params = [];

// If search term is not empty, add the conditions for LIKE
if (!empty($searchTerm)) {
    $conditions[] = "(q.title LIKE ? OR q.description LIKE ? OR q.tags LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

// If a category is selected and it's not 'all', filter by category
if ($category != 'all') {
    $conditions[] = "q.category = ?";
    $params[] = $category;
}

// If a quiz type is selected, filter by question_type
if (!empty($quizType)) {
    $conditions[] = "qq.question_type = ?";
    $params[] = $quizType;
}

// If there are conditions, append them to the SQL query
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);

// Bind parameters only if necessary
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch the search results
$searchResults = [];
while ($row = $result->fetch_assoc()) {
    $searchResults[] = $row;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | Narrative Learn</title>
    <link rel="stylesheet" href="<?php echo BASE_URL?>quiz/css/styles-quiz-search.css">
    <style>
        .nav-search {
            border-bottom: white 6px solid !important;
        }
    </style>
</head>
<body>
<main class="main-container">
    <div class="main-content">

        <div class="search">

            <!-- Search Form with Dropdown -->
            <form class="quiz-search-form" method="get" action="quiz-search.php">
                <div class="form-bar search-container">
                    <!-- Search Input -->
                    <img class="header-links-img" src="<?php echo BASE_URL?>public/images/header-img/search.png">

                    <input id="text-search-bar" type="text" name="txt-search" autocomplete="off"
                           placeholder="Search for a Quiz" value="<?php echo htmlspecialchars($searchTerm); ?>">

                    <!-- Search Button -->
                    <input id="btn-search" type="submit" value="Search">
                </div>

                <div class="filter-dropdown-quiz">
                    <!-- Dropdown Menu for Categories -->
                    <select name="category" id="category-dropdown" class="category-dropdown">
                        <option value="all" <?php echo ($category == 'all') ? 'selected' : ''; ?>>All Categories</option>
                        <option value="sports" <?php echo ($category == 'sports') ? 'selected' : ''; ?>>Sports</option>
                        <option value="geography" <?php echo ($category == 'geography') ? 'selected' : ''; ?>>Geography</option>
                        <option value="music" <?php echo ($category == 'music') ? 'selected' : ''; ?>>Music</option>
                        <option value="movies" <?php echo ($category == 'movies') ? 'selected' : ''; ?>>Movies</option>
                        <option value="tv" <?php echo ($category == 'tv') ? 'selected' : ''; ?>>TV</option>
                        <option value="history" <?php echo ($category == 'history') ? 'selected' : ''; ?>>History</option>
                        <option value="language" <?php echo ($category == 'language') ? 'selected' : ''; ?>>Language</option>
                        <option value="science" <?php echo ($category == 'science') ? 'selected' : ''; ?>>Science</option>
                        <option value="gaming" <?php echo ($category == 'gaming') ? 'selected' : ''; ?>>Gaming</option>
                        <option value="literature" <?php echo ($category == 'literature') ? 'selected' : ''; ?>>Literature</option>
                        <option value="entertainment" <?php echo ($category == 'entertainment') ? 'selected' : ''; ?>>Entertainment</option>
                        <option value="miscellaneous" <?php echo ($category == 'miscellaneous') ? 'selected' : ''; ?>>Miscellaneous</option>
                    </select>

                    <!-- Dropdown Menu for Quiz Type -->
                    <select name="quiz-type" id="quiz-type-dropdown" class="quiz-type-dropdown">
                        <option value="">All Quiz Types</option>
                        <option value="Classic" <?php echo ($quizType == 'Classic') ? 'selected' : ''; ?>>Classic</option>
                        <option value="Slides" <?php echo ($quizType == 'Slides') ? 'selected' : ''; ?>>Slideshow</option>
                        <option value="Clickable" <?php echo ($quizType == 'Clickable') ? 'selected' : ''; ?>>Clickable</option>
                    </select>

                    <!-- Dropdown Menu for Length (optional) -->
                    <select name="length" id="length-dropdown" class="length-dropdown">
                        <!-- Assuming length options are based on timer values, you can adjust as needed -->
                        <option value="1:00">1:00</option>
                        <option value="2:00">2:00</option>
                        <option value="3:00">3:00</option>
                        <option value="4:00">4:00</option>
                        <option value="5:00">5:00</option>
                        <option value="10:00">10:00</option>
                        <option value="15:00">15:00</option>
                    </select>

                    <!-- Dropdown Menu for Relevance -->
                    <select name="Relevance" id="relevance-dropdown" class="relevance-dropdown">
                        <option value="Relevance">Relevance</option>
                        <option value="Date">Date</option>
                        <option value="Popularity">Popularity</option>
                    </select>

                </div>
            </form>
        </div>

        <?php if (!empty($searchResults)): ?>
            <ul class="results-list">
                <?php foreach ($searchResults as $result): ?>
                    <li class="result-item">
                        <a href="<?php echo BASE_URL?>quiz/quiz.php?quiz_id=<?php echo urlencode($result['id'] ?? ''); ?>">
                            <?php echo htmlspecialchars($result['title'] ?? 'Untitled Quiz'); ?>
                        </a>
                        <p class="result-description">
                            <em><?php echo htmlspecialchars($result['description'] ?? 'No description'); ?></em>
                        </p>
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
