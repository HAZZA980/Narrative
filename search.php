<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'features/write/write-icon-fixed.php';
include BASE_PATH . 'features/search/search-logic.php';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Content | Narrative Learn</title>
    <link rel="stylesheet" href="features/pagination/css/pagination.css">
    <link rel="stylesheet" href="features/search/css/styles-search.css">
    <link rel="stylesheet" href="features/search/css/search-filter.css">
    <link rel="stylesheet" href="explore/articleLayouts/styles-default-article-formation.css">
    <link rel="stylesheet" href="account/css/feed.css">
</head>
<body>

<main class="search-page-main-container">
    <section class="search-page-main-content">
        <div class="search">
            <img class="header-links-img" src="public/images/header-img/search.png">
            <form method="get" action="search.php" class="form-bar">
                <input id="text-search-bar" type="text" name="txt-search" placeholder="Search Narrative"
                       value="<?php echo $search; ?>" autocomplete="off">
                <input type="submit" value="Search" id="btn-search">
            </form>
        </div>


        <div class="pagination-container">
            <!-- Filter Button -->
            <div class="filter-order-buttons">
                <!-- Order Button -->
                <div class="order-dropdown">
                    <a href="#"><img src="<?php echo BASE_URL; ?>public/images/pagination/order.svg" alt="Order" title="Order"></a>
                    <div class="dropdown-content">
                        <?php
                        // Retain all current filters in ordering links
                        $queryParams = [
                            'tab' => htmlspecialchars($tab),
                            'txt-search' => urlencode($search),
                            'author' => isset($_GET['author']) ? $_GET['author'] : '0',
                            'title' => isset($_GET['title']) ? $_GET['title'] : '0',
                            'content' => isset($_GET['content']) ? $_GET['content'] : '0',
                            'startDate' => isset($_GET['startDate']) ? $_GET['startDate'] : '',
                            'endDate' => isset($_GET['endDate']) ? $_GET['endDate'] : '',
                            'categories' => isset($_GET['categories']) ? $_GET['categories'] : '',
                            'page' => isset($_GET['page']) ? $_GET['page'] : 1 // Include the current page for pagination
                        ];

                        // Generate correct query string for the order links
                        $queryString = http_build_query($queryParams);
                        ?>

<!--                        <a href="?--><?php //echo $queryString; ?><!--&order_by=datePublished&order_dir=ASC">Order By Date (Ascending)</a>-->
                        <a href="?<?php echo $queryString; ?>&order_by=datePublished&order_dir=DESC">Order By Date</a>
                        <a href="?<?php echo $queryString; ?>&order_by=alphabetical&order_dir=ASC">Order Alphabetically</a>
