<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'includes/quiz-header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .category-box {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 250px;
            text-align: center;
            overflow: hidden;
            transition: transform 0.3s ease;
            display: block;
        }

        .category-box:hover {
            transform: scale(1.05);
        }

        .category-box img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 2px solid #ddd;
        }

        .category-box h3 {
            font-size: 20px;
            margin: 15px 0;
            color: #333;
        }

        .category-box a {
            text-decoration: none;
            color: inherit;
        }

        .category-box a:hover {
            color: inherit;
        }

        .nav-categories {
            border-bottom: white 6px solid !important;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Entertainment Category -->
    <a href="quiz-search.php?txt-search=&category=entertainment" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/quiz/ham.webp" alt="Entertainment">
        <h3>Entertainment</h3>
    </a>

    <!-- Geography Category -->
    <a href="quiz-search.php?txt-search=&category=geography" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/users/52/geography.jpg" alt="Geography">
        <h3>Geography</h3>
    </a>

    <!-- History Category -->
    <a href="quiz-search.php?txt-search=&category=history" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/users/52/Napoleon.jpg" alt="History">
        <h3>History</h3>
    </a>

    <!-- IT Category -->
    <a href="quiz-search.php?txt-search=&category=IT" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/quiz/IT.jpg" alt="IT">
        <h3>IT</h3>
    </a>

    <!-- Language Category -->
    <a href="quiz-search.php?txt-search=&category=language" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/quiz/languages-signpost.jpg" alt="Language">
        <h3>Language</h3>
    </a>

    <!-- Literature Category -->
    <a href="quiz-search.php?txt-search=&category=literature" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/users/52/shakespeare.jpg" alt="Literature">
        <h3>Literature</h3>
    </a>

    <!-- Miscellaneous Category -->
    <a href="quiz-search.php?txt-search=&category=miscellaneous" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/quiz/msc.png" alt="Miscellaneous">
        <h3>Miscellaneous</h3>
    </a>

    <!-- Music Category -->
    <a href="quiz-search.php?txt-search=&category=music" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/quiz/tata-ed.webp" alt="Music">
        <h3>Music</h3>
    </a>

    <!-- Movies Category -->
    <a href="quiz-search.php?txt-search=&category=movies" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/users/52/The-Philadelphia-Story-009.webp" alt="TV">
        <h3>Movies</h3>
    </a>

    <!-- Science Category -->
    <a href="quiz-search.php?txt-search=&category=science" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/users/52/science.jpg" alt="Science">
        <h3>Science</h3>
    </a>

    <!-- Sports Category -->
    <a href="quiz-search.php?txt-search=&category=sport" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/quiz/messi.jpg" alt="Sports">
        <h3>Sports</h3>
    </a>

    <!-- TV Category -->
    <a href="quiz-search.php?txt-search=&category=tv" class="category-box">
        <img src="<?php echo BASE_URL?>public/images/quiz/ofah.jpg" alt="TV">
        <h3>TV</h3>
    </a>

</div>

</body>
</html>
