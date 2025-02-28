<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'features/write/write-icon-fixed.php';
include BASE_PATH . 'model/subcategories.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Site Map | Narrative Learn</title>
    <link rel="stylesheet" href="<?php echo BASE_URL?>features/siteMap/css/styles-articles-sitemap-layout.css">
    <style>
        /* Main container */
        .TagSection {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
        }

        /* Accordion button */
        .accordion {
            font-size: 1.2em;
            font-weight: bold;
            background-color: #f4f4f4;
            padding: 10px;
            width: 100%;
            text-align: left;
            border: 1px solid #ddd;
            cursor: pointer;
            outline: none;
            transition: 0.3s;
        }

        .accordion:hover {
            background-color: #ddd;
        }

        /* Active accordion */
        .accordion.active {
            background-color: #ccc;
        }

        /* Accordion content (hidden by default) */
        .accordion-content {
            display: none;
            overflow: hidden;
            padding: 10px;
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        /* Table for tags */
        .tag-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        /* Table row */
        .tag-table td {
            border: 1px solid #ddd;
            text-align: center;
        }

        /* Tag links */
        .tag-table a {
            text-decoration: none;
            color: #0073e6;
            display: block;
            padding: 5px;
        }

        .tag-table a:hover {
            text-decoration: underline;
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
                    "Business" => "business.php",
                    "Entertainment" => "entertainment.php",
                    "Food" => "food.php",
                    "Gaming" => "gaming.php",
                    "Health" => "health.php",
                    "History" => "history-and-culture.php",
                    "Lifestyle" => "lifestyle.php",
                    "Philosophy" => "philosophy.php",
                    "Politics" => "politics.php",
                    "Reviews" => "reviews.php",
                    "Sports" => "sports.php",
                    "Science" => "science.php",
                    "Travel" => "travel.php",
                    "Technology" => "technology.php",
                    "Writing Craft" => "writing-craft.php",
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

        <h2 class="section-header">Articles by Tags</h2>

        <div class="TagSection">
            <?php
            if (!empty($subcategories)) {
                foreach ($subcategories as $category => $tags) {
                    echo "<button class='accordion'>" . htmlspecialchars($category) . "</button>";
                    echo "<div class='accordion-content'><table class='tag-table'><tbody><tr>";

                    $count = 0; // Track number of tags in current row
                    foreach ($tags as $tag) {
                        // Create a URL-friendly version of the tag
                        $tagUrl = BASE_URL . "tag.php?tag=" . urlencode($tag);

                        echo "<td><a href=\"{$tagUrl}\">" . htmlspecialchars($tag) . "</a></td>";
                        $count++;

                        // Start a new row every 7 tags
                        if ($count % 5 === 0) {
                            echo "</tr><tr>";
                        }
                    }

                    // Fill empty cells if the last row has less than 7 items
                    while ($count % 5 !== 0) {
                        echo "<td></td>";
                        $count++;
                    }

                    echo "</tr></tbody></table></div>";
                }
            } else {
                echo "<p>No tags available.</p>";
            }
            ?>
        </div>

</main>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const accordions = document.querySelectorAll(".accordion");

        accordions.forEach((accordion) => {
            accordion.addEventListener("click", function () {
                this.classList.toggle("active");
                let content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        });
    });

</script>
</body>
</html>
