<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'includes/quiz-header.php';

$user_id = $_SESSION['user_id'] ?? null;

// Fetch the user's information (username and isAdmin status)
$query = "SELECT isAdmin FROM users WHERE user_id = ?";
$stmt1 = $conn->prepare($query);
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$user_result = $stmt1->get_result();
$user_row = $user_result->fetch_assoc();

$isAdmin = $user_row['isAdmin'] ?? 0; // Fetch the isAdmin status

$searchTerm = $_GET['txt-search'] ?? '';
$category = $_GET['category'] ?? 'all';
$quizType = $_GET['quiz-type'] ?? '';
$timerLimit = $_GET['length'] ?? 'All Timer Lengths';
$sortBy = $_GET['Relevance'] ?? 'Relevance';

$sql = "SELECT DISTINCT q.*, qq.question_type FROM `quiz-quizzes` q 
    JOIN `quiz-questions` qq ON q.id = qq.quiz_id
";

$conditions = [];
$params = [];
$types = "";

// Search term
if (!empty($searchTerm)) {
    $conditions[] = "(q.title LIKE ? OR q.description LIKE ? OR q.tags LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $types .= "sss";
}

// Category
if ($category !== 'all') {
    $conditions[] = "q.category = ?";
    $params[] = $category;
    $types .= "s";
}

// Quiz Type
if (!empty($quizType)) {
    $conditions[] = "qq.question_type = ?";
    $params[] = $quizType;
    $types .= "s";
}

// Timer filter (convert MM:SS to seconds)
if ($timerLimit !== 'All Timer Lengths') {
    list($min, $sec) = explode(":", $timerLimit);
    $maxSeconds = ($min * 60) + $sec;
    $conditions[] = "q.timer <= ?";
    $params[] = $maxSeconds;
    $types .= "i";
}

// Combine WHERE conditions
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Sort by Date if selected
if ($sortBy === "Date") {
    $sql .= " ORDER BY q.date_created DESC";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

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

        .results-list {
            list-style: none;
            padding: 0;
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .result-item {
            background-color: #ffffff;
            /*border: 1px solid #ddd;*/
            /*border-left: 5px solid #007BFF; !* Accent color *!*/
            padding: 5px 0 0 20px;
            border-radius: 8px;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        }

        .result-item:hover {
            padding: 5px 0 0 20px;
            border-radius: 8px;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            background-color: #f9f9f9;
        }


        .result-item a {
            font-size: 1.3rem;
            /*font-weight: 600;*/
            color: #007BFF;
            /*display: inline-block;*/
            /*margin-bottom: 8px;*/
        }

        .result-item a:hover {
            text-decoration: none;
            color: #0056b3;
        }

        .result-description {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 10px;
        }

        .result-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: blue;
        }

        .result-title:hover {
            text-decoration: underline;
            color: darkblue;
        }
        .result-meta {
            font-size: 0.9rem;
            color: #888;
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-group {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            width: 70%;
        }

        .quiz-description {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
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
                    </select>

                    <!-- Dropdown Menu for Length (optional) -->
                    <select name="length" id="length-dropdown" class="length-dropdown">
                        <option value="All Timer Lengths" <?php echo ($timerLimit == 'All Timer Lengths') ? 'selected' : ''; ?>>All Timer Durations</option>
                        <option value="1:00" <?php echo ($timerLimit == '1:00') ? 'selected' : ''; ?>>1:00</option>
                        <option value="2:00" <?php echo ($timerLimit == '2:00') ? 'selected' : ''; ?>>2:00</option>
                        <option value="3:00" <?php echo ($timerLimit == '3:00') ? 'selected' : ''; ?>>3:00</option>
                        <option value="4:00" <?php echo ($timerLimit == '4:00') ? 'selected' : ''; ?>>4:00</option>
                        <option value="5:00" <?php echo ($timerLimit == '5:00') ? 'selected' : ''; ?>>5:00</option>
                        <option value="10:00" <?php echo ($timerLimit == '10:00') ? 'selected' : ''; ?>>10:00</option>
                        <option value="15:00" <?php echo ($timerLimit == '15:00') ? 'selected' : ''; ?>>15:00</option>
                        <option value="20:00" <?php echo ($timerLimit == '20:00') ? 'selected' : ''; ?>>20:00</option>
                        <option value="25:00" <?php echo ($timerLimit == '25:00') ? 'selected' : ''; ?>>25:00</option>
                        <option value="30:00" <?php echo ($timerLimit == '30:00') ? 'selected' : ''; ?>>30:00</option>
                    </select>


                    <!-- Dropdown Menu for Relevance -->
                    <select name="Relevance" id="relevance-dropdown" class="relevance-dropdown">
                        <option value="Relevance" <?php echo ($sortBy == 'Relevance') ? 'selected' : ''; ?>>Relevance</option>
                        <option value="Date" <?php echo ($sortBy == 'Date') ? 'selected' : ''; ?>>Date</option>
                    </select>


                </div>
            </form>
        </div>

        <?php if (!empty($searchResults)): ?>
        <ul class="results-list">
            <?php foreach ($searchResults as $result): ?>
                <li class="result-item">
                    <a href="<?php echo BASE_URL ?>quiz/quiz.php?quiz_id=<?php echo urlencode($result['id'] ?? ''); ?>">
                        <h5 class="result-title"><?php echo htmlspecialchars($result['title'] ?? 'Untitled Quiz'); ?></h5>
                    <div class="quiz-description">
                        <p class="result-description"><em><?php echo htmlspecialchars($result['description'] ?? 'No description'); ?></em></p>

<!--                    --><?php //if (isset($_SESSION['user_id']) &&  $isAdmin == 1): ?>
<!--                        <div style="display: flex; gap: 10px;">-->
<!--                            <a href="--><?php //echo BASE_URL . 'quiz/profile/edit-quiz.php?id=' . $result['id']; ?><!--" title="Edit Quiz">✏️</a>-->
<!---->
<!--                            <a href="#" class="delete-btn" data-id="--><?php //echo $result['id']; ?><!--" title="Delete Quiz">🗑️</a>-->
<!--                        </div>-->
<!--                    --><?php //endif; ?>
                    </div>

                    <div class="meta-group">
                        <p class="result-meta">📁 Category: <?php echo htmlspecialchars($result['category'] ?? 'Unknown'); ?></p>

                        <p class="result-meta">🧩 Quiz Type: <?php echo htmlspecialchars($result['question_type'] ?? 'Unknown'); ?></p>

                        <?php
                        $seconds = (int)($result['timer'] ?? 0);
                        $minutes = floor($seconds / 60);
                        $secs = str_pad($seconds % 60, 2, "0", STR_PAD_LEFT);
                        $formattedTime = $minutes . ':' . $secs;
                        ?>
                        <p class="result-meta">⏱️ Timer: <?php echo $formattedTime; ?></p>

                        <?php
                        $createdDate = date('F j, Y', strtotime($result['date_created'] ?? ''));
                        ?>
                        <p class="result-meta">📅 Created: <?php echo $createdDate; ?></p>
                    </div>
                    </a>
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
