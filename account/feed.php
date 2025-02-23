<?php
ob_start();
include $_SERVER['DOCUMENT_ROOT']. '/phpProjects/narrative/config/config.php';
include BASE_PATH . "user/model/delete.article.php";
include BASE_PATH . "user/view/delete-article-modal.html";
include BASE_PATH . "account/model/feed.php";
include BASE_PATH . "account/account-masthead.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--    <link rel="stylesheet" href="../public/css/styles-forYou-homepage.css">-->
    <link rel="stylesheet" href="../features/pagination/css/pagination.css">
    <link rel="stylesheet" href="../explore/articleLayouts/styles-default-article-formation.css">
    <link rel="stylesheet" href="<?php echo BASE_URL?>user/css/delete-article-modal.css">
    <link rel="stylesheet" href="<?php echo BASE_URL?>account/css/feed.css">
    <title>Your Articles | Narrative</title>
    <!--    <script src="--><?php //echo BASE_PATH; ?><!--public/js/save-page-position.js"></script>-->
</head>
<body>
<?php include "../layouts/mastheads/articles/account-masthead.php"; ?>


<div class="feed-outer-container">
    <div class="top-container">
        <p class="top-container-paragraph">Welcome to your personal feed! This is your central hub for managing all your
            published and unpublished
            articles. Organise, edit, and view your works based on different criteria with ease.

            <br><br> Use the options menu (three dots above each article) to make quick updates, toggle privacy
            settings, or delete articles. Whether you're working on drafts or showcasing your published masterpieces,
            this feed is tailored to help you stay in control of your content.

            <br><br> <strong>Note:</strong> Deleting an article will permanently remove it from the server. This action
            is irreversible, so proceed with caution. Start creating, sharing, and curating your stories today!</p>
    </div>

    <div class="feed-tabs" id="feed-tabs">
        <ul class="feed-tabs-ul">
            <li class="feed-tabs-li"><a href="?tab=public_feed" class="<?= $tab == 'public_feed' ? 'active' : '' ?>">Public
                    Feed</a></li>
            <li class="feed-tabs-li"><a href="?tab=drafts" class="<?= $tab == 'drafts' ? 'active' : '' ?>">Drafts</a>
            </li>
            <li class="feed-tabs-li"><a href="?tab=commented_articles"
                                        class="<?= $tab == 'commented_articles' ? 'active' : '' ?>">Commented
                    Articles</a></li>
            <li class="feed-tabs-li"><a href="?tab=saved_articles"
                                        class="<?= $tab == 'saved_articles' ? 'active' : '' ?>">Saved
                    Articles</a></li>
        </ul>
    </div>

    <div class="pagination-container">
        <!-- Filter and Order Buttons -->
        <div class="filter-order-buttons">
            <!-- Order Button -->
            <div class="order-dropdown">
                <a href="#"><img src="<?php echo BASE_URL; ?>public/images/pagination/order.svg" alt="Order"
                                 title="Order"></a>
                <div class="dropdown-content">
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=date">Order By Date</a>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=chronological">Order By ID</a>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=alphabetical">Order By Alphabetical</a>
                </div>
            </div>

            <!-- Filter Button -->
            <a href="#" style="display: none"><img src="<?php echo BASE_URL; ?>public/images/pagination/filter.svg" alt="Filter"
                             title="Filter"></a>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <!-- Previous Button -->
                <?php if ($current_page > 1): ?>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $current_page - 1; ?>"
                       class="pagination-link prev-button"
                       aria-label="Previous Page">
                        Previous
                    </a>
                <?php endif; ?>

                <!-- First Page -->
                <?php if ($current_page > 5): ?>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=1"
                       class="pagination-link"
                       aria-label="Page 1">
                        1
                    </a>
                    <span class="dots">...</span>
                <?php endif; ?>

                <!-- Middle Pages -->
                <?php
                $start_page = max(1, $current_page - 4);
                $end_page = min($total_pages, $current_page + 4);
                for ($page = $start_page; $page <= $end_page; $page++): ?>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $page; ?>"
                       class="pagination-link <?php echo $current_page == $page ? 'current' : ''; ?>"
                       aria-label="Page <?php echo $page; ?>">
                        <?php echo $page; ?>
                    </a>
                <?php endfor; ?>

                <!-- Last Page -->
                <?php if ($current_page < $total_pages - 4): ?>
                    <span class="dots">...</span>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $total_pages; ?>"
                       class="pagination-link"
                       aria-label="Page <?php echo $total_pages; ?>">
                        <?php echo $total_pages; ?>
                    </a>
                <?php endif; ?>

                <!-- Next Button -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $current_page + 1; ?>"
                       class="pagination-link next-button"
                       aria-label="Next Page">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="asc-desc">
            <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=<?php echo htmlspecialchars($order_by); ?>&order_dir=asc">
                <img src="<?php echo BASE_URL; ?>public/images/pagination/arrow-up.svg" alt="Ascending"
                     title="Ascending">
            </a>
            <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=<?php echo htmlspecialchars($order_by); ?>&order_dir=desc">
                <img src="<?php echo BASE_URL; ?>public/images/pagination/arrow-down.svg" alt="Descending"
                     title="Descending">
            </a>
        </div>
    </div>

    <div class="personal-feed-container">
        <div class="flex-container" id="flex-container">
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
                                    <?php
                                    if (!empty($row['Tags'])) {
                                        // Explode tags by comma and trim whitespace
                                        $tags = explode(",", $row['Tags']);
                                        $first_tag = trim($tags[0]); // Get the first tag
                                        ?>
                                        <!-- Tag link to feed.php with tag query -->
                                        <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($first_tag); ?>" class="tag-link">
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
                                <div class="edit-article">
                                    <img src="../public/images/article-layout-img/three-dots.svg" alt="Edit Menu"
                                         class="edit-menu-icon">
                                    <div class="edit-menu">
                                        <ul>
                                            <li>
                                                <a href="<?php echo BASE_URL?>user/edit-article.php?id=<?php echo $row['id']; ?>"
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
                                                <a href="javascript:void(0);" class="admin-action-link delete-link"
                                                   data-article-id="<?php echo $row['id']; ?>">Delete Article</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                            <a href="<?php echo BASE_URL; ?>user/article.php?id=<?php echo $row['id']; ?>"
                               class="article-main-link">
                                <div class="blog-body">
                                    <div class="blog-details">
                                        <h2 id="blog-title"><?php echo htmlspecialchars($row['title']);?></h2>
                                        <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
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
                                <div class="likes-and-comments" data-article-id="<?php echo $row['id']; ?>">
                                    <div class="like">
                                        <?php
                                        // Get the current user's ID
                                        $user_id = $_SESSION['user_id']; // Or however you are retrieving the user_id from the session

                                        // Assuming you're inside the loop for displaying each article
                                        $article_id = $row['id']; // Article ID for the current post

                                        // Check if the user has already liked the article
                                        $query = "SELECT * FROM article_likes WHERE article_id = ? AND user_id = ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $article_id, $user_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        // Check if there is a like record for this article and user
                                        $article_liked = $result->num_rows > 0 ? true : false;
                                        ?>
                                        <!-- Like button with form -->
                                        <form action="<?php echo BASE_URL; ?>features/likes/like.php"
                                              method="POST" class="like-form">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <!-- Show filled icon if the article is liked -->
                                            <button type="submit" class="like-btn" name="bookmark_action"
                                                    value="<?php echo $article_liked ? 'remove' : 'add'; ?>">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-regular.svg"
                                                     alt="Add Like" class="like-icon"
                                                     style="display: <?php echo $article_liked ? 'none' : 'block'; ?>"/>
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-solid.svg"
                                                     alt="Remove Like" class="like-icon"
                                                     style="display: <?php echo $article_liked ? 'block' : 'none'; ?>"/>
                                            </button>
                                        </form>
                                        <?php
                                        // Query to get the number of likes for the current article
                                        $like_count_query = "SELECT COUNT(*) AS like_count FROM article_likes WHERE article_id = ?";
                                        $stmt = $conn->prepare($like_count_query);
                                        $stmt->bind_param("i", $article_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $like_count = $result->fetch_assoc()['like_count']; // Fetch the count of likes
                                        ?>
                                        <p class="like-status"><?php echo $like_count; ?></p>
                                    </div>


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
                                        <a href="<?php echo BASE_URL;?>user/article.php?id=<?php echo $row['id']?>" class="comments-link">
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
                                              method="POST" class="bookmark-form">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <!-- Show filled icon if the article is bookmarked -->
                                            <button type="submit" class="bookmark-btn" name="bookmark_action"
                                                    value="<?php echo $article_bookmarked ? 'remove' : 'add'; ?>">
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
                                <span class="aa" id="author-name"><?php echo htmlspecialchars($row['Author']) ?></span>
                                <span class="aa" id="writing-about">is writing about</span>
                                <?php
                                if (!empty($row['Tags'])) {
                                    // Explode tags by comma and trim whitespace
                                    $tags = explode(",", $row['Tags']);
                                    $first_tag = trim($tags[0]); // Get the first tag
                                    ?>
                                    <!-- Tag link to feed.php with tag query -->
                                    <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($first_tag); ?>" class="tag-link">
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
                                        <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
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
                                        // Get the current user's ID
                                        $user_id = $_SESSION['user_id']; // Or however you are retrieving the user_id from the session

                                        // Assuming you're inside the loop for displaying each article
                                        $article_id = $row['id']; // Article ID for the current post

                                        // Check if the user has already liked the article
                                        $query = "SELECT * FROM article_likes WHERE article_id = ? AND user_id = ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $article_id, $user_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        // Check if there is a like record for this article and user
                                        $article_liked = $result->num_rows > 0 ? true : false;
                                        ?>
                                        <!-- Like button with form -->
                                        <form action="<?php echo BASE_URL ?>/features/likes/like.php"
                                              method="POST" class="like-form">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <!-- Show filled icon if the article is liked -->
                                            <button type="submit" class="like-btn" name="bookmark_action"
                                                    value="<?php echo $article_liked ? 'remove' : 'add'; ?>">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-regular.svg"
                                                     alt="Add Like" class="like-icon"
                                                     style="display: <?php echo $article_liked ? 'none' : 'block'; ?>"/>
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-solid.svg"
                                                     alt="Remove Like" class="like-icon"
                                                     style="display: <?php echo $article_liked ? 'block' : 'none'; ?>"/>
                                            </button>
                                        </form>
                                        <?php
                                        // Query to get the number of likes for the current article
                                        $like_count_query = "SELECT COUNT(*) AS like_count FROM article_likes WHERE article_id = ?";
                                        $stmt = $conn->prepare($like_count_query);
                                        $stmt->bind_param("i", $article_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $like_count = $result->fetch_assoc()['like_count']; // Fetch the count of likes
                                        ?>
                                        <p class="like-status"><?php echo $like_count; ?></p>
                                    </div>


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
                                        <a href="<?php echo BASE_URL;?>user/article.php?id=<?php echo $row['id']?>" class="comments-link">
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
                                        <form action="<?php echo BASE_URL ?>/features/bookmarks/bookmark.php"
                                              method="POST" class="bookmark-form">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <!-- Show filled icon if the article is bookmarked -->
                                            <button type="submit" class="bookmark-btn" name="bookmark_action"
                                                    value="<?php echo $article_bookmarked ? 'remove' : 'add'; ?>">
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
                    <?php endif; ?>

                <?php endwhile; ?>
                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($total_pages > 1): ?>
                        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                            <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $page; ?>"
                               class="pagination-link <?php echo $current_page == $page ? 'current' : ''; ?>"
                               aria-label="Page <?php echo $page; ?>">
                                <?php echo $page; ?>
                            </a>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No articles found.</p>
            <?php endif; ?>


        </div>
    </div>
</div>
<script>
    var BASE_URL = "<?php echo BASE_URL; ?>";
</script>
<script src="<?php echo BASE_URL?>user/js/delete-article.js"></script>
<script src="<?php echo BASE_URL?>public/js/save-page-position.js"></script>
<script src="<?php echo BASE_URL?>account/js/editArticleDropdown.js"></script>
<script src="<?php echo BASE_URL?>account/js/articleFilter.js"></script>
</body>
</html>
