<?php
// Start the session and connect to the database
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'features/write/write-icon-fixed.php';

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
    <title><?php echo htmlspecialchars($tag); ?> | Narrative</title>
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



        /* Profile image container */
        .profile-image-container {
            display: flex;
            align-items: center;
            margin-right: 10px;
        }

        /* Wrapper for the profile picture */
        .profile-pic-wrapper {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 10px;
            position: relative;
        }

        /* Profile picture */
        .pp-author-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Profile initial (if no image is available) */
        .profile-initial {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 2rem; /* Adjust font size for initials */
            text-align: center;
            background-color: #888; /* Default random color */
        }

        /* Author name */
        .profile-author-name {
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            color: #333;
            margin-left: 1rem;
        }

        /* Hover effect for author name */
        .profile-author-name:hover {
            color: #007bff;
            text-decoration: underline;
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
                                    <p id="blog-content"><?php echo $row['summary']; ?>...</p>
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
                                    <button class="like-btn" data-article-id="<?php echo $article_id; ?>" data-liked="<?php echo $article_liked ? '1' : '0'; ?>">
                                        <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-regular.svg"
                                             class="like-icon like-unfilled"
                                             style="display: <?php echo $article_liked ? 'none' : 'block'; ?>"/>

                                        <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-solid.svg"
                                             class="like-icon like-filled"
                                             style="display: <?php echo $article_liked ? 'block' : 'none'; ?>"/>
                                    </button>

                                    <!-- Like Count -->
                                    <p class="like-status" id="like-count-<?php echo $article_id; ?>"><?php echo $like_count; ?></p>
                                </div>

                                <script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        document.querySelectorAll(".like-btn").forEach(button => {
                                            button.addEventListener("click", function() {
                                                let articleId = this.getAttribute("data-article-id");
                                                let isLiked = this.getAttribute("data-liked") === "1";

                                                let action = isLiked ? "remove" : "add";

                                                fetch("features/likes/like.php", {
                                                    method: "POST",
                                                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
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
                                        <!-- Display comment count -->
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
                                        <button class="bookmark-btn" data-article-id="<?php echo $article_id; ?>" data-bookmarked="<?php echo $article_bookmarked ? '1' : '0'; ?>">
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
                                    document.addEventListener("DOMContentLoaded", function() {
                                        document.querySelectorAll(".bookmark-btn").forEach(button => {
                                            button.addEventListener("click", function() {
                                                let articleId = this.getAttribute("data-article-id");
                                                let isBookmarked = this.getAttribute("data-bookmarked") === "1";

                                                let action = isBookmarked ? "remove" : "add";

                                                fetch("features/bookmarks/bookmark.php", {
                                                    method: "POST",
                                                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
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
                <?php endwhile; ?>


            <?php else: ?>
                <p>No articles match that tag. Try searching for another <a
                            href="<?php echo BASE_URL ?>search.php">Here</a>.</p>
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


        <?php
        // Assuming the user is logged in and their user_id is stored in a session variable
        $current_user_id = $_SESSION['user_id'] ?? null; // Get current user's ID from session

        // Get the tags from the User_preferences table that the user has set
        $preferences_sql = "SELECT Tag FROM User_preferences WHERE User_id = ?";
        $stmt = $conn->prepare($preferences_sql);
        $stmt->bind_param("i", $current_user_id);
        $stmt->execute();
        $preferences_result = $stmt->get_result();

        // Store user preferences in an array (Tags that correspond to Category in tbl_blogs)
        $excluded_categories = [];
        while ($pref_row = $preferences_result->fetch_assoc()) {
            $excluded_categories[] = $pref_row['Tag'];
        }

        // If no preferences found, just skip the filtering for categories (avoid empty IN clause)
        $excluded_categories_str = count($excluded_categories) > 0 ? "'" . implode("','", $excluded_categories) . "'" : "'0'"; // Default to '0' if no preferences

        // Query to get articles that are not written by the logged-in user and not in the user's preferred categories
        $sql = "
                SELECT b.id, b.title, LEFT(b.content, 270) AS summary, b.datePublished, b.Category, b.Image, b.user_id, u.username
                FROM tbl_blogs b
                JOIN Users u ON b.user_id = u.user_id
                WHERE b.user_id != ? 
                  AND b.Private = 0
                  AND b.Category NOT IN ($excluded_categories_str)
                ORDER BY RAND()  -- Randomize the order of articles
                LIMIT 5
";


        // Prepare and execute the query
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("i", $current_user_id);
        $stmt2->execute();
        $non_recommended_result = $stmt2->get_result();
        ?>

        <aside class="aside-links">
            <aside class="non-recommended-articles">
                <?php if (!isset($_SESSION['user_id'])): ?>

                    <h2 class="article-title">Log in to get personal recommendations, view your favorite authors, and create your own articles</h2>
                    <h3 class="author-title"><a href="<?php echo BASE_URL; ?>user_auth.php">Sign In / Register Here</a></h3>
                <?php else: ?>

                <h2 class="aside-title">Other Articles</h2>
                <?php if ($non_recommended_result->num_rows > 0): ?>
                    <ul>
                        <?php while ($row = $non_recommended_result->fetch_assoc()): ?>
                            <li>
                                <a href="user/article.php?id=<?php echo $row['id']; ?>">
                                    <div class="article-summary">
                                        <p class="author-name"><?php echo htmlspecialchars($row['username']); ?></p>
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


                <?php
                // Fetch the most bookmarked authors
                $sql = "SELECT u.user_id, u.username, COUNT(ub.article_id) AS bookmark_count, ud.profile_picture
        FROM user_bookmarks ub
        JOIN tbl_blogs b ON ub.article_id = b.id
        JOIN Users u ON b.user_id = u.user_id
        LEFT JOIN User_details ud ON u.user_id = ud.user_id
        WHERE ub.user_id = ?
        GROUP BY u.user_id, u.username, ud.profile_picture
        ORDER BY bookmark_count DESC
        LIMIT 3";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>

                <div>
                    <h3 class="aside-title">Your Favourite Authors</h3>
                    <ul>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <li>
                                    <div class="profile-image-container">
                                        <?php
                                        // Get profile picture and handle missing profile picture
                                        $profilePic = $row['profile_picture'] ?? null;
                                        $author_id = $row['user_id'];

                                        if (!empty($profilePic) && file_exists(BASE_PATH . 'public/images/users/' . $author_id . '/' . htmlspecialchars($profilePic))) {
                                            // Display the profile picture if it exists
                                            echo '<div class="profile-pic-wrapper">
                                            <img src="' . BASE_URL . 'public/images/users/' . $author_id . '/' . htmlspecialchars($profilePic) . '" alt="Profile Picture" class="pp-author-img"></div>';
                                        } else {
                                            // If the profile picture doesn't exist, display the user's initial with a random background color
                                            $initial = strtoupper(substr($row['username'], 0, 1));  // Get the first letter of the username
                                            $randomColor = '#' . substr(md5(rand()), 0, 6); // Generate a random hex color
                                            echo '<div class="profile-initial" style="background-color: ' . $randomColor . ';">' . $initial . '</div>';
                                        }
                                        ?>
                                        <a href="<?php echo BASE_URL; ?>feed.php?username=<?php echo urlencode($row['username']); ?>" class="profile-author-name">
                                            <?php echo htmlspecialchars($row['username']); ?>
                                            <!--                                            <p>(--><?php //echo $row['bookmark_count']; ?><!-- bookmarks)</p>-->
                                        </a>
                                    </div>

                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No bookmarked authors yet.</p>
                        <?php endif; ?>
                    </ul>
                </div>





                <?php
                // Assuming $user_id is already defined
                $user_id = $_SESSION['user_id'] ?? 0; // Get the logged-in user ID

                // Step 1: Find the user's most liked category (excluding preferred ones)
                $most_liked_category_sql = "
                SELECT Category
                FROM tbl_blogs
                WHERE Category NOT IN (
                SELECT Tag FROM User_preferences WHERE User_id = ?
                )
                AND Category IS NOT NULL
                GROUP BY Category
                ORDER BY (
                SELECT COUNT(*) FROM article_likes al
                JOIN tbl_blogs tb ON al.article_id = tb.id
                WHERE tb.Category = tbl_blogs.Category
                ) DESC
                LIMIT 1
                ";

                $stmt = $conn->prepare($most_liked_category_sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $category_result = $stmt->get_result();
                $most_liked_category = $category_result->fetch_assoc()['Category'] ?? null;

                // Step 2: Fetch distinct tags from the most liked category
                if ($most_liked_category) {
                    $distinct_tags_sql = "
   SELECT tag_list.tag, COUNT(al.id) AS like_count
FROM (
    SELECT DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(b.Tags, ',', n.n), ',', -1)) AS tag, b.id AS article_id
    FROM tbl_blogs b
    JOIN (
        SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 
        UNION ALL SELECT 9 UNION ALL SELECT 10
    ) n 
    ON CHAR_LENGTH(b.Tags) - CHAR_LENGTH(REPLACE(b.Tags, ',', '')) >= n.n - 1
    WHERE b.Category = ? AND b.Private = 0
) tag_list
LEFT JOIN article_likes al ON tag_list.article_id = al.article_id
GROUP BY tag_list.tag
ORDER BY like_count DESC;

";


                    $stmt = $conn->prepare($distinct_tags_sql);
                    $stmt->bind_param("s", $most_liked_category);
                    $stmt->execute();
                    $tags_result = $stmt->get_result();
                }
                ?>

                <!-- Display Section -->
                <div class="aside-recommended-topics">
                    <h2 class="aside-title">Topics You May Like</h2>
                    <?php if ($tags_result->num_rows > 0): ?>
                        <ul>
                            <?php while ($tag_row = $tags_result->fetch_assoc()): ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>tag.php?tag=<?php echo urlencode($tag_row['tag']); ?>" class="tag-link">
                                        <?php echo htmlspecialchars($tag_row['tag']); ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>No topics available at the moment.</p>
                    <?php endif; ?>
                </div>
            </aside>

            <?php endif; ?>


        </aside>
    </div>
</main>
<script src="<?php echo BASE_URL . 'public/js/save-page-position.js' ?>"></script>
</body>
</html>
