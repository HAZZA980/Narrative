<?php
session_start();
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    die("User not logged in. Redirecting...");
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User ID not found in session.");
}

// Fetch user's preferred tags
$stmt = $conn->prepare("SELECT tag FROM user_preferences WHERE user_id = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$preferred_tags = [];
while ($row = $result->fetch_assoc()) {
    $preferred_tags[] = $row['tag'];
}
//----------------------------------------------------------------------------------------------------------------------
// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Fetch blogs based on preferences or fetch latest blogs with pagination
$blogs_result = null;
if (!empty($preferred_tags)) {
    // Query blogs based on preferred tags with pagination
    $placeholders = implode(",", array_fill(0, count($preferred_tags), "?"));
    $query = "SELECT b.id, b.user_id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, b.Tags, b.Image, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Tags IN ($placeholders) AND b.Private = 0
              ORDER BY b.datePublished DESC 
              LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing blogs query: " . $conn->error);
    }

    // Merge preferred tags and the pagination parameters
    $params = array_merge($preferred_tags, [$articles_per_page, $offset]);

    // Dynamically build the type string for bind_param()
    $type_str = str_repeat("s", count($preferred_tags)) . "ii";
    $stmt->bind_param($type_str, ...$params); // Bind the parameters

    $stmt->execute();
    $blogs_result = $stmt->get_result();

    if (!$blogs_result) {
        die("Error executing blogs query: " . $stmt->error);
    }
} else {
    // If no preferred tags, fetch latest blogs with pagination
    $query = "SELECT b.id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, b.Tags, b.Image, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Private = 0
              ORDER BY b.datePublished DESC 
              LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing blogs query: " . $conn->error);
    }

    $stmt->bind_param("ii", $articles_per_page, $offset); // Explicit binding for pagination parameters
    $stmt->execute();
    $blogs_result = $stmt->get_result();

    if (!$blogs_result) {
        die("Error fetching latest blogs: " . $conn->error);
    }
}

// Count the total number of articles for pagination
$total_query = "SELECT COUNT(*) as total FROM tbl_blogs WHERE Tags IN (" . implode(",", array_fill(0, count($preferred_tags), "?")) . ") AND Private = 0";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param(str_repeat("s", count($preferred_tags)), ...$preferred_tags);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $articles_per_page);

// Query articles NOT in the user's preferred tags for the ASIDE bar
$non_recommended_result = null;
if (!empty($preferred_tags)) {
    $placeholders = implode(",", array_fill(0, count($preferred_tags), "?"));
    $query = "SELECT b.id, b.title, b.datePublished, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Tags NOT IN ($placeholders) AND b.Private = 0
              ORDER BY b.datePublished DESC LIMIT 5";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing non-recommended query: " . $conn->error);
    }

    $stmt->bind_param(str_repeat("s", count($preferred_tags)), ...$preferred_tags);
    $stmt->execute();
    $non_recommended_result = $stmt->get_result();

    if (!$non_recommended_result) {
        die("Error fetching non-recommended blogs: " . $stmt->error);
    }
} else {
    $query = "SELECT b.id, b.title, b.datePublished, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Private = 0
              ORDER BY b.datePublished DESC LIMIT 5";
    $non_recommended_result = $conn->query($query);

    if (!$non_recommended_result) {
        die("Error fetching non-recommended blogs: " . $conn->error);
    }
}

//----------------------------------------------------------------------------------------------------------------------
// Query topics (tags) NOT in the user's preferred tags for the ASIDE Bar
$non_recommended_topics_result = null;
if (!empty($preferred_tags)) {
    $placeholders = implode(",", array_fill(0, count($preferred_tags), "?"));
    $query = "SELECT DISTINCT Tags FROM tbl_blogs WHERE Category NOT IN ($placeholders) AND Private = 0";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing non-recommended topics query: " . $conn->error);
    }

    $stmt->bind_param(str_repeat("s", count($preferred_tags)), ...$preferred_tags);
    $stmt->execute();
    $non_recommended_topics_result = $stmt->get_result();

    if (!$non_recommended_topics_result) {
        die("Error fetching non-recommended topics: " . $stmt->error);
    }
} else {
    $query = "SELECT DISTINCT Tags FROM tbl_blogs WHERE Private = 0";
    $non_recommended_topics_result = $conn->query($query);

    if (!$non_recommended_topics_result) {
        die("Error fetching non-recommended topics: " . $conn->error);
    }
}

