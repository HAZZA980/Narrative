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
    <title>Reviews | Narrative</title>
<!--    <link rel="stylesheet" href="articleLayouts/layoutFive_8_Items.css">-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>

        .carousel-item {
            min-height: 30rem; /* Consistent height */
        }

        .carousel-item .container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%; /* Ensure full height within each slide */
        }

        .carousel-item .row {
            width: 100%; /* Ensures content stretches properly */
        }

        .carousel-item img {
            width: 100%;
            height: 25rem; /* Fixed height */
            object-fit: cover; /* Ensures good cropping */
            border-radius: 10px;
        }

        .carousel-content {
            padding: 20px;
        }

        .carousel-content h5 {
            font-weight: 600;
            font-size: 30px;
            color: black;
        }

        .carousel-content p {
        }

        .carousel-caption {
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: black; /* Makes the icons black */
            border-radius: 50%; /* Optional: Makes them rounded */
            width: 50px; /* Adjust size */
            height: 50px;
        }

        .carousel-control-prev,
        .carousel-control-next {
            opacity: 1; /* Ensures full visibility */
        }

    </style>
</head>
<body>

<main class="main-container">
    <div class="main-content">

        <h1 class="main-content-title">Featured Reviews This Week</h1>
        <div class="grid-container">
            <?php

            $featured_ids = [];
            $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Tags, Image, user_id 
                    FROM tbl_blogs 
                    WHERE Category = 'Reviews' 
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
                            <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                        </div>
                    </a>
                    <div class="blog-details-2">
                        <p id="blog-tags">
                            <a href="<?php echo BASE_URL; ?>explore/lifestyle.php">
                                <?php echo htmlspecialchars($row['Tags']); ?>
                            </a>
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

    <div class="tab-content active main-content" id="play-reviews">
        <h4 class="main-content-title">Books of the Week</h4>

        <div id="playReviewsCarousel" class="carousel slide" data-bs-ride="carousel">
            <!-- Indicators -->
            <div class="carousel-indicators">
                <?php
                $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Category, Tags, Image, user_id 
                FROM tbl_blogs 
                WHERE Category = 'Reviews' AND private = 0 AND Tags like '%Book%' AND Featured = '0'
                ORDER BY datePublished DESC";

                $result = $conn->query($sql);
                $indicatorIndex = 0;
                while ($result->fetch_assoc()) {
                    echo '<button type="button" data-bs-target="#playReviewsCarousel" data-bs-slide-to="' . $indicatorIndex . '"' . ($indicatorIndex === 0 ? ' class="active"' : '') . ' aria-current="true" aria-label="Slide ' . ($indicatorIndex + 1) . '"></button>';
                    $indicatorIndex++;
                }
                ?>
            </div>


            <!-- Carousel Items -->
            <div class="carousel-inner">
                <?php
                $result->data_seek(0); // Reset the result pointer
                $isActive = true;
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="carousel-item <?php echo $isActive ? 'active' : ''; ?>">
                        <div class="container">
                            <div class="row align-items-center">
                                <!-- Left Side - Image -->
                                <div class="col-md-6">
                                    <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>">
                                        <img src="<?php echo BASE_URL . 'public/images/users/' . htmlspecialchars($row['user_id']) . '/' . htmlspecialchars($row['Image']); ?>"
                                             class="d-block w-100 rounded" alt="Play Image">
                                    </a>
                                </div>

                                <!-- Right Side - Text Content -->
                                <div class="col-md-6">
                                    <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>"
                                       style="text-decoration: none;">
                                        <div class="carousel-content">
                                            <h5><?php echo htmlspecialchars($row['title']); ?></h5>
                                            <p><?php echo htmlspecialchars($row['summary']); ?>...</p>
                                            <p>
                                                <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                                            </p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $isActive = false;
                }
                ?>
            </div>


            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#playReviewsCarousel"
                    data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#playReviewsCarousel"
                    data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>


    <div class="main-content">
        <h4 class="main-content-title">Shakespeare Reimagined</h4>
        <div class="grid-container">
            <?php
            $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Tags, Image, user_id 
                    FROM tbl_blogs 
                    WHERE Tags like '%Shakespeare Plays%' 
                      AND featured = 0 
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
                            <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                        </div>
                    </a>
                    <div class="blog-details-2">
                        <p id="blog-tags">
                            <a href="<?php echo BASE_URL; ?>explore/reviews.php">
                                <?php echo htmlspecialchars($row['Tags']); ?>
                            </a>
                        </p>
                        <p id="blog-date">
                            <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                        </p>
                    </div>
                </div>
                <?php
                $i++;
            }
            ?>
        </div>

        <h5 class="main-content-title">Latest Reviews</h5>
        <div class="latest-container">
            <?php
            // Query to get latest blogs
            $sql = "SELECT Id, Title, LEFT(Content, 230) AS summary, DatePublished, Tags, Image, user_id, Private
            FROM tbl_blogs WHERE Private = '0' AND Category = 'Reviews'
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
                        <div class="latest-grid-container" onclick="window.location.href='<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['Id']; ?>'">
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
                                        <p id="latest-blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                                    </div>
                                </div>
                                <div class="latest-grid-container-3">
                                    <p id="blog-tags">
                                        <?php
                                        if (!empty($row['Tags'])) {
                                            $tags = explode(",", $row['Tags']);
                                            $first_tag = trim($tags[0]); ?>
                                            <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($first_tag); ?>" class="tag-link">
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

            <!-- Load More / Change Less Button -->
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
                                            <small>${new Date(row.DatePublished).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" })}</small>
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
                        button.innerText = "Change Less";
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


        <h3 class="main-content-title">ELSEWHERE ON NARRATIVE</h3>
        <h5 class="main-content-title">History & Culture</h5>

        <div class="grid-container">
            <?php
            $sql = "SELECT id, title, LEFT(content, 250) AS summary, datePublished, Tags, featured, Image, user_id 
                    FROM tbl_blogs 
                    WHERE Category = 'History & Culture' 
                    ORDER BY RAND() 
                    LIMIT 5";
            $result = $conn->query($sql);

            $i = 1; // Counter to track grid items
            while ($row = $result->fetch_assoc()) {
                // Dynamically create grid items for each blog
                ?>
                <div class="grid-item <?php echo $i; ?>">
                    <a href="<?php echo BASE_URL ?>users/article.php?id=<?php echo $row['id']; ?>">
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
                            <a href="history-and-culture.php"><?php echo htmlspecialchars($row['Tags'])?></a>
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

        <h5 class="main-content-title">If you liked this then you might like this:</h5>

        <div class="grid-container">
            <?php
            $sql = "SELECT id, title, LEFT(content, 250) AS summary, datePublished, Tags, featured, Image, user_id FROM tbl_blogs WHERE Private = 0 AND Category = 'Entertainment' LIMIT 10";
            $result = $conn->query($sql);

            $i = 1; // Counter to track grid items
            while ($row = $result->fetch_assoc()) {
                // Dynamically create grid items for each blog
                ?>
                <div class="grid-item <?php echo $i; ?>">
                    <a href="<?php echo BASE_URL ?>users/article.php?id=<?php echo $row['id']; ?>">
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
                            <a href="<?php echo BASE_URL?>explore/entertainment.php"><?php echo htmlspecialchars($row['Tags'])?></a>
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


