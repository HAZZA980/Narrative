<?php

// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "user_auth.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $tags = $_POST['tags'] ?? '';  // Tags sent as a comma-separated string
    $user_id = $_SESSION['user_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $image = null;

    // Check which button was clicked
    $action = $_POST['submit_article'] ?? 'create';
    $private = ($action === 'draft') ? 1 : 0;

    // Define the user's image directory
    $userDirectory = BASE_PATH . "public/images/users/" . $user_id;

    if (!is_dir($userDirectory) && !mkdir($userDirectory, 0777, true)) {
        error_log("Failed to create directory: $userDirectory. Check permissions.");
        die("Failed to create directory for user images.");
    }

    // Handle image upload
    // Handle image upload securely
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        $imageName = basename($_FILES['image']['name']);
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageSize = $_FILES['image']['size'];
        $imageType = mime_content_type($imageTmpName); // Get actual MIME type
        $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        // Validate file extension
        if (!in_array($imageExtension, $allowedExtensions)) {
            die("Invalid file extension! Only JPG, PNG, GIF, and WEBP are allowed.");
        }

        // Validate MIME type
        if (!in_array($imageType, $allowedMimeTypes)) {
            die("Invalid file type! Please upload a valid image.");
        }

//        // Restrict file size (2MB limit)
//        if ($imageSize > 2 * 1024 * 1024) {
//            die("File size too large! Max size allowed is 2MB.");
//        }

        // Generate a unique filename to prevent overwriting
        $newImageName = uniqid("img_", true) . "." . $imageExtension;
        $imagePath = $userDirectory . "/" . $newImageName;

        if (move_uploaded_file($imageTmpName, $imagePath)) {
            $image = $newImageName; // Store this in the database
        } else {
            die("Failed to upload the image.");
        }
    } else {
        $image = 'narrative-logo-big.png';
    }


    // Ensure that tags are provided
    $category = 'Uncategorized'; // Default if no match found
    if (!empty($tags)) {
        // Convert comma-separated tags into an array and trim spaces
        $tagArray = array_map('trim', explode(',', $tags));
        $firstTag = strtolower($tagArray[0]);  // Get the first tag and convert to lowercase

        // Iterate through subcategories and check if the first tag matches any subcategory
        foreach ($subcategories as $categoryKey => $subcategoryArray) {
            foreach ($subcategoryArray as $subcategory) {
                if (strtolower($subcategory) === $firstTag) {
                    $category = $categoryKey;
                    break 2;  // Exit both loops once a match is found
                }
            }
        }
    }

    // Validate required fields
    if (!empty($title) && !empty($content) && !empty($tags)) {
        // Prepare the SQL statement and ensure it matches the bind parameters
        $stmt = $conn->prepare("INSERT INTO tbl_blogs (user_id, Type, Tags, Category, LastUpdated, DatePublished, Title, Content, featured, Image, Private) 
                               VALUES (?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?)");

        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            die("Database prepare error: " . $conn->error);
        }

        // Bind parameters correctly, ensuring the correct number of placeholders
        $type = 'General';
        $stmt->bind_param("isssssisi", $user_id, $type, $tags, $category, $title, $content, $featured, $image, $private);

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
ob_end_flush();
?>