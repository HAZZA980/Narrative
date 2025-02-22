<?php
// Start the session and connect to the database
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Get the tag from the URL (if set)
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : null;
// Ensure that the tag is provided in the URL, else exit
if (!$tag) {
    die("Tag not provided.");
}

// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Add wildcards to search for the tag within the comma-separated list
$tagParam = '%' . $tag . '%';

// Debugging: Output the tagParam to ensure it's correct
//echo "Tag Parameter: " . htmlspecialchars($tagParam) . "<br>";



///----------------------------------------------------------------------------Sorting out the ordering of the articles
// Prepare the query
$query = "SELECT b.id, b.user_id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, 
       b.Category, b.Tags, b.Image, u.username AS Author 
FROM tbl_blogs b
JOIN users u ON b.user_id = u.user_id
WHERE b.Private = 0 AND b.Tags LIKE ? 
ORDER BY 
    (CASE 
        WHEN b.Tags = ? THEN 1  -- Exact match first
        WHEN b.Tags LIKE CONCAT(?, ',%') THEN 2  -- Tag at the beginning
        WHEN b.Tags LIKE CONCAT('%', ?, '%') THEN 3  -- Tag appears anywhere
        ELSE 4  
    END), 
    b.datePublished DESC  -- Sort by date
LIMIT ? OFFSET ?;";

// Prepare the statement
$stmt = $conn->prepare($query);

// Debugging: Check if the query preparation is successful
if (!$stmt) {
    die("Query preparation error: " . $conn->error);
}

$tagParam = '%' . $tag . '%';  // For WHERE condition
$exactTag = $tag;  // For exact match
$firstTagParam = $tag . ',';  // For ordering first tag
$anyTagParam = $tag;  // For ordering if tag is anywhere

// Correct number of bind_param values
$stmt->bind_param("ssssii", $tagParam, $exactTag, $firstTagParam, $anyTagParam, $articles_per_page, $offset);

// Execute the query
if (!$stmt->execute()) {
    die("Execution error: " . $stmt->error);
}

// Fetch result
$blogs_result = $stmt->get_result();

// Close the statement
$stmt->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles Home | Narrative</title>
    <link rel="stylesheet" href="public/css/styles-forYou.css">
    <link rel="stylesheet" href="features/pagination/css/pagination.css">
    <link rel="stylesheet" href="explore/articleLayouts/styles-default-article-formation.css">
    <style>
        .tag-link {
            color: firebrick;
            text-decoration: none;
        }

        .tag-link:hover {
            text-decoration: underline;
            color: firebrick;
        }
    </style>
</head>
<body>

