<?php
$message = '';
$redirect = false; // Add a flag for redirection

// Get article ID from URL (if provided)
$article_id = $_GET['id'] ?? '';

if (!is_numeric($article_id)) {
    die("Invalid article ID.");
}

// Database query to fetch article details
$stmt = $conn->prepare("SELECT * FROM tbl_blogs WHERE id = ?");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

// If article not found
if (!$article) {
    die("Article not found.");
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("User not authenticated");
    }

    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $image = $article['Image']; // Keep existing image unless changed
    $tags = trim($_POST['tags'] ?? '');

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $fileTmp = $_FILES['image']['tmp_name'];
        $fileType = mime_content_type($fileTmp);
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        // Validate MIME type and extension
        if (!in_array($fileType, $allowedMimeTypes) || !in_array($fileExtension, $allowedExtensions)) {
            die("Invalid image format. Allowed formats: jpg, png, gif, webp.");
        }

        // Check if file is an actual image
        if (!exif_imagetype($fileTmp)) {
            die("Uploaded file is not a valid image.");
        }

        // Create user directory if not exists
        $userDirectory = BASE_PATH . "public/images/users/" . $_SESSION['user_id'];
        if (!is_dir($userDirectory) && !mkdir($userDirectory, 0777, true)) {
            die("Failed to create directory for user images.");
        }

        // Secure filename to prevent double extension attacks
        $imageName = uniqid() . '.' . $fileExtension;
        $imagePath = $userDirectory . "/" . $imageName;

        if (move_uploaded_file($fileTmp, $imagePath)) {
            $image = $imageName;
        } else {
            die("Failed to upload the image.");
        }
    }

    // Ensure required fields are filled
    if ($title && $content && $tags) {
        $stmt = $conn->prepare("UPDATE tbl_blogs SET Title = ?, Content = ?, Tags = ?, featured = ?, Image = ? WHERE id = ?");
        $stmt->bind_param("sssisi", $title, $content, $tags, $featured, $image, $article_id);

        if ($stmt->execute()) {
            header("Location: " . BASE_URL . "user/article.php?id=" . $article_id);
            exit();
        } else {
            die("Error updating article: " . $stmt->error);
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}

// Get blog ID from URL
$id = intval($_GET['id']);

// Fetch blog data from tbl_blogs
$sql = "SELECT id, user_id, title, content, datePublished, Image, Tags, featured, Private FROM tbl_blogs WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $blog = $result->fetch_assoc();
} else {
    echo "Blog not found!";
    exit;
}

// Get the author's user_id
$author_user_id = $blog['user_id'];

// Fetch the author's username based on user_id
$sql_author = "SELECT username FROM users WHERE user_id = $author_user_id";
$author_result = $conn->query($sql_author);
$author = ($author_result->num_rows > 0) ? $author_result->fetch_assoc()['username'] : 'Unknown Author';

// Get the tags from the article
$get_tag = $blog['Tags'];

// Fetch articles with the same tags
$sql_recommended = "SELECT id, title, datePublished, user_id FROM tbl_blogs WHERE Tags LIKE '%$get_tag%' AND id != $id LIMIT 5";
$recommended_result = $conn->query($sql_recommended);

// Fetch articles with different tags
$sql_elsewhere = "SELECT id, title, datePublished, user_id FROM tbl_blogs WHERE Tags NOT LIKE '%$get_tag%' AND id != $id LIMIT 5";
$elsewhere_result = $conn->query($sql_elsewhere);

// Assume the logged-in user's username is stored in a session variable
$current_user = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Check if the current user is the author of the article
$is_author = ($current_user == $author);


// Start processing the form if submitted
if (isset($_POST['is_private'])) {
// Make sure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        die('User not authenticated');
    }

    $user_id = $_SESSION['user_id'];
    $is_private = $_POST['is_private'];

// Update the 'private' value for the logged-in user's blog post
    $stmt = $conn->prepare("UPDATE tbl_blogs SET private = ? WHERE user_id = ? AND id = ?");
    $stmt->bind_param("iii", $is_private, $user_id, $id);
    $stmt->execute();
    $stmt->close();

// Redirect back to the same page after the update
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit(); // Ensure no further code is executed after redirection
}

// Fetch the current 'private' status for the user and article
$stmt = $conn->prepare("SELECT private FROM tbl_blogs WHERE user_id = ? AND id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$currentPrivateState = $row['private']; // 0 or 1
$stmt->close();
?>