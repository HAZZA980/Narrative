<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Site Map | Narrative Learn</title>
    <link rel="stylesheet" href="<?php echo BASE_URL?>features/siteMap/css/styles-articles-sitemap-layout.css">
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
                    echo "<a href=\"features/siteMap/yearLayoutArticle.php?year={$year}\" class=\"{$activeClass}\">{$year}</a>";
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
                    $categoryUrl = BASE_URL . "explore/" . $file;
                    echo "<a href=\"{$categoryUrl}\">" . $category . "</a>";
                }
                ?>
            </div>
        </div>


     </div>
</main>

</body>
</html>
