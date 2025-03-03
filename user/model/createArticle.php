<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/htmlpurifier-4.15.0/library/HTMLPurifier.auto.php'; // Adjust path

// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "user_auth.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $user_id = $_SESSION['user_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $image = null;

    // ✅ Sanitize Content with HTMLPurifier
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', 'p,b,strong,i,em,u,ul,ol,li,br,blockquote'); // Allow safe formatting
    $purifier = new HTMLPurifier($config);

    $rawContent = $_POST['content']; // User input
    $cleanContent = $purifier->purify($rawContent); // Purify input

    // Prevent script injection
    if (strpos($cleanContent, '<script>') !== false) {
        die("Invalid content detected!");
    }

    // ✅ Determine Action: Publish or Save as Draft
    $action = $_POST['submit_article'] ?? 'create';
    $private = ($action === 'draft') ? 1 : 0;

    // ✅ Define User's Image Directory
    $userDirectory = BASE_PATH . "public/images/users/" . $user_id;

    if (!is_dir($userDirectory) && !mkdir($userDirectory, 0777, true)) {
        error_log("Failed to create directory: $userDirectory");
        die("Failed to create directory for user images.");
    }

    // ✅ Handle Secure Image Upload
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        $imageName = basename($_FILES['image']['name']);
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageSize = $_FILES['image']['size'];
        $imageType = mime_content_type($imageTmpName);
        $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        // Validate File Extension & MIME Type
        if (!in_array($imageExtension, $allowedExtensions) || !in_array($imageType, $allowedMimeTypes)) {
            die("Invalid file type! Allowed: JPG, PNG, GIF, WEBP.");
        }

        // ✅ Generate Unique Filename
        $newImageName = uniqid("img_", true) . "." . $imageExtension;
        $imagePath = $userDirectory . "/" . $newImageName;

        if (move_uploaded_file($imageTmpName, $imagePath)) {
            $image = $newImageName; // Store in DB
        } else {
            die("Failed to upload the image.");
        }
    } else {
        $image = 'narrative-logo-big.png'; // Default image
    }

    // ✅ Category Assignment Based on Tags
    $category = 'Uncategorized';
    if (!empty($tags)) {
        $tagArray = array_map('trim', explode(',', $tags));
        $firstTag = strtolower($tagArray[0]);

        // Assign category based on the first tag
        foreach ($subcategories as $categoryKey => $subcategoryArray) {
            if (in_array($firstTag, array_map('strtolower', $subcategoryArray))) {
                $category = $categoryKey;
                break;
            }
        }
    }

    // ✅ Validate Required Fields
    if (!empty($title) && !empty($cleanContent) && !empty($tags)) {
        $stmt = $conn->prepare("INSERT INTO tbl_blogs (user_id, Type, Tags, Category, LastUpdated, DatePublished, Title, Content, featured, Image, Private) 
                                VALUES (?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?)");

        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            die("Database prepare error: " . $conn->error);
        }

        $type = 'General';
        $stmt->bind_param("isssssisi", $user_id, $type, $tags, $category, $title, $cleanContent, $featured, $image, $private);

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
            error_log("Execute failed: " . $stmt->error);
            die("Error executing statement: " . $stmt->error);
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}
?>
