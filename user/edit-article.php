<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "user/view/delete-article-modal.html";
include BASE_PATH . "user/model/edit-article.php";
include BASE_PATH . 'features/write/write-icon-fixed.php';


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
    <link rel="stylesheet" href="<?php echo BASE_URL ?>user/css/author-actions.css">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

</head>
<body>

<main class="main-container">
    <div class="main-content">
        <!-- Confirmation Modal -->
        <div id="confirmationModal" class="modal">
            <div class="modal-content">
                <h2>Are you sure you want to save the changes?</h2>
                <div class="modal-actions">
                    <button id="confirmSave" class="btn-update">Yes</button>
                    <button id="cancelSave" class="btn-cancel">No</button>
                </div>
            </div>
        </div>

        <div class="flex-container">
            <div class="editing-container">
                <h1 class="edit-article-header">Editing Mode</h1>

                <?php if ($article): ?>
                <form id="updateForm" method="POST" enctype="multipart/form-data"
                      action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $article_id); ?>">
                    <!-- Form fields remain the same -->
                    <div class="edit-title-container">
                        <label for="title"></label>
                        <input type="text" id="blog-title" name="title"
                               value="<?php echo htmlspecialchars($article['Title']); ?>" required>
                    </div>
                    <div class="edit-content-container">
                        <label for="content"></label>
                        <textarea class="blog-content" id="content" name="content" rows="6"
                                  required><?php echo $article['Content']; ?></textarea>
                    </div>
                    <script>
                        const textarea = document.getElementById('content');

                        // Function to adjust the height based on content
                        const adjustHeight = () => {
                            textarea.style.height = 'auto'; // Reset height to auto to shrink if needed
                            textarea.style.height = (textarea.scrollHeight) + 'px'; // Adjust height to fit content
                        };

                        // Attach the event listener to automatically adjust height on input
                        textarea.addEventListener('input', adjustHeight);

                        // Initial adjustment if the textarea contains pre-filled content
                        adjustHeight();
                    </script>

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

                    <div class="edit-category-container">
                        <label class="input-tags-label" for="tags">Tags:</label>
                        <div class="suggestion-dropdown">
                        <input type="text" id="tags-input" placeholder="Enter tags" autocomplete="off">
                            <input type="hidden" name="tags" id="tags-hidden" value="<?php echo htmlspecialchars($article['Tags']); ?>">
                        <div id="suggestions"></div>
                        </div>
                    </div>


                    <?php
                    // Check if an article image exists
                    $imageUrl = null;
                    if ($article['Image']) {
                        // Construct the image URL if it exists
                        $imageUrl = BASE_URL . 'public/images/users/' . $article['user_id'] . '/' . $article['Image'];
                    }

                    // In your form, check if the image URL is set and display the image
                    ?>

                    <!-- HTML part inside edit-article.php -->
                    <div class="edit-image-container">
                        <label for="image" class="upload-image-label">UPLOAD IMAGE</label>
                        <input type="file" id="image" name="image" accept="image/*" class="image-input" onchange="previewImage(event)">

                        <!-- Image Preview Container -->
                        <div id="image-preview-container" class="image-preview-container">
                            <!-- Display the pre-existing image (if any) -->
                            <?php if ($imageUrl): ?>
                                <img id="image-preview" src="<?= $imageUrl ?>" alt="" class="image-preview">
                                <span id="remove-image" class="remove-image">×</span> <!-- X button for removing image -->
                            <?php else: ?>
                                <p>No image uploaded yet.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Hidden input holding the existing image URL, for updating/removing -->
                        <input type="hidden" id="existing-image-url" value="<?= $imageUrl ?>">
                        <input type="hidden" name="remove_image" id="remove_image" value="0">

                    </div>

            </div>
        </div>

        <aside class="aside-links">
            <aside class="aside-section">
                <?php if ($is_author || $isAdmin): ?>
                    <aside class="aside-admin">
                        <h2 class="admin-actions-title">Admin Actions</h2>
                        <ul class="admin-action-list">
                            <li class="admin-action-item">
                                <?php
                                // Fetch the freeze_user status for the user
                                $query = "SELECT freeze_user FROM users WHERE user_id = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $_SESSION['user_id']);
                                $stmt->execute();
                                $stmt->store_result();
                                $stmt->bind_result($freeze_user);
                                $stmt->fetch();
                                $stmt->close();
                                ?>
                                <?php if ($freeze_user == 0): ?>
                                <button type="submit" id="saveButton" class="item-publish item-class" >Save Changes</button>
                                <?php elseif ($freeze_user == 1): ?>
                                    <p class="freeze-warning delete">Account is frozen pending review. <br><br> Editing privileges have been suspended.</p>
                                <?php endif; ?>
                            </li>

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
                                <a href="javascript:void(0);" class="item-class admin-action-link delete-link delete-class"
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
<script>
    CKEDITOR.replace('content'); // Applies CKEditor to the textarea
    CKEDITOR.replace('content', {
        height: 300,                   // Minimum height for the editor
        resize_enabled: false,          // Disable resizing handles
        removePlugins: 'elementspath',  // Optional: removes element path in the toolbar
        contentsCss: [
            'body {font-family: Arial, sans-serif; line-height: 1.6;}'
        ],
        // Allow editor to grow and avoid internal scrollbars
        bodyClass: 'ckeditor-body',
        on: {
            instanceReady: function (evt) {
                // Prevent scrollbars when content grows
                const editor = this;
                const editorArea = editor.container.$;
                editorArea.style.overflow = 'hidden';
                editorArea.style.height = 'auto';
            }
        }
    });

</script>

</body>
</html>
