<?php
ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/phpProjects/Narrative/config/config.php";
include BASE_PATH . "user/model/delete.article.php";
include BASE_PATH . "user/view/delete-article-modal.html";
include BASE_PATH . "user/model/article-logic.php";
include BASE_PATH . 'features/write/write-icon-fixed.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> | Narrative</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>user/css/styles-article.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>explore/articleLayouts/styles-default-article-formation.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>user/css/delete-article-modal.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>user/css/author-actions.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>user/css/admin-bar.css">

    <style>
        .popup {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #222;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            opacity: 0;
            animation: fadeInOut 3s ease-in-out;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-10px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-10px); }
        }

    </style>
</head>
<body>

<main class="main-container">
    <div class="main-content">
        <div class="flex-container">
            <div class="blogs-content">
                <!-- Admin Tools Bar (Visible only for admin users) -->
                <?php if (isset($_SESSION['user_id']) && $_SESSION['isAdmin'] == 1): ?>
                    <div class="admin-bar">
                        <p class="admin-taskbar-title">ADMIN TASKBAR</p>

                        <!-- Edit Icon -->
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['isAdmin'] == 1): ?>
                            <div class="img-container">
                                <a href="<?php echo BASE_URL; ?>user/edit-article.php?id=<?php echo $id; ?>">
                                    <img src="<?php echo BASE_URL ?>public/images/adminbar/pencil.png" alt="Edit">
                                </a>
                            </div>
                        <?php endif; ?>


                            <a href="javascript:void(0);"
                               class="admin-action-link delete-link"
                               data-article-id="<?php echo $id; ?>">
                                <div class="img-container">
                                    <img src="<?php echo BASE_URL ?>public/images/adminbar/delete.png" alt="Delete">
                                </div>

                            </a>


                        <!-- Category Dropdown -->
                        <form id="category-form" action="<?php echo BASE_URL ?>user/model/update_category.php" method="POST">
                            <select id="category-dropdown" name="category" data-article-id="<?php echo $blog['id']; ?>" onchange="updateCategory()">

                            <?php
                                // List of categories
                                $categories = [
                                    "Business", "Entertainment", "Food", "Gaming", "Health & Fitness",
                                    "History and Culture", "Lifestyle", "Philosophy", "Politics",
                                    "Reviews", "Science", "Sports", "Technology", "Travel", "Writing Craft"
                                ];

                                // Fetch the current category for the current article
                                $article_id = $blog['id'];  // Get the article ID
                                $sql_category = "SELECT Category FROM tbl_blogs WHERE id = $article_id"; // Fetch the current category using article ID
                                $result_category = $conn->query($sql_category);

                                // Default category (if not found in db)
                                $currentCategory = '';

                                if ($result_category->num_rows > 0) {
                                    $row = $result_category->fetch_assoc();
                                    $currentCategory = $row['Category'];  // Assuming 'Tags' field stores the category
                                }

                                // Loop through categories and set the selected attribute to the current category
                                foreach ($categories as $category) {
                                    $selected = ($category == $currentCategory) ? 'selected' : ''; // Check if category matches current category
                                    echo "<option value='$category' $selected>$category</option>"; // Output category options
                                }
                                ?>
                            </select>
                        </form>



                        <form id="featured-form">
                            <label>
                                <input type="checkbox" id="featured" name="featured"
                                       data-article-id="<?php echo $blog['id']; ?>"
                                        <?php echo ($blog['featured'] == 1) ? 'checked' : ''; ?>
                                       onchange="updateFeatured()">
                                Featured
                            </label>
                        </form>



                        <?php
                        // Fetch the user_id associated with the article
                        $query = "SELECT user_id FROM tbl_blogs WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $blog['id']); // Assuming $blog['id'] is the article_id
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($user_id);
                        $stmt->fetch();
                        $stmt->close();

                        // Fetch the freeze_user status for the associated user
                        $query = "SELECT freeze_user FROM users WHERE user_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($freeze_user);
                        $stmt->fetch();
                        $stmt->close();
                        ?>


                        <form id="freeze-user-form" action="<?php echo BASE_URL ?>admin/model/freeze_user.php" method="POST" data-article-id="<?php echo $blog['id']; ?>">
                            <button type="button" id="freeze-user" onclick="toggleFreezeUser()">
                                <?php echo $freeze_user == 1 ? 'Unfreeze User' : 'Freeze User'; ?>
                            </button>
                        </form>

                        <script>
                            function toggleFreezeUser() {
                                var button = document.getElementById("freeze-user");
                                var article_id = document.getElementById("freeze-user-form").getAttribute("data-article-id"); // Get article ID

                                // Determine the action (freeze or unfreeze)
                                var action = (button.innerText.trim() === "Freeze User") ? 1 : 0; // Freeze if the button says "Freeze", otherwise unfreeze

                                var xhr = new XMLHttpRequest();
                                xhr.open("POST", "<?php echo BASE_URL ?>admin/model/freeze_user.php", true);
                                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                                xhr.onreadystatechange = function () {
                                    if (xhr.readyState === 4) {
                                        console.log("Raw Response:", xhr.responseText); // <-- Add this line

                                        if (xhr.status === 200) {
                                            var response;
                                            try {
                                                response = JSON.parse(xhr.responseText);
                                            } catch (error) {
                                                console.error("Invalid JSON response:", xhr.responseText); // Show error
                                                return;
                                            }

                                            if (response.success) {
                                                button.innerText = (action === 1) ? "Unfreeze User" : "Freeze User";
                                                showPopup(response.message); // Use the response message
                                            } else {
                                                console.error("Failed to update freeze status.");
                                            }
                                        } else {
                                            console.error("Request failed");
                                        }
                                    }
                                };

                                xhr.send("article_id=" + encodeURIComponent(article_id) + "&action=" + encodeURIComponent(action)); // Only send article_id and action
                            }

                            // Function to show the popup
                            function showPopup(message) {
                                var existingPopup = document.querySelector(".popup");
                                if (existingPopup) {
                                    existingPopup.remove(); // Remove existing popup if any
                                }

                                var popup = document.createElement("div");
                                popup.classList.add("popup");
                                popup.innerHTML = `<p>${message}</p>`;

                                document.body.appendChild(popup);

                                // Make sure popup is visible
                                setTimeout(function () {
                                    popup.classList.add("visible");
                                }, 50); // Small delay to trigger CSS animation

                                // Remove popup after 3 seconds
                                setTimeout(function () {
                                    popup.classList.remove("visible");
                                    setTimeout(() => popup.remove(), 500);
                                }, 3000);
                            }
                        </script>



                    </div>
                <?php endif;

                $blogTitle = htmlspecialchars($blog['title']); ?>
                <h1 class="blog-title"><?php echo $blogTitle; ?></h1>
                <div class="blog-image-container">
                    <img src="<?php echo isset($blog['Image']) && !empty($blog['Image']) && $blog['Image'] !== 'narrative-logo-big.png'
                        ? BASE_URL . 'public/images/users/' . $blog['user_id'] . '/' . $blog['Image']
                        : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                </div>
                <p class="date-author"><strong>By<a
                                href="<?php echo BASE_URL; ?>feed.php?username=<?php echo urlencode($author); ?>">
                            <?php echo htmlspecialchars($author); ?></a></strong>


                    <small><?php echo date('F j, Y', strtotime($blog['datePublished'])); ?></small></p>
                <div class="blog-content">
                    <p id="blog"><?php echo nl2br(htmlspecialchars($blog['content'])); ?></p>
                </div>

                <div class="blog-details-2">


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
                            $article_id = $blog['id']; // Article ID for the current post

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
                            $article_id = $blog['id']; // Get article ID
                            $comment_query = "SELECT COUNT(*) AS comment_count FROM article_comments WHERE article_id = ?";
                            $comment_stmt = $conn->prepare($comment_query);
                            $comment_stmt->bind_param("i", $article_id);
                            $comment_stmt->execute();
                            $comment_stmt->bind_result($comment_count);
                            $comment_stmt->fetch();
                            $comment_stmt->close();
                            ?>

                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/comments-regular.svg"
                                 alt="Comments">
                            <p class="comments-count"><?php echo $comment_count; ?></p>
                            <!-- Display comment count -->
                        </div>


                        <div class="bookmark">
                            <?php
                            // Check if the user is logged in
                            if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
                                // Redirect to login page if not logged in
                                echo '<form action="' . BASE_URL . 'user_auth.php" method="GET">';
                                echo '<button type="submit" class="bookmark-btn">';
                                echo '<img src="' . BASE_URL . 'public/images/article-layout-img/file-earmark-plus.svg" alt="Add to Bookmarks" class="bookmark-icon"/>';
                                echo '</button>';
                                echo '</form>';
                            } else {
                                // Get the current user's ID
                                $user_id = $_SESSION['user_id'];

                                // Assuming you're inside the loop for displaying each article
                                $article_id = $blog['id']; // Article ID for the current post

                                // Check if the user has already bookmarked the article
                                $check_query = "SELECT * FROM user_bookmarks WHERE user_id = ? AND article_id = ?";
                                $stmt = $conn->prepare($check_query);
                                $stmt->bind_param("ii", $user_id, $article_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $article_bookmarked = $result->num_rows > 0;
                                ?>
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
                            <?php } ?>
                            <p class="bookmark-status"></p>
                        </div>

                    </div>
                </div>
                <div class="preferred_tags">
                    <?php
                    $tagsArray = explode(',', $blog['Tags']); // Split tags by comma
                    foreach ($tagsArray as $tag) {
                        $trimmedTag = trim($tag); // Trim whitespace
                        if (!empty($trimmedTag)) {
                            echo '<a href="' . BASE_URL . 'tag.php?tag=' . urlencode($trimmedTag) . '" class="tag">' . htmlspecialchars($trimmedTag) . '</a>';

                        }
                    }

                    ?>
                </div>


                <div class="comments-section">
                    <h2>Comments</h2>
                    <!-- Display existing comments -->
                    <div class="existing-comments">
                        <?php
                        ini_set('display_errors', 1);
                        ini_set('display_startup_errors', 1);
                        error_reporting(E_ALL);

                        // Fetch comments for the current article
                        if (isset($blog['user_id'])) {
                            $user_id = $blog['user_id'];
                            $article_id = $blog['id'];
                        } else {
                            echo "<p>Error: Article ID is undefined.</p>";
                            exit;
                        }

                        $comments_query = "SELECT c.id, c.comment, c.commented_at, u.username, c.user_id AS comment_user_id
                           FROM article_comments c
                           INNER JOIN users u ON c.user_id = u.user_id
                           WHERE c.article_id = ?
                           ORDER BY c.commented_at DESC";
                        $stmt = $conn->prepare($comments_query);

                        if (!$stmt) {
                            echo "<p>Error preparing statement: " . htmlspecialchars($conn->error) . "</p>";
                            exit;
                        }

                        $stmt->bind_param("i", $article_id);

                        if (!$stmt->execute()) {
                            echo "<p>Error executing statement: " . htmlspecialchars($stmt->error) . "</p>";
                            exit;
                        }

                        $comments_result = $stmt->get_result();

                        if ($comments_result->num_rows > 0):
                            while ($comment = $comments_result->fetch_assoc()):
                                $is_comment_author = (isset($_SESSION['user_id']) && $comment['comment_user_id'] == $_SESSION['user_id']);
                                ?>
                                <div class="comment-details" data-comment-id="<?php echo $comment['id']; ?>">
                                    <!-- Original Comment Display -->
                                    <div class="comment-header">
                                        <p class="comment-username">
                                            <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong></p>
                                        <?php if ($is_comment_author): ?>
                                            <span class="comment-actions">
                                            <!-- Inline Edit and Delete Links -->
                                            <a href="javascript:void(0)" class="edit-comment-toggle">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/edit-comment.svg"></a> |
                                            <a href="<?php echo BASE_URL; ?>features/comments/delete-comment.php?comment_id=<?php echo $comment['id']; ?>&article_id=<?php echo $article_id; ?>"
                                               onclick="return confirm('Are you sure you want to delete this comment?');">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/trash.svg"></a>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Comment Body -->
                                    <div class="comment-display">
                                        <p class="comment-content"><?php echo nl2br(htmlspecialchars($comment['comment'], ENT_QUOTES, 'UTF-8')); ?></p>
                                        <p class="comment-date">
                                            <small>Posted
                                                on <?php echo date('F j, Y, g:i a', strtotime($comment['commented_at'])); ?></small>
                                        </p>
                                    </div>

                                    <!-- Hidden Edit Form -->
                                    <form action="<?php echo BASE_URL; ?>features/comments/edit-comment.php"
                                          method="POST"
                                          class="edit-comment-form" style="display: none;">
                                        <textarea name="comment" placeholder="Write your comment here..."
                                                  class="auto-resizing-textarea" required>
                                            <?php echo htmlspecialchars($comment['comment']); ?></textarea>
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                        <div class="hidden-update-cancel-buttons">
                                            <button type="submit" class="btn btn-update">Update Comment</button>
                                            <button type="button" class="btn btn-cancel cancel-edit">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                                <hr>
                            <?php endwhile;
                        else: ?>
                            <p>No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>

                    <!-- Add a new comment -->
                    <h3 class="post-a-comment">POST A COMMENT</h3>
                    <form action="<?php echo BASE_URL ?>features/comments/add-comment.php" method="POST"
                          class="comment-form">
                        <textarea name="comment" placeholder="Write your comment here..." required></textarea>
                        <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">

                        <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
                            <!-- If not logged in, use the button to redirect to the login/register page -->
                            <button type="button" class="redirect-to-login-btn"
                                    onclick="window.location.href='<?php echo BASE_URL; ?>user_auth.php'">Log in to
                                comment
                            </button>
                        <?php else: ?>
                            <!-- If logged in, show the submit button -->
                            <button type="submit">Post Comment</button>
                        <?php endif; ?>
                    </form>
                </div>


            </div>
        </div>


        <aside class="aside-links">
            <aside class="aside-section">
                <?php if ($is_author): ?>
                    <aside class="aside-admin">
                        <h2 class="aside-title">Quick Actions</h2>
                        <ul class="admin-action-list">
                            <li class="admin-action-item">
                                <a href="<?php echo BASE_URL; ?>user/edit-article.php?id=<?php echo $id; ?>"
                                   class="admin-action-link item-class">Edit Article</a>
                            </li>
                            <!-- HTML part for button -->
                            <li class="admin-action-item">
                                <form action="" method="POST">
                                    <button class="admin-action-link item-class" type="submit" name="is_private"
                                            value="<?php echo $currentPrivateState == 1 ? 0 : 1; ?>"
                                            id="toggle-private-btn">
                                        <?php echo $currentPrivateState == 1 ? 'Make Public' : 'Make Private'; ?>
                                    </button>
                                </form>
                            </li>

                            <li class="admin-action-item">
                                <a href="javascript:void(0);"
                                   class="item-class admin-action-link delete-link delete-class"
                                   data-article-id="<?php echo $id; ?>">Delete Article</a>
                            </li>
                        </ul>
                    </aside>
                <?php endif; ?>


                <aside class="aside-articles-similar" id="similar-articles-section">
                    <h2 class="aside-title-header">Similar Articles</h2>

                    <?php
                    // SQL query to fetch similar articles
                    $sql = "
        (SELECT id, title, LEFT(content, 250) AS summary, datePublished, Tags, featured, Image, user_id 
         FROM tbl_blogs 
         WHERE Tags LIKE '%$get_tag%' 
           AND id != $id
           AND datePublished >= (SELECT datePublished FROM tbl_blogs WHERE id = $id)
         ORDER BY ABS(TIMESTAMPDIFF(SECOND, datePublished, (SELECT datePublished FROM tbl_blogs WHERE id = $id))) ASC, 
                  datePublished ASC
         LIMIT 3)

        UNION

        (SELECT id, title, LEFT(content, 250) AS summary, datePublished, Tags, featured, Image, user_id 
         FROM tbl_blogs 
         WHERE Tags LIKE '%$get_tag%' 
           AND id != $id
         ORDER By datePublished ASC
         LIMIT 3)
    ";

                    $result = $conn->query($sql);

                    // Check if there are any results
                    if ($result->num_rows > 0) {
                        ?>

                        <!-- Display articles only if there are results -->
                        <ul class="article-list">
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <li class="article-item">
                                    <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>" class="article-link">
                                        <div class="aside-article-summary">
                                            <div class="aside-image-container">
                                                <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                                    ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                                    : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                                            </div>

                                            <h3 class="aside-title"><?php echo htmlspecialchars($row['title'] ?? 'Untitled'); ?></h3>
                                            <div class="aside-date-container">
                                                <p class="aside-date">
                                                    <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>

                        <?php
                    } else {
                        // If no articles are found, hide the section by using JavaScript
                        echo "<script>document.getElementById('similar-articles-section').style.display = 'none';</script>";
                    }
                    ?>

                </aside>


                <aside class="aside-elsewhere-articles">
                    <h2 class="aside-title-header">Elsewhere On Narrative</h2>
                    <?php
                    // Get the current file name dynamically
                    $sql = "SELECT id, title, LEFT(content, 250) AS summary, datePublished, Tags, featured, Image, user_id 
                            FROM tbl_blogs 
                            WHERE Tags NOT LIKE '%$get_tag%' AND id != $id 
                            ORDER BY RAND() 
                            LIMIT 5";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()) {
                    // Dynamically create grid items for each blog
                    ?>
                    <ul class="article-list">
                        <li class="article-item">
                            <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['id']; ?>" class="article-link">
                                <div class="aside-article-summary">
                                    <div class="aside-image-container">
                                        <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                            ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                            : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                                    </div>

                                    <h3 class="aside-title"><?php echo htmlspecialchars($row['title'] ?? 'Untitled'); ?></h3>
                                    <div class="aside-date-container">
                                        <p class="aside-date">
                                            <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <?php };?>
                    </ul>
                </aside>

            </aside>
        </aside>
    </div>
