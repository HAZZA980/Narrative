<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "user/view/delete-article-modal.html";
include BASE_PATH . "user/model/edit-article.php";

// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "home.php");
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article</title>
    <link rel="stylesheet" href="<?php echo BASE_URL?>user/css/styles-edit-article.css">
    <link rel="stylesheet" href="<?php echo BASE_URL?>user/css/delete-article-modal.css">
</head>
<body>

<main class="main-container">
    <div class="main-content">
        <!-- Confirmation Modal -->
        <div id="confirmationModal" class="modal">
            <div class="modal-content">
                <h2>Are you sure you want to save the changes?</h2>
                <div class="modal-actions">
                    <button id="confirmSave" class="modal-button">Yes</button>
                    <button id="cancelSave" class="modal-button">No</button>
                </div>
            </div>
        </div>

        <div class="flex-container">
            <div class="container">
                <h1 class="edit-article-header">Editing Mode</h1>

                <?php if ($article): ?>
                <form id="updateForm" method="POST" enctype="multipart/form-data"
                      action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $article_id); ?>">
                    <!-- Form fields remain the same -->
                    <div class="edit-title-container">
                        <label for="title">Title:</label>
                        <input type="text" id="blog-title" name="title"
                               value="<?php echo htmlspecialchars($article['Title']); ?>" required>
                    </div>
                    <div class="edit-content-container">
                        <label for="content">Content:</label>
                        <textarea class="blog-content" id="content" name="content" rows="6"
                                  required><?php echo htmlspecialchars($article['Content']); ?></textarea>
                    </div>
                    <div class="edit-category-container">
                        <label for="tags">Tags:</label>
                        <div id="selected-tags">
                            <?php
                            $tagsArray = explode(',', $article['Tags']);
                            foreach ($tagsArray as $tag) {
                                $trimmedTag = trim($tag);
                                if ($trimmedTag !== '') {
                                    echo "<span class='tag'>$trimmedTag <span class='remove-tag'>&times;</span></span>";
                                }
                            }
                            ?>
                        </div>
                        <input type="text" id="tags-input" placeholder="Enter tags" autocomplete="off">
                        <div id="suggestions"></div>
                        <input type="hidden" name="tags" id="tags-hidden" value="<?php echo htmlspecialchars($article['Tags']); ?>">
                    </div>


                    <div class="edit-image-container">
                        <label for="image">Upload Image (optional):</label>
                        <input type="file" id="image" name="image" accept="image/*">
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
                                <button type="submit" id="saveButton">Save Changes</button>
                            </li>
                            <li class="admin-action-item">
                                <form action="" method="POST">
                                    <button class="admin-action-link" type="submit" name="is_private"
                                            value="<?php echo $currentPrivateState == 1 ? 0 : 1; ?>"
                                            id="toggle-private-btn">
                                        <?php echo $currentPrivateState == 1 ? 'Publish' : 'Change to Private'; ?>
                                    </button>
                                </form>
                            </li>
                            <li class="admin-action-item">
                                <a href="javascript:void(0);" class="admin-action-link delete-link"
                                   data-article-id="<?php echo $id; ?>">Delete Article</a>
                            </li>
                        </ul>
                    </aside>

                <?php endif; ?>
                <aside class="aside-last-updated">
                    <p>Last Updated: <?php echo htmlspecialchars($article['LastUpdated']) ?></p>
                </aside>

            </aside>


            <!-- Hidden submit button for form submission after confirmation -->
            <button type="submit" id="hiddenSubmit" style="display: none;">Hidden Submit</button>
            </form>

            <?php endif; ?>
        </aside>

    </div>
</main>
<script>
    var BASE_URL = "<?php echo BASE_URL; ?>";
</script>
<script src="<?php echo BASE_URL?>user/js/delete-article.js"></script>
<script src="<?php echo BASE_URL?>user/js/editArticle.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?php echo BASE_URL ?>model/subcategories.js"></script>

</body>
</html>
