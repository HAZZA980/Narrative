<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?php echo BASE_URL?>public/css/styles-articles-sitemap-layout.css">
    <title>Articles by Year | Narrative Learn</title>
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

        .section-header {
            margin-top: 1.5em;
            font-size: 2.5rem;
            color: #222;
            margin-bottom: 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<main class="main-container">
    <div class="main-content">
        <!-- Article Grid -->
        <?php
        try {
        // Get the selected year from the URL parameter
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y'); // Default to the current year if no year is selected
        ?>

        <h2 class="section-header"><?php echo $year ?> Articles</h2>

        <?php
        // Query to get featured blogs
        $sql = "SELECT id, title, DatePublished, Tags, Image, user_id FROM tbl_blogs WHERE DatePublished like '%$year%' ORDER BY DatePublished DESC";
        $result = $conn->query($sql);

        $i = 1; // Counter to track grid items
        ?>
        <div class="grid-container">
            <?php
            while ($row = $result->fetch_assoc()) {
                // Dynamically create grid items for each blog
                ?>
                <div class="grid-item p<?php echo $i; ?>">
                    <a href="<?php echo BASE_URL ?>layouts/pages/articles/article.php?id=<?php echo $row['id']; ?>">
                        <div class="image-container">
                            <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                        </div>
                        <div class="blog-details">
                            <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                            <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                        </div>
                    </a>
                    <div class="blog-details-2">
                        <p id="blog-tags">
                            <a href="<?php
                            // Normalize the tag to handle special characters like &amp;
                            $tag = trim(html_entity_decode($row['Tags']));
                            // Case-insensitive comparison
                            if (strcasecmp($tag, "Entertainment") == 0) {
                                echo "entertainment.php";
                            } else if (strcasecmp($tag, "Business") == 0) {
                                echo "business.php";
                            } else if (strcasecmp($tag, "History") == 0) {
                                echo "history-and-culture.php";
                            } else if (strcasecmp($tag, "Lifestyle") == 0) {
                                echo "lifestyle.php";
                            } else if (strcasecmp($tag, "Politics") == 0) {
                                echo "politics.php";
                            } else if (strcasecmp($tag, "Reviews") == 0) {
                                echo "reviews.php";
                            } else if (strcasecmp($tag, "Technology") == 0) {
                                echo "technology.php";
                            } else if (strcasecmp($tag, "Travel") == 0) {
                                echo "travel.php";
                            } else if (strcasecmp($tag, "Writing Craft") == 0) {
                                echo "writing-craft.php";

                            } else {
                                echo "#"; // Fallback link if no match is found
                            }
                            ?>">
                                <?php echo htmlspecialchars($row['Tags']); ?>
                            </a>
                        </p>
                        <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['date'])); ?></small></p>
                    </div>
                </div>
                <?php
                $i++; // Increment the counter for the next blog
            }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            ?>
        </div>

    </div>
</main>

</body>
</html>
