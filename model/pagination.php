<?php
// Get the order parameter from the URL (if it exists)
$order = $_GET['order'] ?? 'date_desc'; // Default to newest to oldest

// Determine the SQL ORDER BY clause based on the selected order
switch ($order) {
    case 'date_asc':
        $order_by = "datePublished ASC";
        break;
    case 'date_desc':
        $order_by = "datePublished DESC";
        break;
    case 'alphabetical':
        $order_by = "title ASC";
        break;
    case 'tags':
        $order_by = "Tags ASC";
        break;
    default:
        $order_by = "datePublished DESC"; // Default order
        break;
}

// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Fetch blogs written by the currently logged-in user using the username
$query = "SELECT id, title, LEFT(content, 100) AS summary, datePublished, Tags, Image, Author 
          FROM tbl_blogs WHERE Author = ? ORDER BY $order_by LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error preparing blogs query: " . $conn->error);
}

$stmt->bind_param("sii", $username, $articles_per_page, $offset); // Use username for matching
$stmt->execute();
$blogs_result = $stmt->get_result();

if (!$blogs_result) {
    die("Error fetching blogs: " . $stmt->error);
}

// Count the total number of articles written by the user for pagination
$total_query = "SELECT COUNT(*) as total FROM tbl_blogs WHERE Author = ?";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("s", $username); // Use username for matching
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $articles_per_page);
























?>
