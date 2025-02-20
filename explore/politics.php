<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="articleLayouts/layoutOne_7_12_Item.css">
    <title>Politics | Narrative</title>
    <style>
    </style>
</head>
<body>

<main class="main-container">

    <div class="main-content">
        <h1 class="main-content-title">Politics</h1>

         <div class="grid-container">
             <?php
             // Query to get latest blogs excluding featured blogs
             $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Tags, Image, user_id 
            FROM tbl_blogs 
            WHERE (Tags like '%politics') 
              AND featured = 1 
              AND Private = 0 
            ORDER BY datePublished DESC 
            LIMIT 12";
             $result = $conn->query($sql);

             $i = 1; // Counter to track grid items
             while ($row = $result->fetch_assoc()) {
                 ?>
                    <div class="grid-item p<?php echo $i; ?>">
                        <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['id']; ?>">
                            <div class="image-container">
                                <img src="<?php echo BASE_URL . 'public/images/users/' . htmlspecialchars($row['user_id']) . '/' . htmlspecialchars($row['Image']); ?>" alt="Article Image">
                            </div>
                            <div class="blog-details">
                                <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?>

                                </h2>
                                <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                            </div>
                        </a>
                        <div class="blog-details-2">
                            <p id="blog-tags">
                                <a href="<?php echo BASE_URL; ?>explore/lifestyle.php">
                                    <?php echo htmlspecialchars($row['Tags']); ?>
                                </a>
                            </p>
                            <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small></p>
                        </div>
                    </div>
                    <?php
                    $i++; // Increment the counter for the next blog
                }
                ?>
            </div>


        <h4 class="main-content-title">American Politics</h4>

        <div class="grid-container">
            <?php
            // Query to get latest blogs excluding featured blogs
            $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Tags, Image, user_id 
            FROM tbl_blogs 
            WHERE (Tags = 'US Politics') 
              AND featured = 0 
              AND Private = 0 
            ORDER BY datePublished DESC 
            LIMIT 12";
            $result = $conn->query($sql);

            $i = 1; // Counter to track grid items
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="grid-item p<?php echo $i; ?>">
                    <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['id']; ?>">
                        <div class="image-container">
                            <img src="<?php echo BASE_URL . 'public/images/users/' . htmlspecialchars($row['user_id']) . '/' . htmlspecialchars($row['Image']); ?>"
                                 alt="Article Image">
                        </div>
                        <div class="blog-details">
                            <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?>

                            </h2>
                            <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                        </div>
                    </a>
                    <div class="blog-details-2">
                        <p id="blog-tags">
                            <a href="<?php echo BASE_URL; ?>explore/politics.php">
                                <?php echo htmlspecialchars($row['Tags']); ?>
                            </a>
                        </p>
                        <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                        </p>
                    </div>
                </div>
                <?php
                $i++; // Increment the counter for the next blog
            }
            ?>
        </div>



        <h4 class="main-content-title">What's Happening Across Europe</h4>

        <div class="grid-container">
            <?php
            // Query to get latest blogs excluding featured blogs
            $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Tags, Image, user_id 
            FROM tbl_blogs 
            WHERE (Tags = 'European Politics') 
              AND featured = 0 
              AND Private = 0 
            ORDER BY datePublished DESC 
            LIMIT 12";
            $result = $conn->query($sql);

            $i = 1; // Counter to track grid items
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="grid-item p<?php echo $i; ?>">
                    <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['id']; ?>">
                        <div class="image-container">
                            <img src="<?php echo BASE_URL . 'public/images/users/' . htmlspecialchars($row['user_id']) . '/' . htmlspecialchars($row['Image']); ?>"
                                 alt="Article Image">
                        </div>
                        <div class="blog-details">
                            <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?>

                            </h2>
                            <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                        </div>
                    </a>
                    <div class="blog-details-2">
                        <p id="blog-tags">
                            <a href="<?php echo BASE_URL; ?>layouts/pages/articles/categories/politics.php">
                                <?php echo htmlspecialchars($row['Tags']); ?>
                            </a>
                        </p>
                        <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                        </p>
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
