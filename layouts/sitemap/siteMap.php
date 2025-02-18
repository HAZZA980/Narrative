<?php include "../../config/config.php" ?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Site Map | Narrative Learn</title>
    <style>
        /* General Reset */
        .main-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .main-content {
            width: 73%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
        }

        .site-header {
            margin-top: 1.5em;
            font-size: 2.5rem;
            color: #222;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .section-header {
            font-size: 1.75rem;
            color: #444;
            margin-bottom: 20px;
            font-weight: 500;
        }


        .yearSection, .categorySection {
            width: 100%;
        }

        /* Year Buttons */
        .year-buttons {
            margin-bottom: 40px;
            border-bottom: 1px solid #e5e5e5;
            width: 100%;
            padding-bottom: 20px;
        }

        .year-buttons a {
            display: inline-block;
            padding: 12px 20px;
            margin: 8px;
            font-size: 16px;
            color: #444;
            text-decoration: none;
            background-color: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .year-buttons a:hover {
            background-color: #1abc9c;
            color: #fff;
        }

        /* Category Links */
        .category-links {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 40px;
            border-bottom: 3px solid grey;
            width: 100%;
            padding-bottom: 20px;
        }

        .category-links a {
            padding: 14px 25px;
            font-size: 16px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            background-color: #fafafa;
            border: 1px solid #ddd;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
            text-transform: capitalize;
        }

        .category-links a:hover {
            background-color: #1abc9c;
            color: #fff;
            transform: scale(1.05);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .category-links a {
                flex-basis: calc(33.33% - 20px); /* 3 items per row for tablets */
            }
        }

        @media (max-width: 768px) {
            .category-links a {
                flex-basis: calc(50% - 20px); /* 2 items per row for small screens */
            }
        }

        @media (max-width: 480px) {
            .category-links a {
                flex-basis: 100%; /* Full width for very small screens */
            }
        }
    </style>
</head>
<body>

<main class="main-container">
    <div class="main-content">

        <!-- Article Heading -->
        <h1 class="site-header">Site Map</h1>

        <div class="yearSection">
            <h2 class="section-header">Articles by Year</h2>

            <!-- Year Links -->
            <div class="year-buttons">
                <?php
                $years = [2025, 2024, 2023, 2022, 2021];
                foreach ($years as $year) {
                    // Check if the current year matches the selected year in the URL (if any)
                    $activeClass = ($_GET['year'] == $year) ? 'active' : '';

                    // Link to the yearLayout.php page, passing the year as a query parameter
                    echo "<a href=\"yearLayoutArticle.php?year={$year}\" class=\"{$activeClass}\">{$year}</a>";
                }
                ?>
            </div>

        </div>


        <h2 class="section-header">Articles by Category</h2>

        <div class="categorySection">
            <div class="category-links">
                <?php
                // Array of categories and corresponding filenames
                $categoriess = [
                    "Lifestyle" => "lifestyle.php",
                    "Writing Craft" => "writing-craft.php",
                    "Travel" => "travel.php",
                    "Reviews" => "reviews.php",
                    "History" => "history.php",
                    "Entertainment" => "entertainment.php",
                    "Business" => "business.php",
                    "Technology" => "technology.php",
                    "Politics" => "politics.php",
                ];

                // Loop through categories and generate links dynamically
                foreach ($categoriess as $category => $file) {
                    // Clean category name for URL (lowercase and replace spaces with hyphens)
                    $categoryUrl = BASE_URL . "layouts/pages/articles/categories/" . $file;
                    echo "<a href=\"{$categoryUrl}\">" . $category . "</a>";
                }
                ?>
            </div>
        </div>


     </div>
</main>

</body>
</html>
