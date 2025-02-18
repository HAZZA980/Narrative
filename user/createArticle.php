<?php
ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/phpProjects/Narrative/config/config.php";
include $_SERVER["DOCUMENT_ROOT"] . "/phpProjects/Narrative/model/subcategories.php";


// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "layouts/pages/user/signIn_register.php");
    exit;
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $tag = $_POST['tags'] ?? ''; // Get tags from hidden input
    $user_id = $_SESSION['user_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $image = null;

    // Check if saving as draft
    $action = $_POST['submit_article'] ?? 'create';
    $private = ($action === 'draft') ? 1 : 0;

    // Handle image upload
    $userDirectory = BASE_PATH . "public/images/users/" . $user_id;
    if (!is_dir($userDirectory)) {
        if (!mkdir($userDirectory, 0777, true)) {
            error_log("Failed to create directory: $userDirectory. Check permissions.");
            die("Failed to create directory for user images.");
        }
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $userDirectory . "/" . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = $imageName;
        } else {
            $message = "Failed to upload the image.";
        }
    } else {
        $image = 'narrative-logo-big.png'; // Default image
    }

    // Validate required fields
    if ($title && $content && $tag) {
        // Include predefined categories
        $categories = [
            "Lifestyle", "Writing Craft", "Travel", "Reviews", "History & Culture", "Entertainment",
            "Business", "Technology", "Politics", "Science", "Sports", "Health & Fitness", "Food & Drink"
        ];

        // Convert tags into an array
        $tagArray = explode(', ', $tag);
        $selectedCategory = 'General'; // Default category if no match is found

        // Loop through categories and match against subcategories from `subcategories.php`
        foreach ($categories as $category) {
            if (isset($subcategories[$category])) { // Ensure subcategories exist for the category
                foreach ($tagArray as $singleTag) {
                    if (in_array(trim(strtolower($singleTag)), array_map('strtolower', $subcategories[$category]))) {
                        $selectedCategory = $category;
                        break 2; // Stop checking once a match is found
                    }
                }
            }
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO tbl_blogs (user_id, Type, Tags, Category, LastUpdated, DatePublished, Title, Content, featured, Image, Private) 
                               VALUES (?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $type = 'General'; // Default type
        $stmt->bind_param("issssisis", $user_id, $type, $tag, $selectedCategory, $title, $content, $featured, $image, $private);

        if ($stmt->execute()) {
            $messageType = ($action === 'draft') ? 'saved as a draft' : 'published successfully';
            echo '<script>
                alert("Article ' . $messageType . '!");
                setTimeout(function() {
                    window.location.href = "../forYou.php";
                }, 1);
            </script>';
            exit;
        } else {
            die("Error creating article: " . $stmt->error);
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Article</title>
    <link rel="stylesheet" href="css/styles-edit-article.css">
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

                    <div class="edit-category-container">
                        <label for="tags">Tags:</label>
                        <input type="text" id="tags-input" placeholder="Type a tag..." autocomplete="off">
                        <div id="suggestions" class="suggestions"></div>
                        <div id="selected-tags"></div>
                        <input type="hidden" name="tags" id="tags-hidden">
                    </div>

                    <div class="edit-image-container">
                        <label for="image">Upload Image (optional):</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>

            </div>
        </div>

        <aside class="aside-links">


            <aside class="aside-section">
                <aside class="aside-admin">
                    <h2 class="aside-title">Admin Actions</h2>
                    <ul class="admin-action-list">
                        <li class="admin-action-item">
                            <button type="submit" name="submit_article" value="draft" id="submit-article">Save in Drafts</button>
                        </li>
                        <li class="admin-action-item">
                            <button type="submit" name="submit_article" value="create" id="submit-article">Create Article</button>
                        </li>

                    </ul>
                </aside>
            </aside>
            </form>

        </aside>
    </div>
</main>

</body>
</html>

<script src="<?php echo BASE_URL?>model/subcategories.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        const tagsInput = document.getElementById("tags-input");
        const suggestionsBox = document.getElementById("suggestions");
        const selectedTagsContainer = document.getElementById("selected-tags");
        const hiddenTagsInput = document.getElementById("tags-hidden");
        let selectedTags = [];

        // Show suggestions when user types
        tagsInput.addEventListener("input", function () {
            const query = tagsInput.value.toLowerCase().trim();
            suggestionsBox.innerHTML = "";

            if (query.length === 0) return;

            // Find matching subcategories
            let matches = [];
            for (const [category, subcategories] of Object.entries(categories)) {
                subcategories.forEach(sub => {
                    if (sub.toLowerCase().includes(query) && !selectedTags.includes(sub)) {
                        matches.push({ sub, category });
                    }
                });
            }

            // Display suggestions
            matches.slice(0, 5).forEach(match => {
                const suggestion = document.createElement("div");
                suggestion.classList.add("suggestion-item");
                suggestion.innerHTML = `<strong>${match.sub}</strong> <small>(${match.category})</small>`;
                suggestion.addEventListener("click", () => selectTag(match.sub));
                suggestionsBox.appendChild(suggestion);
            });
        });

        // Select a tag
        function selectTag(tag) {
            if (!selectedTags.includes(tag)) {
                selectedTags.push(tag);

                const tagElement = document.createElement("span");
                tagElement.classList.add("tag");
                tagElement.innerHTML = `${tag} <span class="remove-tag">&times;</span>`;
                selectedTagsContainer.appendChild(tagElement);

                // Update hidden input value
                hiddenTagsInput.value = selectedTags.join(",");

                // Remove tag on click
                tagElement.querySelector(".remove-tag").addEventListener("click", function () {
                    selectedTags = selectedTags.filter(t => t !== tag);
                    tagElement.remove();
                    hiddenTagsInput.value = selectedTags.join(",");
                });
            }

            tagsInput.value = "";
            suggestionsBox.innerHTML = "";
        }
    });
</script>
