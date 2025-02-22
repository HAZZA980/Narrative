<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/narrative/config/config.php';
include BASE_PATH . 'model/subcategories.php';
include BASE_PATH . 'user/model/createArticle.php';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Article</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>user/css/styles-edit-article.css">
    <style>

    </style>
</head>
<body>

<main class="main-container">
    <div class="main-content">

        <div class="flex-container">
            <div class="container">
                <h1 class="edit-article-header">Create Article</h1>

                <?php if ($message): ?>
                    <p class="<?php echo strpos($message, 'successfully') !== false ? 'message' : 'error'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="updateForm">

                    <div class="edit-title-container">
                        <label for="title">Title:</label>
                        <input autocomplete="off" type="text" id="blog-title" name="title"
                               placeholder="Enter article title"
                               required>
                    </div>

                    <div class="edit-content-container">
                        <label for="content">Content:</label>
                        <textarea class="blog-content" id="content" name="content" rows="6"
                                  placeholder="Write your article content here..." required></textarea>
                    </div>

                    <div id="selected-tags"></div>

                    <div class="edit-category-container">
                        <label class="input-tags-label" for="tags">Tags:</label>
                        <div class="suggestion-dropdown">
                            <input type="text" id="tags-input" placeholder="Type a tag..." autocomplete="off">
                            <input type="hidden" name="tags" id="tags-hidden">
                            <div id="suggestions" class="suggestions"></div>

                        </div>
                    </div>

                    <div class="edit-image-container">
                        <label for="image" class="upload-image-label">UPLOAD IMAGE</label>
                        <input type="file" id="image" name="image" accept="image/*" class="image-input" onchange="previewImage(event)">
                        <div id="image-preview-container" class="image-preview-container">
                            <img id="image-preview" src="" alt="Image Preview" class="image-preview">
                            <span id="remove-image" class="remove-image">×</span> <!-- X button for removing image -->
                        </div>
                    </div>



            </div>
        </div>

        <aside class="aside-links">
            <aside class="aside-section">
                <aside class="aside-admin">
                    <h2 class="aside-title">Admin Actions</h2>
                    <ul class="admin-action-list">
                        <li class="admin-action-item">
                            <button type="submit" name="submit_article" value="draft" id="submit-article">Save Draft
                            </button>
                        </li>
                        <li class="admin-action-item">
                            <button type="submit" name="submit_article" value="create" id="submit-article">Publish
                                Article
                            </button>
                        </li>
                    </ul>
                </aside>
            </aside>
            </form>
        </aside>
    </div>
</main>
<script src="<?php echo BASE_URL ?>model/subcategories.js"></script>
<script src="<?php echo BASE_URL ?>user/js/createArticle.js"></script>
<script>

</script>
</body>
</html>


