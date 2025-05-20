<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    <title>Philosophy | Narrative</title>
</head>
<body>

<main class="main-container">
    <div class="main-content">

        <h1 class="main-content-title">Featured This Week</h1>
        <div class="grid-container">
            <?php

            $featured_ids = [];
            $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Tags, Image, user_id 
                    FROM tbl_blogs 
                    WHERE Category = 'Philosophy' 
                      AND featured = 1 
                      AND Private = 0 
                    ORDER BY datePublished DESC 
                    LIMIT 12";
            $result = $conn->query($sql);

            $i = 1;
            while ($row = $result->fetch_assoc()) {
                $featured_ids[] = $row['id'];
                ?>
                <div class="grid-item p<?php echo $i; ?>">
                    <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>">
                        <div class="image-container">
                            <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                        </div>
                        <div class="blog-details">
                            <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                            <p id="blog-content"><?php echo strip_tags($row['summary']); ?>...</p>
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
                        <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                        </p>
                    </div>
                </div>
                <?php
                $i++;
            }
            ?>
        </div>
    </div>

    <div class="main-content">
        <h4 class="main-content-title">THERE ARE NO ARTICLES IN THIS CATEGORY YET!</h4>
        <div class="grid-container">
            <?php
            $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Tags, Image, user_id 
                    FROM tbl_blogs 
                    WHERE Tags like '%altruism%' 
                      AND featured = 0 
                      AND Private = 0
                    ORDER BY datePublished DESC 
                    LIMIT 12";
            $result = $conn->query($sql);

            $i = 1;
            while ($row = $result->fetch_assoc()) {
                $featured_ids[] = $row['id'];

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
                            <p id="blog-content"><?php echo strip_tags($row['summary']); ?>...</p>
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
                        <p id="blog-date">
                            <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                        </p>
                    </div>
                </div>
                <?php
                $i++;
            }

            if ($result->num_rows > 0) {
                $article = $result->fetch_assoc();
                // Display article
            } else {
                echo "";
            }

            ?>
        </div>




        <h5 class="main-content-title">Latest</h5>
        <div class="latest-container">
            <?php
            // Query to get latest blogs
            $sql = "SELECT Id, Title, LEFT(Content, 180) AS summary, DatePublished, Tags, Image, user_id, Private
            FROM tbl_blogs WHERE Private = '0' AND Category = 'Philosophy'
            ORDER BY DatePublished DESC";
            $result = $conn->query($sql);

            $blogs = [];
            while ($row = $result->fetch_assoc()) {
                $blogs[] = $row;
            }
            ?>

            <div id="blog-list">
                <?php
                // Show only the first 6 articles initially
                for ($i = 0; $i < min(6, count($blogs)); $i++) {
                    $row = $blogs[$i];
                    ?>
                    <div class="latest">
                        <div class="latest-grid-container"
                             onclick="window.location.href='<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['Id']; ?>'">
                            <div class="latest-grid-item-1">
                                <div class="latest-image-container">
                                    <img src="<?php echo !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                        ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                        : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                                </div>
                            </div>
                            <div class="latest-grid-container-2">
                                <div class="latest-grid-item-2">
                                    <div class="latest-content">
                                        <h5 id="latest-blog-title"><?php echo htmlspecialchars($row['Title']); ?></h5>
                                        <p id="latest-blog-content"><?php echo strip_tags($row['summary']); ?>
                                            ...</p>
                                    </div>
                                </div>
                                <div class="latest-grid-container-3">
                                    <p id="blog-tags">
                                        <?php
                                        if (!empty($row['Tags'])) {
                                            $tags = explode(",", $row['Tags']);
                                            $first_tag = trim($tags[0]); ?>
                                            <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($first_tag); ?>"
                                               class="tag-link">
                                                <?php echo htmlspecialchars($first_tag); ?>
                                            </a>
                                        <?php } else {
                                            echo "<span>Uncategorized</span>";
                                        } ?>
                                    </p>
                                    <div class="latest-grid-item-3">
                                        <p id="latest-blog-date">
                                            <small><?php echo date('F j, Y', strtotime($row['DatePublished'])); ?></small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- Load More / Show Less Button -->
            <div style="text-align: center; margin-top: 20px;">
                <button id="loadMoreBtn" onclick="loadMoreArticles()">Load More</button>
            </div>

        </div>

        <script>
            let allBlogs = <?php echo json_encode($blogs); ?>; // Full blog list from PHP
            let currentIndex = 6; // Start after the first 6
            let isExpanded = false;

            function loadMoreArticles() {
                let blogList = document.getElementById("blog-list");
                let button = document.getElementById("loadMoreBtn");

                if (!isExpanded) {
                    let newContent = "";
                    for (let i = currentIndex; i < Math.min(currentIndex + 6, allBlogs.length); i++) {
                        let row = allBlogs[i];
                        newContent += `
                    <div class="latest">
                        <div class="latest-grid-container" onclick="window.location.href='${"<?php echo BASE_URL ?>"}/user/article.php?id=${row.Id}'">
                            <div class="latest-grid-item-1">
                                <div class="latest-image-container">
                                    <img src="${row.Image && row.Image !== 'narrative-logo-big.png'
                            ? "<?php echo BASE_URL ?>public/images/users/" + row.user_id + "/" + row.Image
                            : "<?php echo BASE_URL ?>narrative-logo-big.png"}" alt="Blog Image">
                                </div>
                            </div>
                            <div class="latest-grid-container-2">
                                <div class="latest-grid-item-2">
                                    <div class="latest-content">
                                        <h5 id="latest-blog-title">${row.Title}</h5>
                                        <p id="latest-blog-content">${row.summary}...</p>
                                    </div>
                                </div>
                                <div class="latest-grid-container-3">
                                    <p id="blog-tags">
                                        ${row.Tags ? `<a href="<?php echo BASE_URL; ?>tag.php?tag=${encodeURIComponent(row.Tags.split(",")[0])}" class="tag-link">
                                            ${row.Tags.split(",")[0]}
                                        </a>` : "<span>Uncategorized</span>"}
                                    </p>
                                    <div class="latest-grid-item-3">
                                        <p id="latest-blog-date">
                                            <small>${new Date(row.DatePublished).toLocaleDateString("en-US", {
                            month: "long",
                            day: "numeric",
                            year: "numeric"
                        })}</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    }

                    blogList.innerHTML += newContent;
                    currentIndex += 6;

                    if (currentIndex >= allBlogs.length) {
                        button.innerText = "Show Less";
                        isExpanded = true;
                    }
                } else {
                    blogList.innerHTML = blogList.innerHTML.split("</div>").slice(0, 6).join("</div>") + "</div>";
                    button.innerText = "Load More";
                    currentIndex = 6;
                    isExpanded = false;
                }
            }


        </script>

        <div class="elsewhere-section">
            <h3 class="main-content-title">ELSEWHERE ON NARRATIVE</h3>
            <h5 class="main-content-title">Lifestyle</h5>

            <div class="grid-container">
                <?php
                $sql = "SELECT id, title, LEFT(content, 180) AS summary, datePublished, Tags, featured, Image, user_id 
                    FROM tbl_blogs 
                    WHERE Category = 'Lifestyle' 
                    ORDER BY RAND() 
                    LIMIT 5";
                $result = $conn->query($sql);

                $i = 1; // Counter to track grid items
                while ($row = $result->fetch_assoc()) {
                    // Dynamically create grid items for each blog
                    ?>
                    <div class="grid-item <?php echo $i; ?>">
                        <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>">
                            <div class="image-container">
                                <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                    ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                    : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                            </div>
                            <div class="blog-details">
                                <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                                <p id="blog-content"><?php echo strip_tags($row['summary']); ?>...</p>
                            </div>
                        </a>
                        <div class="blog-details-2">
                            <p id="blog-tags">
                                <?php
                                if (!empty($row['Tags'])) {
                                    $tags = explode(",", $row['Tags']);
                                    $first_tag = trim($tags[0]); ?>
                                    <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($first_tag); ?>"
                                       class="tag-link">
                                        <?php echo htmlspecialchars($first_tag); ?>
                                    </a>
                                <?php } else {
                                    echo "<span>Uncategorized</span>";
                                } ?>
                            </p>
                            <p id="blog-date">
                                <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                            </p>
                        </div>
                    </div>
                    <?php
                    $i++; // Increment the counter for the next blog
                }
                ?>
            </div>

            <h5 class="main-content-title">If you liked that then you might like this:</h5>

            <div class="grid-container">
                <?php
                $sql = "SELECT id, title, LEFT(content, 180) AS summary, datePublished, Tags, featured, Image, user_id 
                        FROM tbl_blogs WHERE Private = 0 AND Category = 'Business' ORDER BY RAND() LIMIT 10";
                $result = $conn->query($sql);

                $i = 1; // Counter to track grid items
                while ($row = $result->fetch_assoc()) {
                    // Dynamically create grid items for each blog
                    ?>
                    <div class="grid-item <?php echo $i; ?>">
                        <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>">
                            <div class="image-container">
                                <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                    ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                    : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                            </div>
                            <div class="blog-details">
                                <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                                <p id="blog-content"><?php echo strip_tags($row['summary']); ?>...</p>
                            </div>
                        </a>
                        <div class="blog-details-2">
                            <p id="blog-tags">
                                <?php
                                if (!empty($row['Tags'])) {
                                    $tags = explode(",", $row['Tags']);
                                    $first_tag = trim($tags[0]); ?>
                                    <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($first_tag); ?>"
                                       class="tag-link">
                                        <?php echo htmlspecialchars($first_tag); ?>
                                    </a>
                                <?php } else {
                                    echo "<span>Uncategorized</span>";
                                } ?>
                            </p>
                            <p id="blog-date">
                                <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                            </p>
                        </div>
                    </div>
                    <?php
                    $i++; // Increment the counter for the next blog
                }
                ?>
            </div>

        </div>


    </div>
</main>

<script>
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });
</script>
</body>
</html>


