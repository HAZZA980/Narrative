<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?php echo BASE_URL?>explore/articleLayouts/layoutOne_7_12_item.css">

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
                <div class="grid-item">
                    <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>">
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
                            <?php
                            if (!empty($row['Tags'])) {
                                // Explode tags by comma and trim whitespace
                                $tags = explode(",", $row['Tags']);
                                $first_tag = trim($tags[0]); // Get the first tag
                                ?>
                                <!-- Tag link to feed.php with tag query -->
                                <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($first_tag); ?>"
                                   class="tag-link">
                                    <?php echo htmlspecialchars($first_tag); ?>
                                </a>
                                <?php
                            } else {
                                echo "<span>Uncategorized</span>";
                            }
                            ?>


                        </p>
                        <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['DatePublished'])); ?></small></p>
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
