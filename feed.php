<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'features/write/write-icon-fixed.php';



if (isset($_GET['username'])) {
    $username = htmlspecialchars($_GET['username']);
    // Use $username to fetch the user's profile or feed data.
}

// Get the username from the URL
$username = $_GET['username'] ?? null;
if (!$username) {
    die("Username not provided.");
}

// Verify if the user exists
$user_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
if (!$user_stmt) {
    die("Error preparing user query: " . $conn->error);
}
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    die("User not found.");
}

$user_data = $user_result->fetch_assoc();
$user_id = $user_data['user_id'];

// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Fetch blogs written by the user with pagination
$query = "SELECT b.user_id, b.id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, b.Tags, b.Image 
          FROM tbl_blogs b
          WHERE b.user_id = ? AND b.Private = 0
          ORDER BY b.datePublished DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error preparing blogs query: " . $conn->error);
}

$stmt->bind_param("iii", $user_id, $articles_per_page, $offset);
$stmt->execute();
$blogs_result = $stmt->get_result();

if (!$blogs_result) {
    die("Error executing blogs query: " . $stmt->error);
}

// Count the total number of articles for pagination
$total_query = "SELECT COUNT(*) as total FROM tbl_blogs WHERE user_id = ? AND Private = 0";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $articles_per_page);


//--------------------------------------------------------------------------------------------------


// Query articles NOT in the user's preferred tags for the ASIDE bar
$non_recommended_result = null;
if (!empty($preferred_tags)) {
    $placeholders = implode(",", array_fill(0, count($preferred_tags), "?"));
    $query = "SELECT b.id, b.title, b.datePublished, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Tags NOT IN ($placeholders) 
              AND b.Private = 0 
              AND u.username != ? 
              ORDER BY b.datePublished DESC LIMIT 5";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing query: " . $conn->error);
    }

    // Dynamically bind parameters
    $params = array_merge($preferred_tags, [$_GET['username']]); // Add the username passed in the URL
    $type_str = str_repeat("s", count($preferred_tags)) . "s"; // Add an "s" for the username
    $stmt->bind_param($type_str, ...$params);

    $stmt->execute();
    $non_recommended_result = $stmt->get_result();

    if (!$non_recommended_result) {
        die("Error fetching non-recommended blogs: " . $stmt->error);
    }
} else {
    // If no preferred tags, fetch articles not authored by the username
    $query = "SELECT b.id, b.title, b.datePublished, u.username AS Author 
              FROM tbl_blogs b
              JOIN users u ON b.user_id = u.user_id
              WHERE b.Private = 0 
              AND u.username != ? 
              ORDER BY b.datePublished DESC LIMIT 5";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error preparing query: " . $conn->error);
    }

    $username = $_GET['username'];
    $stmt->bind_param("s", $username);

    $stmt->execute();
    $non_recommended_result = $stmt->get_result();

    if (!$non_recommended_result) {
        die("Error fetching non-recommended blogs: " . $stmt->error);
    }
}

//----------------------------------------------------------------------------------------------------------------------
// Query topics (tags) NOT in the user's preferred tags for the ASIDE Bar
$non_recommended_topics_result = null;
if (!empty($preferred_tags)) {
    $placeholders = implode(",", array_fill(0, count($preferred_tags), "?"));
    $query = "SELECT DISTINCT Tags FROM tbl_blogs WHERE Tags NOT IN ($placeholders) AND Private = 0";
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


// Fetch the user_id corresponding to the username
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows == 0) {
    die("User not found.");
}

// Prepare and execute the query
$stmt1 = $conn->prepare("SELECT user_id, profile_picture, bio FROM user_details WHERE user_id = ?");
if (!$stmt1) {
    die("Error preparing statement: " . $conn->error);
}
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$result = $stmt1->get_result();

// Fetch user details
$user = $result->fetch_assoc();
$user_id = $user['user_id'] ?? null;
$profilePic = $user['profile_picture'] ?? null;  // Default to null if not found
$bio = $user['bio'] ?? null; // Default to null if not found

$stmt->close();

// Fetch user's preferences from user_preferences
$stmt = $conn->prepare("SELECT DISTINCT tag FROM user_preferences WHERE user_id = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$preferred_categories = [];
while ($row = $result->fetch_assoc()) {
    $preferred_categories[] = $row['tag']; // Assuming 'tag' holds category names
}

$stmt->close();

// If no preferences found, set a default message
if (empty($preferred_categories)) {
    $preferred_categories[] = 'No preferences provided';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($username); ?>'s Profile | Narrative</title>
    <link rel="stylesheet" href="public/css/styles-forYou.css">
    <link rel="stylesheet" href="features/pagination/css/pagination.css">
    <link rel="stylesheet" href="explore/articleLayouts/styles-default-article-formation.css">
    <style>
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
                <?php echo "Latest by " . htmlspecialchars($username); ?></h1>
            <p>Explore all articles written by <?php echo htmlspecialchars($username); ?>.</p>

            <?php if ($blogs_result->num_rows > 0): ?>
                <?php while ($row = $blogs_result->fetch_assoc()): ?>
                    <div class="flex-item">
                        <div class="article-author-and-topic">
                            <a href="#" class="aa" id="author-name">
                                <?php echo $username ?>
                            </a>
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
                                    <a href="<?php echo BASE_URL; ?>layouts/pages/articles/article.php?id=<?php echo $row['id'] ?>"
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
                                    <form action="features/bookmarks/bookmark.php" method="POST" class="bookmark-form">
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
                <p>No Articles Found for <?php echo htmlspecialchars($username); ?>.</p>
            <?php endif; ?>

            <div class="pagination">
                <?php if ($total_pages > 1): ?>
                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <a href="?username=<?php echo urlencode($username); ?>&page=<?php echo $page; ?>"
                           class="pagination-link <?php echo $current_page == $page ? 'current' : ''; ?>"
                           aria-label="Page <?php echo $page; ?>">
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>




        <?php
        // Assuming the user is logged in and their user_id is stored in a session variable
        $current_user_id = $_SESSION['user_id'] ?? null; // Get current user's ID from session

        // Ensure that the user is logged in
        if (!$current_user_id) {
            die('User not logged in');
        }

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

        </aside>
    </div>
</main>
<script src="<?php echo BASE_URL . 'public/js/save-page-position.js' ?>"></script>
</body>
</html>