<main class="main-container">
    <div class="main-content">
        <div class="flex-container">
            <h1 class="main-content-title">
                #<?php echo empty($preferred_categories) ? $tag : "Recommended for You"; ?>
            </h1>

            <?php
            // Fetch and display blogs
            if ($blogs_result->num_rows > 0):
                $i = 0;
                while ($row = $blogs_result->fetch_assoc()): ?>
                    <div class="flex-item">
                        <div class="article-author-and-topic">
                            <a href="<?php echo BASE_URL; ?>feed.php?username=<?php echo urlencode($row['Author']); ?>"
                               class="aa" id="author-name">
                                <?php echo htmlspecialchars($row['Author']); ?>
                            </a>
                            <span class="aa" id="writing-about">is writing about</span>
                            <span class="aa" id="blog-tags">
                            <?php
                            if (!empty($row['Tags'])) {
                                $tags = array_map('trim', explode(",", $row['Tags'])); // Split tags and trim whitespace
                                ?>
                            <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($tags[0]); ?>" class="tag-link">
                                <?php echo htmlspecialchars($tags[0]); ?>
                                </a><?php
                            } else {
                                echo "Uncategorized"; // Fallback if there are no tags
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
                                    <form action="features/likes/like.php" method="POST" class="like-form">
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
                                    <a href="<?php echo BASE_URL; ?>user/article.php?id=<?php echo $row['id'] ?>"
                                       class="comments-link">
                                        <img src="<?php echo BASE_URL ?>public/images/article-layout-img/comments-regular.svg"
                                             alt="Comments">
                                        <p class="comments-count"><?php echo $comment_count; ?></p>
                                        <!-- Display comment count -->
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
                                    <form action="features/bookmarks/bookmark.php" method="POST"
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
                <?php endwhile; ?>


            <?php else: ?>
                <p>No Articles Found. Publish your First Article <a
                            href="<?php echo BASE_URL ?>includes/createArticle.php">Here</a>.</p>
            <?php endif; ?>


            <?php
            // Count the total number of articles for pagination based on the tag from the URL
            $total_query = "SELECT COUNT(*) as total FROM tbl_blogs WHERE Private = 0 AND Tags LIKE ?";

            // Prepare the statement
            $total_stmt = $conn->prepare($total_query);

            if (!$total_stmt) {
                die("Error preparing pagination query: " . $conn->error);
            }

            // Add wildcards to search for the tag within the comma-separated list
            $tagParam = '%' . $tag . '%';

            // Bind the parameter
            $total_stmt->bind_param("s", $tagParam);

            // Execute the query
            $total_stmt->execute();
            $total_result = $total_stmt->get_result();
            $total_row = $total_result->fetch_assoc();
            $total_articles = $total_row['total'];
            $total_pages = ceil($total_articles / $articles_per_page);

            $total_stmt->close();

            // Generate pagination links
            if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <a href="?tag=<?php echo urlencode($tag); ?>&page=<?php echo $page; ?>"
                           class="pagination-link <?php echo $current_page == $page ? 'current' : ''; ?>"
                           aria-label="Page <?php echo $page; ?>">
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>

        </div>


        <aside class="aside-links">

            <aside class="non-recommended-articles">
                <h2 class="aside-title">Other Articles</h2>
                <?php if ($non_recommended_result->num_rows > 0): ?>
                    <ul>
                        <?php while ($row = $non_recommended_result->fetch_assoc()): ?>
                            <li>
                                <a href="user/article.php?id=<?php echo $row['id']; ?>">
                                    <div class="article-summary">
                                        <p class="author-name"><?php echo htmlspecialchars($row['Author']); ?></p>
                                        <h3 class="article-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                        <p class="article-date">
                                            <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                                        </p>
                                    </div>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No other articles available at the moment.</p>
                <?php endif; ?>

                <div>
                    <h3 class="aside-title">Latest From Your Followed Authors</h3>
                </div>

                <div class="aside-recommended-topics">
                    <h2 class="aside-title">Topics You May Like</h2>
                    <?php if ($non_recommended_topics_result->num_rows > 0): ?>
                        <ul>
                            <?php while ($topic_row = $non_recommended_topics_result->fetch_assoc()): ?>
                                <li>
                                    <?php
                                    $tagData = $topic_row['Tags'];

                                    // If it's an array, get the first element; otherwise, treat it as a string
                                    $firstTag = is_array($tagData) ? reset($tagData) : explode(',', $tagData)[0];

                                    $firstTag = trim($firstTag); // Remove any spaces around the tag
                                    $file_name = $category_map[$firstTag] ?? null; // Check if the tag exists in the map

                                    if ($file_name): ?>
                                        <a href="<?php echo BASE_URL; ?>layouts/pages/articles/categories/<?php echo htmlspecialchars($file_name); ?>"
                                           class="tag-link">
                                            <?php echo htmlspecialchars($firstTag); ?>
                                        </a>
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($firstTag); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>No topics available at the moment.</p>
                    <?php endif; ?>
                </div>
            </aside>
        </aside>
    </div>
</main>
<script src="<?php echo BASE_URL . 'public/js/save-page-position.js' ?>"></script>
</body>
</html>
