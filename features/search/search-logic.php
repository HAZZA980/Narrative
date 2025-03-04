<?php
// Ensure database connection is initialized
if (!isset($conn)) {
    die("Database connection not established.");
}

// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Initialize the search term safely
$search = isset($_GET['txt-search']) ? trim($_GET['txt-search']) : '';
$search_term = !empty($search) ? "%$search%" : null;

// Order by setup
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'datePublished';
$order_dir = isset($_GET['order_dir']) ? $_GET['order_dir'] : 'DESC';

// Prevent SQL injection by allowing only specific values
$allowed_order_by = ['datePublished', 'chronological', 'alphabetical'];
$allowed_order_dir = ['ASC', 'DESC'];

if (!in_array($order_by, $allowed_order_by)) {
    $order_by = 'datePublished';
}
if (!in_array($order_dir, $allowed_order_dir)) {
    $order_dir = 'DESC';
}

// Map order_by to actual database columns
$order_column = 'tbl_blogs.datePublished'; // Default ordering
if ($order_by === 'chronological') {
    $order_column = 'tbl_blogs.id';
} elseif ($order_by === 'alphabetical') {
    $order_column = 'tbl_blogs.title';
}

// Prepare the SQL query dynamically
$query = "
    SELECT tbl_blogs.id, tbl_blogs.title, tbl_blogs.user_id, 
           LEFT(tbl_blogs.content, 73) AS summary, 
           tbl_blogs.datePublished, tbl_blogs.Tags, tbl_blogs.Image, 
           Users.username 
    FROM tbl_blogs 
    LEFT JOIN Users ON tbl_blogs.user_id = Users.user_id
    WHERE tbl_blogs.private = 0";

$filter_params = [];
$param_types = "";

// If search is applied, modify query
if (!empty($search)) {
    $query .= " AND (tbl_blogs.title LIKE ? 
                     OR tbl_blogs.Tags LIKE ? 
                     OR tbl_blogs.content LIKE ? 
                     OR tbl_blogs.datePublished LIKE ? 
                     OR Users.username LIKE ?)";
    $filter_params = array_fill(0, 5, $search_term);
    $param_types = str_repeat("s", count($filter_params)); // "sssss"
}

// Append ordering and pagination
$query .= " ORDER BY $order_column $order_dir LIMIT ? OFFSET ?";
$filter_params[] = $articles_per_page;
$filter_params[] = $offset;
$param_types .= "ii";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$filter_params);
$stmt->execute();
$blogs_result = $stmt->get_result();

// Count total number of articles (for pagination)
$total_query = "
    SELECT COUNT(*) as total 
    FROM tbl_blogs 
    LEFT JOIN Users ON tbl_blogs.user_id = Users.user_id
    WHERE tbl_blogs.private = 0";

$total_params = [];
$total_param_types = "";

// If search is applied, modify total count query
if (!empty($search)) {
    $total_query .= " AND (tbl_blogs.title LIKE ? 
                          OR tbl_blogs.Tags LIKE ? 
                          OR tbl_blogs.content LIKE ? 
                          OR tbl_blogs.datePublished LIKE ? 
                          OR Users.username LIKE ?)";
    $total_params = array_fill(0, 5, $search_term);
    $total_param_types = str_repeat("s", count($total_params));
}

$total_stmt = $conn->prepare($total_query);
if (!empty($total_params)) {
    $total_stmt->bind_param($total_param_types, ...$total_params);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_articles = $total_row['total'] ?? 0;
$total_pages = max(1, ceil($total_articles / $articles_per_page));
?>
