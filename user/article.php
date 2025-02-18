<?php
ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/phpProjects/Narrative/config/config.php";
include BASE_PATH . "user/model/delete.article.php";
include BASE_PATH . "user/view/delete-article-modal.html";
include BASE_PATH . "user/model/article-logic.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> | Narrative</title>
    <link rel="stylesheet" href="../public/css/styles-article-displayed.css">
    <link rel="stylesheet" href="../public/css/articleLayouts/styles-default-article-formation.css">
    <link rel="stylesheet" href="<?php echo BASE_URL?>user/css/delete-article-modal.css">
    <style>
        .comment-header {
            justify-content: space-between;
        }


        /* Style the dropdown menu */
        .dropdown-menu {
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 200px;
            display: none;
        }


        .edit-comment-form {
            display: flex;
            flex-direction: column;
        }
        .edit-comment-form textarea {
            width: 100%;
        }

        /* General Button Styling */
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Update Button Specific Styling */
        .btn-update {
            background-color: #4CAF50; /* Green */
            color: white;
        }

        .btn-update:hover {
            background-color: #45a049; /* Slightly darker green */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Cancel Button Specific Styling */
        .btn-cancel {
            background-color: #f44336; /* Red */
            color: white;
            margin-left: 10px;
        }

        .btn-cancel:hover {
            background-color: #d32f2f; /* Slightly darker red */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Optional: Add consistent spacing between buttons */
        .hidden-update-cancel-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px; /* Add space above the buttons if needed */
        }

        /* General Textarea Styling */
        .auto-resizing-textarea {
            width: 100%; /* Full width */
            min-height: 50px; /* Minimum height */
            max-height: 300px; /* Optional: Add a maximum height */
            padding: 10px;
            font-size: 14px;
            line-height: 1.5;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none; /* Disable manual resizing */
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease;
            overflow: hidden;
        }

        .auto-resizing-textarea:focus {
            border-color: #4CAF50; /* Green border on focus */
            outline: none;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .date-author {
            display: flex;
            justify-content: space-between;
        }

        .preferred_tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Spacing between tags */
        }

        .tag {
            background-color: #f0f0f0; /* Light grey background */
            color: #333; /* Darker text for contrast */
            padding: 6px 12px;
            border-radius: 15px; /* Rounded pill shape */
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .tag:hover {
            background-color: #007bff; /* Blue on hover */
            color: #fff; /* White text on hover */
        }

    </style>
</head>
<body>

<main class="main-container">
    <div class="main-content">
        <div class="flex-container">
            <div class="blogs-content">
                <?php $blogTitle = htmlspecialchars($blog['title']); ?>
                <h1 class="blog-title"><?php echo $blogTitle; ?></h1>
                <div class="blog-image-container">
                    <img src="<?php echo isset($blog['Image']) && !empty($blog['Image']) && $blog['Image'] !== 'narrative-logo-big.png'
                        ? BASE_URL . 'public/images/users/' . $blog['user_id'] . '/' . $blog['Image']
                        : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                </div>
                <p class="date-author"><strong>By <?php echo htmlspecialchars($author); ?></strong>
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
                            <form action="<?php echo BASE_URL; ?>layouts/pages/articles/like.php" method="POST"
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
                            <form action="<?php echo BASE_URL; ?>layouts/pages/articles/bookmark.php" method="POST"
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
                <div class="preferred_tags">
                    <?php
                    $tagsArray = explode(',', $blog['Tags']); // Split tags by comma
                    foreach ($tagsArray as $tag) {
                        $trimmedTag = trim($tag); // Trim whitespace
                        if (!empty($trimmedTag)) {
                            echo "<a href='tag.php?tag=" . urlencode($trimmedTag) . "' class='tag'>$trimmedTag</a>";
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
                                        <p class="comment-username"><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> </p>
                                    <?php if ($is_comment_author): ?>
                                        <span class="comment-actions">
                                    <!-- Inline Edit and Delete Links -->
                                            <a href="javascript:void(0)" class="edit-comment-toggle">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/edit-comment.svg"></a> |
                                            <a href="<?php echo BASE_URL; ?>layouts/pages/user/delete-comment.php?comment_id=<?php echo $comment['id']; ?>&article_id=<?php echo $article_id; ?>"
                                               onclick="return confirm('Are you sure you want to delete this comment?');">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/trash.svg"></a>
                                        </span>
                                    <?php endif; ?>
                                    </div>


                                    <!-- Comment Body -->
                                    <div class="comment-display">
                                        <p class="comment-content"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                        <p class="comment-date">
                                            <small>Posted on <?php echo date('F j, Y, g:i a', strtotime($comment['commented_at'])); ?></small>
                                        </p>
                                    </div>

                                    <!-- Hidden Edit Form -->
                                    <form action="<?php echo BASE_URL; ?>layouts/pages/user/edit-comment.php"
                                          method="POST"
                                          class="edit-comment-form" style="display: none;">
                                    <textarea
                                            name="comment"
                                            placeholder="Write your comment here..."
                                            class="auto-resizing-textarea"
                                            required
                                    ><?php echo htmlspecialchars($comment['comment']); ?></textarea>
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
                    <form action="<?php echo BASE_URL?>layouts/pages/articles/add-comment.php" method="POST" class="comment-form">
                        <textarea name="comment" placeholder="Write your comment here..." required></textarea>
                        <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                        <button type="submit">Post Comment</button>
                    </form>
                </div>
            </div>
        </div>


        <aside class="aside-links">

            <aside class="aside-section">
                <?php if ($is_author): ?>
                    <aside class="aside-admin">
                        <h2 class="aside-title">Admin Actions</h2>
                        <ul class="admin-action-list">
                            <li class="admin-action-item">
                                <a href="<?php echo BASE_URL; ?>user/edit-article.php?id=<?php echo $id; ?>"
                                   class="admin-action-link">Edit Article</a>
                            </li>
                            <!-- HTML part for button -->
                            <li class="admin-action-item">
                                <form action="" method="POST">
                                    <button class="admin-action-link" type="submit" name="is_private"
                                            value="<?php echo $currentPrivateState == 1 ? 0 : 1; ?>"
                                            id="toggle-private-btn">
                                        <?php echo $currentPrivateState == 1 ? 'Make Public' : 'Make Private'; ?>
                                    </button>
                                </form>
                            </li>

                            <li class="admin-action-item">
                                <a href="javascript:void(0);" class="admin-action-link" id="deleteLink"
                                   data-article-id="<?php echo $id; ?>">Delete Article</a>
                            </li>
                        </ul>
                    </aside>
                <?php endif; ?>

                <aside class="aside-articles-similar">
                    <h2 class="aside-title">Other Articles You May Like</h2>
                    <?php if ($recommended_result->num_rows > 0): ?>
                        <ul class="article-list">
                            <?php while ($row = $recommended_result->fetch_assoc()): ?>
                                <li class="article-item">
                                    <a href="article.php?id=<?php echo $row['id']; ?>" class="article-link">
                                        <div class="article-summary">
                                            <?php
                                            // Get the author's name
                                            $recommended_author_user_id = $row['user_id'];
                                            $sql_author_rec = "SELECT username FROM users WHERE user_id = $recommended_author_user_id";
                                            $recommended_author_result = $conn->query($sql_author_rec);
                                            $recommended_author = ($recommended_author_result->num_rows > 0) ? $recommended_author_result->fetch_assoc()['username'] : 'Unknown Author';
                                            ?>
                                            <p class="author-name"><?php echo htmlspecialchars($recommended_author); ?></p>
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
                        <p class="no-articles-message">No related articles found.</p>
                    <?php endif; ?>
                </aside>

                <aside class="aside-elsewhere-articles">
                    <h2 class="aside-title">Elsewhere on Narrative</h2>
                    <?php if ($elsewhere_result->num_rows > 0): ?>
                        <ul class="article-list">
                            <?php while ($row = $elsewhere_result->fetch_assoc()): ?>
                                <li class="article-item">
                                    <a href="article.php?id=<?php echo $row['id']; ?>" class="article-link">
                                        <div class="article-summary">
                                            <?php
                                            // Get the author's name for elsewhere articles
                                            $elsewhere_author_user_id = $row['user_id'];
                                            $sql_author_elsewhere = "SELECT username FROM users WHERE user_id = $elsewhere_author_user_id";
                                            $elsewhere_author_result = $conn->query($sql_author_elsewhere);
                                            $elsewhere_author = ($elsewhere_author_result->num_rows > 0) ? $elsewhere_author_result->fetch_assoc()['username'] : 'Unknown Author';
                                            ?>
                                            <p class="author-name"><?php echo htmlspecialchars($elsewhere_author); ?></p>
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
                        <p class="no-articles-message">No other articles available at the moment.</p>
                    <?php endif; ?>
                </aside>

            </aside>
        </aside>
    </div>
</main>


<script>
    var BASE_URL = "<?php echo BASE_URL; ?>";
</script>
<script src="<?php echo BASE_URL?>user/model/editArticle.js"></script>
<script src="<?php echo BASE_URL?>user/js/delete-article.js"></script>

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

</body>
<?php $conn->close();
ob_end_flush();
?>
</html>

