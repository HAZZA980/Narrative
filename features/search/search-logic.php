<?php
// Ensure database connection is initialized
if (!isset($conn)) {
    die("Database connection not established.");
}

// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Initialize search term safely
$search = isset($_GET['txt-search']) ? trim($_GET['txt-search']) : '';
$search_term = !empty($search) ? "%$search%" : null;

// Order setup
$order_by = $_GET['order_by'] ?? 'datePublished';
$order_dir = $_GET['order_dir'] ?? 'DESC';

// Allowed values to prevent SQL injection
$allowed_order_by = ['datePublished', 'chronological', 'alphabetical'];
$allowed_order_dir = ['ASC', 'DESC'];

if (!in_array($order_by, $allowed_order_by)) {
    $order_by = 'datePublished';
}
if (!in_array($order_dir, $allowed_order_dir)) {
    $order_dir = 'DESC';
}

// Map order_by to actual database columns
$order_column = ($order_by === 'chronological') ? 'tbl_blogs.id' :
    (($order_by === 'alphabetical') ? 'tbl_blogs.title' : 'tbl_blogs.datePublished');

// Start building SQL query
$base_query = "
    FROM tbl_blogs 
    LEFT JOIN Users ON tbl_blogs.user_id = Users.user_id
    WHERE tbl_blogs.private = 0"; // Ensuring only public blogs

// Filter conditions
$filter_params = [];
$param_types = "";

// Apply search term filtering if provided
if (!empty($search)) {
    $base_query .= " AND (tbl_blogs.title LIKE ? 
                          OR tbl_blogs.Tags LIKE ? 
                          OR tbl_blogs.content LIKE ? 
                          OR tbl_blogs.datePublished LIKE ? 
                          OR Users.username LIKE ?)";
    $filter_params = array_fill(0, 5, $search_term);
    $param_types = str_repeat("s", count($filter_params)); // "sssss"
}

// Filter by Author, Title, Content
$authorFilter = isset($_GET['author']) && $_GET['author'] == "1";
$titleFilter = isset($_GET['title']) && $_GET['title'] == "1";
$contentFilter = isset($_GET['content']) && $_GET['content'] == "1";

$filter_conditions = [];

if ($authorFilter) {
    $filter_conditions[] = "Users.username LIKE ?";
    $filter_params[] = $search_term;
    $param_types .= "s";
}
if ($titleFilter) {
    $filter_conditions[] = "tbl_blogs.title LIKE ?";
    $filter_params[] = $search_term;
    $param_types .= "s";
}
if ($contentFilter) {
    $filter_conditions[] = "tbl_blogs.content LIKE ?";
    $filter_params[] = $search_term;
    $param_types .= "s";
}

if (!empty($filter_conditions)) {
    $base_query .= " AND (" . implode(" OR ", $filter_conditions) . ")";
}

// Filter by Date Range
if (!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
    $base_query .= " AND tbl_blogs.datePublished BETWEEN ? AND ?";
    $filter_params[] = $_GET['startDate'];
    $filter_params[] = $_GET['endDate'];
    $param_types .= "ss";
}

// Filter by Categories
if (!empty($_GET['categories'])) {
    $categories = explode(",", $_GET['categories']);
    $placeholders = implode(",", array_fill(0, count($categories), "?"));
    $base_query .= " AND tbl_blogs.Category IN ($placeholders)";
    $filter_params = array_merge($filter_params, $categories);
    $param_types .= str_repeat("s", count($categories));
}

// Get the total number of filtered results for pagination
$total_query = "SELECT COUNT(*) " . $base_query;
$total_stmt = $conn->prepare($total_query);

if ($param_types) {
    $total_stmt->bind_param($param_types, ...$filter_params);
}
$total_stmt->execute();
$total_stmt->bind_result($total_articles);
$total_stmt->fetch();
$total_stmt->close();

// Calculate total pages **AFTER FILTERING**
$total_pages = ceil($total_articles / $articles_per_page);

// Now fetch the actual articles with pagination
$main_query = "SELECT tbl_blogs.id, tbl_blogs.title, tbl_blogs.user_id, 
                      LEFT(tbl_blogs.content, 73) AS summary, 
                      tbl_blogs.datePublished, tbl_blogs.Tags, tbl_blogs.Image, 
                      Users.username " . $base_query . " 
               ORDER BY $order_column $order_dir 
               LIMIT ? OFFSET ?";

$filter_params[] = $articles_per_page;
$filter_params[] = $offset;
$param_types .= "ii";

$stmt = $conn->prepare($main_query);
$stmt->bind_param($param_types, ...$filter_params);
$stmt->execute();
$blogs_result = $stmt->get_result();
?>