<!--                        <a href="?--><?php //echo $queryString; ?><!--&order_by=alphabetical&order_dir=DESC">Order Alphabetically (Descending)</a>-->
                    </div>
                </div>
            <!-- Filter Button -->
                <a href="#" id="openFilterModal"><img src="<?php echo BASE_URL; ?>public/images/pagination/filter.svg"
                                                      alt="Filter"
                                                      title="Filter"></a>
            </div>


            <!-- Filter Modal -->
            <div id="filterModal" class="modal">
                <div class="modal-content">
                    <h2>Advanced Search</h2>

                    <!-- Checkbox Filters -->
                    <div class="filter-options">
                        <label><input type="checkbox" id="filter-author" checked> Author</label>
                        <label><input type="checkbox" id="filter-title" checked> Title</label>
                        <label><input type="checkbox" id="filter-content" checked> Content</label>
                    </div>

                    <div class="filter-options date-filters">
                        <h3 class="filter-title">Filter By Date</h3>
                        <div class="date-range">
                            <div class="date-item">
                                <label for="start-date">Date From</label>
                                <input type="date" id="start-date">
                            </div>
                            <div class="date-item">
                                <label for="end-date">Date To</label>
                                <input type="date" id="end-date">
                            </div>
                        </div>
                    </div>


                    <!-- Category Filters -->
                    <h3 class="filter-title">Filter By Category</h3>

                    <div class="category-buttons">

                        <?php
                        $categories = ["Lifestyle", "Writing Craft", "Travel", "Reviews", "History and Culture", "Entertainment", "Business", "Technology", "Politics", "Science", "Sports", "Health and Fitness", "Food", "Gaming", "Philosophy"];

                        foreach ($categories as $category): ?>
                            <button class="category-btn" data-category="<?php echo htmlspecialchars($category); ?>">
                                <?php echo htmlspecialchars($category); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="modal-footer">
                        <button id="applyFilters">Apply Filters</button>
                    </div>
                </div>
            </div>


            <div class="pagination">
                <?php if ($total_pages > 1): ?>
                    <!-- Previous Button -->
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&txt-search=<?php echo urlencode($search); ?>&author=<?php echo isset($_GET['author']) ? $_GET['author'] : '0'; ?>&title=<?php echo isset($_GET['title']) ? $_GET['title'] : '0'; ?>&content=<?php echo isset($_GET['content']) ? $_GET['content'] : '0'; ?>&startDate=<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : ''; ?>&endDate=<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : ''; ?>&categories=<?php echo isset($_GET['categories']) ? $_GET['categories'] : ''; ?>&order_by=<?php echo urlencode($order_by); ?>&order_dir=<?php echo urlencode($order_dir); ?>">Previous</a>
                    <?php endif; ?>

                    <!-- First Page -->
                    <a href="?page=1&txt-search=<?php echo urlencode($search); ?>&author=<?php echo isset($_GET['author']) ? $_GET['author'] : '0'; ?>&title=<?php echo isset($_GET['title']) ? $_GET['title'] : '0'; ?>&content=<?php echo isset($_GET['content']) ? $_GET['content'] : '0'; ?>&startDate=<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : ''; ?>&endDate=<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : ''; ?>&categories=<?php echo isset($_GET['categories']) ? $_GET['categories'] : ''; ?>&order_by=<?php echo urlencode($order_by); ?>&order_dir=<?php echo urlencode($order_dir); ?>"
                       class="<?php echo ($current_page == 1) ? 'active' : ''; ?>">1</a>

                    <!-- Ellipsis Before Middle Pages -->
                    <?php if ($current_page > 4): ?>
                        <span>...</span>
                    <?php endif; ?>

                    <!-- Display 2 Pages Before and After Current Page -->
                    <?php
                    $start_page = max(2, $current_page - 1);
                    $end_page = min($total_pages - 1, $current_page + 1);

                    for ($page = $start_page; $page <= $end_page; $page++): ?>
                        <a href="?page=<?php echo $page; ?>&txt-search=<?php echo urlencode($search); ?>&author=<?php echo isset($_GET['author']) ? $_GET['author'] : '0'; ?>&title=<?php echo isset($_GET['title']) ? $_GET['title'] : '0'; ?>&content=<?php echo isset($_GET['content']) ? $_GET['content'] : '0'; ?>&startDate=<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : ''; ?>&endDate=<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : ''; ?>&categories=<?php echo isset($_GET['categories']) ? $_GET['categories'] : ''; ?>&order_by=<?php echo urlencode($order_by); ?>&order_dir=<?php echo urlencode($order_dir); ?>"
                           class="<?php echo ($current_page == $page) ? 'active' : ''; ?>">
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Ellipsis Before Last Page -->
                    <?php if ($current_page < $total_pages - 3): ?>
                        <span>...</span>
                    <?php endif; ?>

                    <!-- Last Page -->
                    <?php if ($total_pages > 1): ?>
                        <a href="?page=<?php echo $total_pages; ?>&txt-search=<?php echo urlencode($search); ?>&author=<?php echo isset($_GET['author']) ? $_GET['author'] : '0'; ?>&title=<?php echo isset($_GET['title']) ? $_GET['title'] : '0'; ?>&content=<?php echo isset($_GET['content']) ? $_GET['content'] : '0'; ?>&startDate=<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : ''; ?>&endDate=<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : ''; ?>&categories=<?php echo isset($_GET['categories']) ? $_GET['categories'] : ''; ?>&order_by=<?php echo urlencode($order_by); ?>&order_dir=<?php echo urlencode($order_dir); ?>"
                           class="<?php echo ($current_page == $total_pages) ? 'active' : ''; ?>">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>

                    <!-- Next Button -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&txt-search=<?php echo urlencode($search); ?>&author=<?php echo isset($_GET['author']) ? $_GET['author'] : '0'; ?>&title=<?php echo isset($_GET['title']) ? $_GET['title'] : '0'; ?>&content=<?php echo isset($_GET['content']) ? $_GET['content'] : '0'; ?>&startDate=<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : ''; ?>&endDate=<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : ''; ?>&categories=<?php echo isset($_GET['categories']) ? $_GET['categories'] : ''; ?>&order_by=<?php echo urlencode($order_by); ?>&order_dir=<?php echo urlencode($order_dir); ?>">Next</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>




            <!-- Ascending and Descending Buttons -->
            <!-- Ascending and Descending Buttons -->
            <div class="asc-desc">
                <a href="?<?php echo htmlspecialchars($queryString); ?>&order_dir=ASC">
                    <img src="<?php echo BASE_URL; ?>public/images/pagination/arrow-up.svg" alt="Ascending"
                         title="Ascending">
                </a>

                <a href="?<?php echo htmlspecialchars($queryString); ?>&order_dir=DESC">
                    <img src="<?php echo BASE_URL; ?>public/images/pagination/arrow-down.svg" alt="Descending"
                         title="Descending">
                </a>
            </div>
        </div>


        <div class="flex-container">
            <?php if ($blogs_result->num_rows > 0): ?>
                <?php while ($row = $blogs_result->fetch_assoc()): ?>
                    <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                        <!-- User is the author of the article -->
                        <div class="flex-item">
                            <?php if ($row['private'] == 1): ?>
                                <div class="private-overlay">
                                    <p>This blog is set to private</p>
                                </div>
                            <?php endif; ?>
                            <div class="article-author-and-topic">
                                <div class="inter">
                                    <span class="aa" id="writing-about">You are writing about </span>
                                    <span>
                            <?php
                            if (!empty($row['Tags'])) {
                                // Explode tags by comma and trim whitespace
                                $tags = explode(",", $row['Tags']);
                                $first_tag = trim($tags[0]); // Get the first tag
                                ?>
                                <!-- Tag link to feed.php with tag query -->
                                <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($first_tag); ?>"
                                   class="tag-link" style="color: firebrick">
                                    <?php echo htmlspecialchars($first_tag); ?>
                                </a>
                                <?php
                            } else {
                                echo "<span>Uncategorized</span>";
                            }
                            ?>
                            </span>

                                </div>
                                <!-- Edit Article Icon and Dropdown Menu -->
                                <?php
                                if (strpos($_SERVER['REQUEST_URI'], 'search.php') === false):
                                    ?>
                                    <div class="edit-article">
                                        <img src="public/images/article-layout-img/three-dots.svg" alt="Edit Menu"
                                             class="edit-menu-icon">
                                        <div class="edit-menu">
                                            <ul>
                                                <li>
                                                    <a href="user/edit-article.php?id=<?php echo $row['id']; ?>"
                                                       class="edit-article-option">Edit Article</a>
                                                </li>
                                                <li class="admin-action-item">
                                                    <form action="" method="POST" style="display: inline;">
                                                        <input type="hidden" name="article_id"
                                                               value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="toggle_private"
                                                                value="<?php echo $row['private'] == 1 ? 0 : 1; ?>"
                                                                class="edit-article-option">
                                                            <?php echo $row['private'] == 1 ? 'Make Public' : 'Make Private'; ?>
                                                        </button>
                                                    </form>
                                                </li>

                                                <li class="admin-action-item">
                                                    <a href="javascript:void(0);" class="admin-action-link"
                                                       id="deleteLink"
                                                       data-article-id="<?php echo $row['id']; ?>">Delete Article</a>
                                                </li>

                                            </ul>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo BASE_URL; ?>user/article.php?id=<?php echo $row['id']; ?>"
                               class="article-main-link">
                                <div class="blog-body">
                                    <div class="blog-details">
                                        <h2 id="blog-title"><?php echo htmlspecialchars($row['title']) ?></h2>
                                        <p id="blog-content"><?php echo strip_tags($row['summary']); ?>...</p>
                                    </div>
                                    <div class="image-container">
                                        <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                            ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                            : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                                    </div>
                                </div>
                            </a>

                            <div class="blog-details-2">
                                <p id="blog-date">
                                    <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                                </p>
                                <div class="likes-and-comments" data-article-id="<?php echo $row['id']; ?>">
                                    <div class="like">
                                        <?php
                                        $user_id = $_SESSION['user_id']; // Get logged-in user's ID
                                        $article_id = $row['id']; // Get current article ID

                                        // Check if the user has already liked the article
                                        $query = "SELECT * FROM article_likes WHERE article_id = ? AND user_id = ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $article_id, $user_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $article_liked = $result->num_rows > 0;

                                        // Get like count
                                        $like_count_query = "SELECT COUNT(*) AS like_count FROM article_likes WHERE article_id = ?";
                                        $stmt = $conn->prepare($like_count_query);
                                        $stmt->bind_param("i", $article_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $like_count = $result->fetch_assoc()['like_count'];
                                        ?>

                                        <!-- Like Button -->
                                        <button class="like-btn" data-article-id="<?php echo $article_id; ?>"
                                                data-liked="<?php echo $article_liked ? '1' : '0'; ?>" onclick="handleBookmarkClick(event)">
                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-regular.svg"
                                                 class="like-icon like-unfilled"
                                                 style="display: <?php echo $article_liked ? 'none' : 'block'; ?>"/>

                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-solid.svg"
                                                 class="like-icon like-filled"
                                                 style="display: <?php echo $article_liked ? 'block' : 'none'; ?>"/>
                                        </button>

                                        <!-- Like Count -->
                                        <p class="like-status"
                                           id="like-count-<?php echo $article_id; ?>"><?php echo $like_count; ?></p>
                                    </div>

                                    <script>
                                        document.addEventListener("DOMContentLoaded", function () {
                                            document.querySelectorAll(".like-btn").forEach(button => {
                                                button.addEventListener("click", function () {
                                                    let articleId = this.getAttribute("data-article-id");
                                                    let isLiked = this.getAttribute("data-liked") === "1";

                                                    let action = isLiked ? "remove" : "add";

                                                    fetch("features/likes/like.php", {
                                                        method: "POST",
                                                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                                                        body: `article_id=${articleId}&action=${action}`
                                                    })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if (data.success) {
                                                                // Toggle like status
                                                                this.setAttribute("data-liked", isLiked ? "0" : "1");

                                                                // Toggle icon display
                                                                this.querySelector(".like-unfilled").style.display = isLiked ? "block" : "none";
                                                                this.querySelector(".like-filled").style.display = isLiked ? "none" : "block";

                                                                // Update like count
                                                                document.getElementById(`like-count-${articleId}`).textContent = data.likes;
                                                            }
                                                        })
                                                        .catch(error => console.error("Error:", error));
                                                });
                                            });
                                        });
                                    </script>


                                    <div class="comment">
                                        <!--Comments Backend-->
                                        <?php
                                        // Get the number of comments for this article
                                        $article_id = $row['id']; // Get article ID
                                        $comment_query = "SELECT COUNT(*) AS comment_count FROM article_comments WHERE article_id = ?";
                                        $comment_stmt = $conn->prepare($comment_query);
                                        $comment_stmt->bind_param("i", $article_id);
                                        $comment_stmt->execute();
                                        $comment_stmt->bind_result($comment_count);
                                        $comment_stmt->fetch();
                                        $comment_stmt->close();
                                        ?>
                                        <a href="<?php echo BASE_URL; ?>user/article.php?id=<?php echo $row['id'] ?>"
                                           class="comments-link">
                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/comments-regular.svg"
                                                 alt="Comments">
                                            <p class="comments-count"><?php echo $comment_count; ?></p>
                                        </a>
                                    </div>


                                    <?php
                                    // Check if the article is already bookmarked
                                    $check_query = "SELECT * FROM user_bookmarks WHERE user_id = ? AND article_id = ?";
                                    $stmt = $conn->prepare($check_query);
                                    $stmt->bind_param("ii", $user_id, $article_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $article_bookmarked = $result->num_rows > 0;
                                    ?>

                                    <div class="bookmark">
                                        <!-- Bookmark button with form -->
                                        <form action="<?php echo BASE_URL; ?>features/bookmarks/bookmark.php"
                                              method="POST"
                                              class="bookmark-form">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <!-- Show filled icon if the article is bookmarked -->
                                            <button type="submit" class="bookmark-btn" name="bookmark_action"
                                                    value="<?php echo $article_bookmarked ? 'remove' : 'add'; ?>" onclick="handleBookmarkClick(event)">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus.svg"
                                                     alt="Add to Bookmarks" class="bookmark-icon"
                                                     style="display: <?php echo $article_bookmarked ? 'none' : 'block'; ?>"/>
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus-fill.svg"
                                                     alt="Remove from Bookmarks" class="bookmark-icon"
                                                     style="display: <?php echo $article_bookmarked ? 'block' : 'none'; ?>"/>
                                            </button>
                                        </form>
                                        <p class="bookmark-status"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="divider"></div>

                    <?php else: ?>
                        <!-- User is NOT the author of the article -->
                        <div class="flex-item">
                            <div class="article-author-and-topic">

                                <?php
                                // Example of how you might be fetching blog posts
                                $sql = "
                            SELECT tbl_blogs.id, tbl_blogs.title, tbl_blogs.user_id, tbl_blogs.datePublished, 
                                   tbl_blogs.Tags, tbl_blogs.Image, Users.username 
                            FROM tbl_blogs 
                            LEFT JOIN Users ON tbl_blogs.user_id = Users.user_id
                            ORDER BY tbl_blogs.datePublished DESC"; ?>
                                <a href="<?php echo BASE_URL; ?>feed.php?username=<?php echo urlencode($row['username']); ?>"
                                   class="aa" id="author-name">
                                    <?php echo htmlspecialchars($row['username']); ?>
                                </a>
                                <span class="aa" id="writing-about">is writing about</span>
                                <span>
                               <?php
                               if (!empty($row['Tags'])) {
                                   // Explode tags by comma and trim whitespace
                                   $tags = explode(",", $row['Tags']);
                                   $first_tag = trim($tags[0]); // Get the first tag
                                   ?>
                                   <!-- Tag link to feed.php with tag query -->
                                   <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($first_tag); ?>"
                                      class="tag-link" style="color: firebrick">
                                    <?php echo htmlspecialchars($first_tag); ?>
                                </a>
                                   <?php
                               } else {
                                   echo "<span>Uncategorized</span>";
                               }
                               ?>

                        </span>
                            </div>

                            <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>"
                               class="article-main-link">
                                <div class="blog-body">
                                    <div class="blog-details">
                                        <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                                        <p id="blog-content"><?php echo strip_tags($row['summary']); ?>...</p>
                                    </div>
                                    <div class="image-container">
                                        <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                            ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                            : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                                    </div>
                                </div>
                            </a>

                            <div class="blog-details-2">
                                <p id="blog-date">
                                    <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                                </p>
                                <div class="likes-and-comments" data-article-id="<?php echo $row['id']; ?>">
                                    <div class="like">
                                        <?php
                                        $user_id = $_SESSION['user_id']; // Get logged-in user's ID
                                        $article_id = $row['id']; // Get current article ID

                                        // Check if the user has already liked the article
                                        $query = "SELECT * FROM article_likes WHERE article_id = ? AND user_id = ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $article_id, $user_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $article_liked = $result->num_rows > 0;

                                        // Get like count
                                        $like_count_query = "SELECT COUNT(*) AS like_count FROM article_likes WHERE article_id = ?";
                                        $stmt = $conn->prepare($like_count_query);
                                        $stmt->bind_param("i", $article_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $like_count = $result->fetch_assoc()['like_count'];
                                        ?>

                                        <!-- Like Button -->
                                        <button class="like-btn" data-article-id="<?php echo $article_id; ?>"
                                                data-liked="<?php echo $article_liked ? '1' : '0'; ?>" onclick="handleBookmarkClick(event)">
                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-regular.svg"
                                                 class="like-icon like-unfilled"
                                                 style="display: <?php echo $article_liked ? 'none' : 'block'; ?>"/>

                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-solid.svg"
                                                 class="like-icon like-filled"
                                                 style="display: <?php echo $article_liked ? 'block' : 'none'; ?>"/>
                                        </button>

                                        <!-- Like Count -->
                                        <p class="like-status"
                                           id="like-count-<?php echo $article_id; ?>"><?php echo $like_count; ?></p>
                                    </div>

                                    <script>
                                        // Function to handle bookmark button click
                                        function handleBookmarkClick(event) {
                                            <?php if (!isset($_SESSION['user_id'])): ?>
                                            // Redirect to login page if user is not logged in
                                            window.location.href = "<?php echo BASE_URL; ?>user_auth.php";
                                            event.preventDefault(); // Prevent the default action (like bookmarking) from happening
                                            <?php endif; ?>
                                        }

                                        document.addEventListener("DOMContentLoaded", function () {
                                            document.querySelectorAll(".like-btn").forEach(button => {
                                                button.addEventListener("click", function () {
                                                    let articleId = this.getAttribute("data-article-id");
                                                    let isLiked = this.getAttribute("data-liked") === "1";

                                                    let action = isLiked ? "remove" : "add";

                                                    fetch("features/likes/like.php", {
                                                        method: "POST",
                                                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                                                        body: `article_id=${articleId}&action=${action}`
                                                    })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if (data.success) {
                                                                // Toggle like status
                                                                this.setAttribute("data-liked", isLiked ? "0" : "1");

                                                                // Toggle icon display
                                                                this.querySelector(".like-unfilled").style.display = isLiked ? "block" : "none";
                                                                this.querySelector(".like-filled").style.display = isLiked ? "none" : "block";

                                                                // Update like count
                                                                document.getElementById(`like-count-${articleId}`).textContent = data.likes;
                                                            }
                                                        })
                                                        .catch(error => console.error("Error:", error));
                                                });
                                            });
                                        });
                                    </script>


                                    <div class="comment">
                                        <!--Comments Backend-->
                                        <?php
                                        // Get the number of comments for this article
                                        $article_id = $row['id']; // Get article ID
                                        $comment_query = "SELECT COUNT(*) AS comment_count FROM article_comments WHERE article_id = ?";
                                        $comment_stmt = $conn->prepare($comment_query);
                                        $comment_stmt->bind_param("i", $article_id);
                                        $comment_stmt->execute();
                                        $comment_stmt->bind_result($comment_count);
                                        $comment_stmt->fetch();
                                        $comment_stmt->close();
                                        ?>
                                        <a href="<?php echo BASE_URL; ?>user/article.php?id=<?php echo $row['id'] ?>"
                                           class="comments-link">
                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/comments-regular.svg"
                                                 alt="Comments">
                                            <p class="comments-count"><?php echo $comment_count; ?></p>
                                        </a>
                                    </div>


                                    <div class="bookmark">
                                        <?php
                                        $user_id = $_SESSION['user_id']; // Get logged-in user's ID
                                        $article_id = $row['id']; // Get current article ID

                                        // Check if the user has already bookmarked the article
                                        $query = "SELECT * FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $article_id, $user_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $article_bookmarked = $result->num_rows > 0;
                                        ?>
                                        <div class="bookmark-form">
                                            <!-- Bookmark Button -->
                                            <button class="bookmark-btn" data-article-id="<?php echo $article_id; ?> "
                                                    data-bookmarked="<?php echo $article_bookmarked ? '1' : '0'; ?>" onclick="handleBookmarkClick(event)">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus.svg"
                                                     class="bookmark-icon bookmark-unfilled"
                                                     style="display: <?php echo $article_bookmarked ? 'none' : 'block'; ?>"/>

                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus-fill.svg"
                                                     class="bookmark-icon bookmark-filled"
                                                     style="display: <?php echo $article_bookmarked ? 'block' : 'none'; ?>"/>
                                            </button>
                                        </div>
                                    </div>

                                    <script>
                                        document.addEventListener("DOMContentLoaded", function () {
                                            document.querySelectorAll(".bookmark-btn").forEach(button => {
                                                button.addEventListener("click", function () {
                                                    let articleId = this.getAttribute("data-article-id");
                                                    let isBookmarked = this.getAttribute("data-bookmarked") === "1";

                                                    let action = isBookmarked ? "remove" : "add";

                                                    fetch("features/bookmarks/bookmark.php", {
                                                        method: "POST",
                                                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                                                        body: `article_id=${articleId}&action=${action}`
                                                    })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if (data.success) {
                                                                // Toggle bookmark status
                                                                this.setAttribute("data-bookmarked", isBookmarked ? "0" : "1");

                                                                // Toggle icon display
                                                                this.querySelector(".bookmark-unfilled").style.display = isBookmarked ? "block" : "none";
                                                                this.querySelector(".bookmark-filled").style.display = isBookmarked ? "none" : "block";
                                                            }
                                                        })
                                                        .catch(error => console.error("Error:", error));
                                                });
                                            });
                                        });
                                    </script>


                                </div>
                            </div>
                        </div>
                        <div class="divider"></div>
                    <?php endif; ?>

                <?php endwhile; ?>

            <?php else: ?>
                <p>No articles found.</p>
            <?php endif; ?>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($total_pages > 1): ?>
                    <!-- Previous Button -->
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&txt-search=<?php echo urlencode($search); ?>">Previous</a>
                    <?php endif; ?>

                    <!-- First Page -->
                    <a href="?page=1&txt-search=<?php echo urlencode($search); ?>"
                       class="<?php echo ($current_page == 1) ? 'active' : ''; ?>">1</a>

                    <!-- Ellipsis if needed -->
                    <?php if ($current_page > 4): ?>
                        <span>...</span>
                    <?php endif; ?>

                    <!-- Page Numbers Around Current Page -->
                    <?php
                    $start_page = max(2, $current_page - 2);
                    $end_page = min($total_pages - 1, $current_page + 2);

                    for ($page = $start_page; $page <= $end_page; $page++): ?>
                        <a href="?page=<?php echo $page; ?>&txt-search=<?php echo urlencode($search); ?>"
                           class="<?php echo ($current_page == $page) ? 'active' : ''; ?>">
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Ellipsis Before Last Page -->
                    <?php if ($current_page < $total_pages - 3): ?>
                        <span>...</span>
                    <?php endif; ?>

                    <!-- Last Page -->
                    <?php if ($total_pages > 1): ?>
                        <a href="?page=<?php echo $total_pages; ?>&txt-search=<?php echo urlencode($search); ?>"
                           class="<?php echo ($current_page == $total_pages) ? 'active' : ''; ?>">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>

                    <!-- Next Button -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&txt-search=<?php echo urlencode($search); ?>">Next</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>


    </section>
