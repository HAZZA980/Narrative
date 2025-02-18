<?php
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