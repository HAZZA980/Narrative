<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
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
    <link rel="stylesheet" href="explore/articleLayouts/styles-default-article-formation.css">
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
                            <span class="aa" id="blog-tags"><?php echo htmlspecialchars($row['Tags']); ?></span>
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
                                        <a href="javascript:void(0);" class="admin-action-link" id="deleteLink"
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
                                <form action="<?php echo BASE_URL; ?>features/likes/like.php" method="POST"
                                      class="like-form">
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
                                <form action="<?php echo BASE_URL; ?>features/bookmarks/bookmark.php" method="POST"
                                      class="bookmark-form">
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
                        <span class="aa" id="author-name"><?php echo htmlspecialchars($row['username']) ?></span>
                        <span class="aa" id="writing-about">is writing about</span>
                        <span class="aa" id="blog-tags"><?php echo htmlspecialchars($row['Tags']); ?></span>
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
                                    <p class="comments-count"><?php echo $comment_count;?></p>
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

            <?php else: ?>
                <p>No articles found.</p>
            <?php endif; ?>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($total_pages > 1): ?>
                    <ul class="pagination-list">
                        <?php if ($current_page > 1): ?>
                            <li>
                                <a href="?page=<?php echo $current_page - 1; ?>&txt-search=<?php echo urlencode($search); ?>"
                                   class="pagination-link">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                            <li>
                                <a href="?page=<?php echo $page; ?>&txt-search=<?php echo urlencode($search); ?>"
                                   class="pagination-link <?php echo $current_page == $page ? 'current' : ''; ?>">
                                    <?php echo $page; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li>
                                <a href="?page=<?php echo $current_page + 1; ?>&txt-search=<?php echo urlencode($search); ?>"
                                   class="pagination-link">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
    </section>
</main>

<?php $conn->close(); ?>

<script src="<?php echo BASE_URL . 'public/js/save-page-position.js' ?>"></script>

</body>
</html>
