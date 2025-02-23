<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'features/write/write-icon-fixed.php';

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="articleLayouts/layoutOne_7_12_Item.css">
    <title>Entertainment | Narrative</title>
</head>
<body>
<main class="main-container">
    <div class="main-content">
        <h1 class="main-content-title">Film & Cinema</h1>

        <div class="grid-container">
            <?php
            try {
                // Get the current file name dynamically
                $sql = "SELECT id, title, LEFT(content, 250) AS summary, datePublished, Tags, featured, Image, user_id FROM tbl_blogs WHERE Tags = 'Film & Cinema'";
                $result = $conn->query($sql);

                $i = 1; // Counter to track grid items
                while ($row = $result->fetch_assoc()) {
                    // Dynamically create grid items for each blog
                    ?>
                    <div class="grid-item p<?php echo $i; ?>">
                        <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['id']; ?>">
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
                                <a href="entertainment.php">
                                    <?php echo htmlspecialchars($row['Tags']); ?>
                                </a>
                            </p>
                            <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small></p>
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


        <h4 class="main-content-title">Remembering ...</h4>

        <div class="grid-container">
            <?php
            $sql = "SELECT id, title, LEFT(content, 250) AS summary, datePublished, Tags, featured, Image, user_id FROM tbl_blogs WHERE Tags = 'Actors'";
            $result = $conn->query($sql);

            $i = 1; // Counter to track grid items
            while ($row = $result->fetch_assoc()) {
                // Dynamically create grid items for each blog
                ?>
                <div class="grid-item <?php echo $i; ?>">
                    <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['id']; ?>">
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
                            <a href="entertainment.php">Film & Cinema</a>
                        </p>
                        <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small></p>
                    </div>
                </div>
                <?php
                $i++; // Increment the counter for the next blog
            }
            ?>
        </div>


    </div>
</main>

</body>
</html>

<?php $conn->close(); ?>