</main>


<script>
    var BASE_URL = "<?php echo BASE_URL; ?>";
</script>
<!--<script src="--><?php //echo BASE_URL ?><!--user/js/editArticle.js"></script>-->
<script src="<?php echo BASE_URL ?>user/js/delete-article.js"></script>

<script>
    // Select all edit icons
    const editIcons = document.querySelectorAll('.edit-comment-toggle');

    // Add event listeners to toggle edit mode
    editIcons.forEach(icon => {
        icon.addEventListener('click', function () {
            const commentDetails = this.closest('.comment-details');
            const displayDiv = commentDetails.querySelector('.comment-display');
            const editForm = commentDetails.querySelector('.edit-comment-form');

            // Toggle visibility
            displayDiv.style.display = 'none';
            editForm.style.display = 'block';
        });
    });

    // Add event listeners to cancel edit mode
    const cancelButtons = document.querySelectorAll('.cancel-edit');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function () {
            const commentDetails = this.closest('.comment-details');
            const displayDiv = commentDetails.querySelector('.comment-display');
            const editForm = commentDetails.querySelector('.edit-comment-form');

            // Toggle visibility
            displayDiv.style.display = 'block';
            editForm.style.display = 'none';
        });
    });

</script>
<script>
    // Auto-resize textarea
    document.addEventListener('input', function (event) {
        if (event.target.classList.contains('auto-resizing-textarea')) {
            const textarea = event.target;
            textarea.style.height = 'auto'; // Reset height to calculate new height
            textarea.style.height = textarea.scrollHeight + 'px'; // Set to scroll height
        }
    });
</script>





<!-- JavaScript for AJAX submission -->
<script>
    function updateCategory() {
        var category = document.getElementById("category-dropdown").value;
        var article_id = document.getElementById("category-dropdown").getAttribute("data-article-id"); // Fetch article ID

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "<?php echo BASE_URL ?>user/model/update_category.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    console.log("Response:", xhr.responseText); // Debugging response
                } else {
                    console.error("Failed to update category.");
                }
            }
        };

        xhr.send("article_id=" + encodeURIComponent(article_id) + "&category=" + encodeURIComponent(category));
    }

</script>
<script>
    function updateFeatured() {
        var checkbox = document.getElementById("featured");
        var isFeatured = checkbox.checked ? 1 : 0; // Convert to 1 or 0
        var article_id = checkbox.getAttribute("data-article-id"); // Fetch article ID

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "<?php echo BASE_URL ?>user/model/update_featured.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    console.log("Response:", xhr.responseText); // Debugging response
                } else {
                    console.error("Failed to update featured status.");
                }
            }
        };

        xhr.send("article_id=" + encodeURIComponent(article_id) + "&featured=" + encodeURIComponent(isFeatured));
    }
</script>

</body>
<?php $conn->close();
ob_end_flush();
?>
</html>