</main>

<?php $conn->close(); ?>

<script src="<?php echo BASE_URL . 'public/js/save-page-position.js' ?>"></script>
<!--<script src="--><?php //echo BASE_URL ?><!--account/js/articleFilter.js"></script>-->
<script>


</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        setupEventListeners();
        restoreFilterSelections(); // Restore selections on page load
    });

    function setupEventListeners() {
        const modal = document.getElementById("filterModal");
        const openFilterBtn = document.getElementById("openFilterModal");
        const applyFiltersBtn = document.getElementById("applyFilters");

        // Ensure the modal is hidden initially
        modal.style.display = "none";

        // Open modal and restore previous selections
        openFilterBtn.addEventListener("click", function () {
            restoreFilterSelections();
            modal.style.display = "flex";
        });

        // Apply filters
        applyFiltersBtn.addEventListener("click", function () {
            let queryParams = new URLSearchParams(window.location.search);

            // Preserve the existing search term
            let searchInput = document.getElementById("text-search-bar").value;
            if (searchInput) queryParams.set("txt-search", searchInput);

            // Checkbox filters
            queryParams.set("author", document.getElementById("filter-author").checked ? "1" : "0");
            queryParams.set("title", document.getElementById("filter-title").checked ? "1" : "0");
            queryParams.set("content", document.getElementById("filter-content").checked ? "1" : "0");

            // Date filters
            let startDate = document.getElementById("start-date").value;
            let endDate = document.getElementById("end-date").value;
            if (startDate) queryParams.set("startDate", startDate);
            if (endDate) queryParams.set("endDate", endDate);

            // Category filters
            let selectedCategories = [];
            document.querySelectorAll(".category-btn.active").forEach(btn => {
                selectedCategories.push(btn.getAttribute("data-category"));
            });
            if (selectedCategories.length > 0) {
                queryParams.set("categories", selectedCategories.join(","));
            } else {
                queryParams.delete("categories");
            }

            // Save filter selections to localStorage
            localStorage.setItem("savedFilters", queryParams.toString());

            // Apply filters without refreshing
            window.history.pushState({}, "", "search.php?" + queryParams.toString());

            // Reload results dynamically
            fetchResults();

            // Hide the modal after applying filters
            modal.style.display = "none";
        });

        // Handle category button toggling
        document.querySelectorAll(".category-btn").forEach(button => {
            button.addEventListener("click", function () {
                this.classList.toggle("active");
            });
        });

        // Close modal when clicking outside of it
        window.addEventListener("click", function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    }

    // Fetch results without refreshing the page
    function fetchResults() {
        fetch("search.php?" + new URLSearchParams(window.location.search))
            .then(response => response.text())
            .then(html => {
                let parser = new DOMParser();
                let newDoc = parser.parseFromString(html, "text/html");
                document.querySelector(".search-page-main-content").innerHTML =
                    newDoc.querySelector(".search-page-main-content").innerHTML;

                // **Reattach event listeners after dynamic update**
                setupEventListeners();
            });
    }

    // Restore previously selected filter values
    function restoreFilterSelections() {
        let queryParams = new URLSearchParams(window.location.search);

        // Restore checkboxes
        document.getElementById("filter-author").checked = queryParams.get("author") === "1";
        document.getElementById("filter-title").checked = queryParams.get("title") === "1";
        document.getElementById("filter-content").checked = queryParams.get("content") === "1";

        // Restore dates
        if (queryParams.get("startDate")) {
            document.getElementById("start-date").value = queryParams.get("startDate");
        }
        if (queryParams.get("endDate")) {
            document.getElementById("end-date").value = queryParams.get("endDate");
        }

        // Restore categories
        let selectedCategories = queryParams.get("categories") ? queryParams.get("categories").split(",") : [];
        document.querySelectorAll(".category-btn").forEach(button => {
            let category = button.getAttribute("data-category");
            if (selectedCategories.includes(category)) {
                button.classList.add("active");
            } else {
                button.classList.remove("active");
            }
        });
    }


</script>

</body>
</html>
