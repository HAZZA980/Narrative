<?php
include $_SERVER["DOCUMENT_ROOT"] . "/phpProjects/narrative/config/config.php";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="articleLayouts/layoutFive_8_Items.css">
    <title>Articles Home | Narrative</title>
    <style>
        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination-link {
            margin: 0 5px;
            padding: 10px;
            text-decoration: none;
            background-color: #f1f1f1;
            color: #333;
            border: 1px solid #ccc;
        }

        .pagination-link.active {
            background-color: #007BFF;
            color: white;
        }

        .pagination-link:hover {
            background-color: #ddd;
        }

    </style>
</head>
<body>

<main class="main-container">

    <div class="main-content">
        <?php
        // Pagination logic
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $results_per_page = ($page == 1) ? 10 : 25; // 10 blogs on page 1, 25 on subsequent pages
        $start_from = ($page - 1) * $results_per_page;
        ?>

        <?php if ($page == 1): ?>
            <!-- Featured Blogs on Page 1 -->
            <h1 class="main-content-title">Featured</h1>
            <div class="grid-container">
                <?php
                // Query to get featured blogs
                $sql = "SELECT Id, Title, LEFT(Content, 270) AS summary, DatePublished, Tags, Image, user_id 
                        FROM tbl_blogs 
                        WHERE Featured = '1' 
                        ORDER BY DatePublished DESC LIMIT 12";
                $result = $conn->query($sql);

                $i = 1; // Counter to track grid items
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="grid-item p<?php echo $i; ?>">
                        <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['Id']; ?>">
                            <div class="image-container">
                                <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                    ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                    : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                            </div>
                            <div class="blog-details">
                                <h2 id="blog-title"><?php echo htmlspecialchars($row['Title']); ?></h2>
                                <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                            </div>
                        </a>
                        <div class="blog-details-2">
                            <p id="blog-tags">
                                <a href="#"><?php echo htmlspecialchars($row['Tags']); ?></a>
                            </p>
                            <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['DatePublished'])); ?></small></p>
                        </div>
                    </div>
                    <?php
                    $i++; // Increment the counter for the next blog
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Pagination Links -->
        <?php
        // Calculate total number of pages based on 25 entries per page for consistency
        $sql_total = "SELECT COUNT(Id) AS total FROM tbl_blogs WHERE Type = 'Article'";
        $result_total = $conn->query($sql_total);
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];
        $results_per_page = 25; // Set results per page to 25 for all pages
        $total_pages = ceil($total_records / $results_per_page); // Total pages based on 25 per page
        ?>

        <div class="pagination">
            <?php
            // Display previous page link
            if ($page > 1) {
                echo "<a href='home.php?page=" . ($page - 1) . "' class='pagination-link'>Previous</a>";
            }

            // Display page links
            for ($i = 1; $i <= $total_pages; $i++) {
                $active_class = ($i == $page) ? 'active' : '';
                echo "<a href='home.php?page=$i' class='pagination-link $active_class'>$i</a>";
            }

            // Display next page link
            if ($page < $total_pages) {
                echo "<a href='home.php?page=" . ($page + 1) . "' class='pagination-link'>Next</a>";
            }
            ?>
        </div>
        <h4 class="main-content-title">Latest Blogs</h4>

        <div class="latest-container">
            <?php
            // Query to get latest blogs with pagination
            $sql = "SELECT Id, Title, LEFT(Content, 250) AS summary, DatePublished, Tags, Image, user_id 
                    FROM tbl_blogs 
                    ORDER BY DatePublished DESC 
                    LIMIT $results_per_page OFFSET $start_from";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="latest">
                    <div class="latest-grid-container" onclick=window.location.href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['Id']; ?>">
                        <div class="latest-grid-item-1">
                            <div class="latest-image-container">
                                <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
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
                                <p class="latest-blog-tags">
                                    <a id="latest-tag" href="#"><?php echo htmlspecialchars($row['Tags']); ?></a>
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

        <div class="pagination">
            <?php
            // Display previous page link
            if ($page > 1) {
                echo "<a href='home.php?page=" . ($page - 1) . "' class='pagination-link'>Previous</a>";
            }

            // Display page links
            for ($i = 1; $i <= $total_pages; $i++) {
                $active_class = ($i == $page) ? 'active' : '';
                echo "<a href='home.php?page=$i' class='pagination-link $active_class'>$i</a>";
            }

            // Display next page link
            if ($page < $total_pages) {
                echo "<a href='home.php?page=" . ($page + 1) . "' class='pagination-link'>Next</a>";
            }
            ?>
        </div>
    </div>
</main>

</body>
</html>

<?php $conn->close(); ?>

