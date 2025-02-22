<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';


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
        /* Profile Image Container */
        .profile-image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 10px;
        }

        /* Profile initial style */
        .profile-initial {
            width: 100px;   /* Same size as the profile image container */
            height: 100px;  /* Same size as the profile image container */
            border-radius: 50%;  /* Makes the div circular */
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;   /* Text color */
            font-size: 40px; /* Adjust font size as needed */
            font-weight: bold;
            text-transform: uppercase;  /* Ensures the initial is uppercase */
        }

        /* Profile Image */
        .profile-image-container img {
            display: block;
            width: 200px;  /* Set a fixed width */
            height: 200px; /* Set the same height to keep it circular */
            object-fit: cover; /* Ensures the image fills the circle */
            border-radius: 50%; /* Makes it perfectly circular */
            border: 2px solid #ddd;
        }


        .profile-header {
            text-align: left;
            margin: 20px 0;
            margin-top: 3rem;
        }

        .profile-header h1 {
            font-size: 2rem;
            color: #333;
        }

        .profile-header p {
            font-size: 1rem;
            color: #666;
        }



        .profile-details {
            display: flex;
            flex-direction: column;
        }

        .profile-details .profile-details-top {
            display: flex;
            flex-direction: row;
            align-items: baseline;
            justify-content: space-between;
        }

        /* User Info */
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            width: 100%;
        }

        /* Username Title */
        .user-info-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin: 5px 0;
        }

        /* Bio Styling */
        .user-info-bio {
            font-size: 1rem;
            padding: 10px;
            border-radius: 8px;
            max-width: 90%;
            text-align: center;
            border: 1px solid #ddd;
        }

        /* Responsive Adjustments */
        @media screen and (max-width: 768px) {
            .aside-user-profile {
                max-width: 100%;
                padding: 15px;
            }

            .user-info-title {
                font-size: 1.3rem;
            }

            .user-info-bio {
                font-size: 0.95rem;
            }
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




        <aside class="aside-links">
            <aside class="non-recommended-articles">
                <section class="profile-header-container">
                    <div class="profile-image-container">
                        <?php
                        // Check if the profile picture is null or empty
                        if (!empty($profilePic) && file_exists(BASE_PATH . 'public/images/users/' . $user_id . '/' . htmlspecialchars($profilePic))) {
                            // Display the profile picture if it exists
                            echo '<img src="' . BASE_URL . 'public/images/users/' . $user_id . '/' . htmlspecialchars($profilePic) . '" alt="Profile Picture">';
                        } else {
                            // If the profile picture is null, display the user's initial with a random background color
                            $initial = strtoupper(substr($username, 0, 1));  // Get the first letter of the username
                            $randomColor = '#' . substr(md5(rand()), 0, 6); // Generate a random hex color
                            echo '<div class="profile-initial" style="background-color: ' . $randomColor . ';">' . $initial . '</div>';
                        }
                        ?>
                    </div>
                </section>

                <div class="aside-user-profile">
                    <div class="user-info">
                        <h2 class="user-info-title"><?php echo htmlspecialchars($username); ?></h2>
                        <p class="user-info-bio">
                            <?php
                            // Check if the bio is null and avoid passing null to htmlspecialchars
                            echo !empty($bio) ? htmlspecialchars($bio) : 'No bio available.';
                            ?>
                        </p>
                    </div>
                </div>




            </aside>
        </aside>
    </div>
</main>
<script src="<?php echo BASE_URL . 'public/js/save-page-position.js' ?>"></script>
</body>
</html>