// Mapping of tags to category file names
$category_map = [
    "Lifestyle" => "lifestyle.php",
    "Writing Craft" => "writing-craft.php",
    "Travel" => "travel.php",
    "Reviews" => "reviews.php",
    "History & Culture" => "history.php",
    "Entertainment" => "entertainment.php",
    "Business" => "business.php",
    "Technology" => "technology.php",
    "Politics" => "politics.php",
    "Science" => "science.php",
    "Health & Fitness" => "health.php",
    "Sports" => "sports.php",
    "Food & Drink" => "food-and-drink.php",
    ];
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Articles Home | Narrative</title>
        <link rel="stylesheet" href="public/css/styles-forYou-homepage.css">
        <link rel="stylesheet" href="public/css/pagination.css">
        <link rel="stylesheet" href="public/css/articleLayouts/styles-default-article-formation.css">
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
                    <?php echo empty($preferred_tags) ? "Latest Blogs" : "Recommended for You"; ?>
                </h1>

                <ul class="preferred_tags">
                    <?php if (!empty($preferred_tags)): ?>
                    <?php foreach ($preferred_tags as $tag): ?>
                    <li id="preferred_tag">
                        <?php
                        $file_name = $category_map[$tag] ?? null; // Check if the tag exists in the map
                        if ($file_name): ?>
                            <a href="<?php echo BASE_URL;?>explore/<?php echo htmlspecialchars($file_name);?>" class="tag-link">
                                <?php echo htmlspecialchars($tag); ?>
                            </a>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($tag); ?></span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                        <a class="changeTopics" href="settings/recommendations.php">+ Change Recommendations</a>
                    <?php else: ?>
                        <li>No preferred tags selected. <a href="settings/recommendations.php">Update Preferences</a></li>
                    <?php endif; ?>
                </ul>

                <?php if ($blogs_result->num_rows > 0): ?>
                    <?php $i = 0;
                    while ($row = $blogs_result->fetch_assoc()): ?>
                        <div class="flex-item">
                            <div class="article-author-and-topic">
                                <a href="<?php echo BASE_URL; ?>layouts/pages/articles/user-profile-feed.php?username=<?php echo urlencode($row['Author']); ?>" class="aa" id="author-name">
                                    <?php echo htmlspecialchars($row['Author']); ?>
                                </a>
                                <span class="aa" id="writing-about">is writing about</span>
                                <span class="aa" id="blog-tags">
                                <?php
                                // Get the tags for the current blog
                                $tags = explode(",", $row['Tags']);
                                foreach ($tags as $tag):
                                    $tag = trim($tag); // Remove whitespace
                                    $file_name = $category_map[$tag] ?? null; // Check if the tag exists in the map
                                    if ($file_name): ?>
                                        <a href="<?php echo BASE_URL;?>explore/<?php echo htmlspecialchars($file_name);?>" class="tag-link"><?php echo htmlspecialchars($tag); ?></a>
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($tag); ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </span>
                        </div>

                        <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>" class="article-main-link">
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
                                    <form action="layouts/pages/articles/like.php" method="POST" class="like-form">
                                        <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                        <!-- Show filled icon if the article is liked -->
                                        <button type="submit" class="like-btn" name="bookmark_action" value="<?php echo $article_liked ? 'remove' : 'add'; ?>">
                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-regular.svg" alt="Add Like" class="like-icon" style="display: <?php echo $article_liked ? 'none' : 'block'; ?>"/>
                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-solid.svg" alt="Remove Like" class="like-icon" style="display: <?php echo $article_liked ? 'block' : 'none'; ?>"/>
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
                                    <p class="comments-count"><?php echo $comment_count; ?></p> <!-- Display comment count -->
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
                                    <form action="layouts/pages/articles/bookmark.php" method="POST" class="bookmark-form">
                                        <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                        <!-- Show filled icon if the article is bookmarked -->
                                        <button type="submit" class="bookmark-btn" name="bookmark_action" value="<?php echo $article_bookmarked ? 'remove' : 'add'; ?>">
                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus.svg" alt="Add to Bookmarks" class="bookmark-icon" style="display: <?php echo $article_bookmarked ? 'none' : 'block'; ?>"/>
                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus-fill.svg" alt="Remove from Bookmarks" class="bookmark-icon" style="display: <?php echo $article_bookmarked ? 'block' : 'none'; ?>"/>
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
                            href="<?php echo BASE_URL?>includes/createArticle.php">Here</a>.</p>
            <?php endif; ?>


            <div class="pagination">
                <?php if ($total_pages > 1): ?>
                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <a href="?page=<?php echo $page; ?>"
                           class="pagination-link <?php echo $current_page == $page ? 'current' : ''; ?>"
                           aria-label="Page <?php echo $page; ?>">
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>


        <aside class="aside-links">
<!--            <aside class="aside-writing-link">-->
<!--                <a href="--><?php //echo BASE_URL?><!--includes/createArticle.php" class="write-link">-->
<!--                    <img src="--><?php //echo BASE_URL?><!--public/images/article-layout-img/pencil-square.svg" alt="Write Icon">-->
<!--                    <h3 class="aside-write">Write</h3>-->
<!--                </a>-->
<!--            </aside>-->


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
                                        <a href="<?php echo BASE_URL; ?>layouts/pages/articles/categories/<?php echo htmlspecialchars($file_name); ?>" class="tag-link">
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
<script src="<?php echo BASE_URL . 'public/js/save-page-position.js'?>"></script>
</body>
</html>
