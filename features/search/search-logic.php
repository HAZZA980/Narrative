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
$query = "
    SELECT tbl_blogs.id, tbl_blogs.title, tbl_blogs.user_id, 
           LEFT(tbl_blogs.content, 73) AS summary, 
           tbl_blogs.datePublished, tbl_blogs.Tags, tbl_blogs.Image, 
           Users.username 
    FROM tbl_blogs 
    LEFT JOIN Users ON tbl_blogs.user_id = Users.user_id
    WHERE tbl_blogs.private = 0";

// Filter parameters
$filter_params = [];
$param_types = "";

// ✅ Apply Search Term *only* if provided
if (!empty($search)) {
    $query .= " AND (tbl_blogs.title LIKE ? 
                     OR tbl_blogs.Tags LIKE ? 
                     OR tbl_blogs.content LIKE ? 
                     OR tbl_blogs.datePublished LIKE ? 
                     OR Users.username LIKE ?)";
    $filter_params = array_fill(0, 5, $search_term);
    $param_types = str_repeat("s", count($filter_params)); // "sssss"
}

// ✅ Apply Advanced Filters even if search is empty
$authorFilter = isset($_GET['author']) && $_GET['author'] == "1";
$titleFilter = isset($_GET['title']) && $_GET['title'] == "1";
$contentFilter = isset($_GET['content']) && $_GET['content'] == "1";

if ($authorFilter || $titleFilter || $contentFilter) {
    $conditions = [];

    if ($authorFilter) {
        $conditions[] = "Users.username LIKE ?";
        $filter_params[] = $search_term ?: "%"; // If no search, match all authors
        $param_types .= "s";
    }
    if ($titleFilter) {
        $conditions[] = "tbl_blogs.title LIKE ?";
        $filter_params[] = $search_term ?: "%"; // If no search, match all titles
        $param_types .= "s";
    }
    if ($contentFilter) {
        $conditions[] = "tbl_blogs.content LIKE ?";
        $filter_params[] = $search_term ?: "%"; // If no search, match all content
        $param_types .= "s";
    }

    $query .= " AND (" . implode(" OR ", $conditions) . ")";
}

// ✅ Apply Date Range Filter
if (!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
    $query .= " AND tbl_blogs.datePublished BETWEEN ? AND ?";
    $filter_params[] = $_GET['startDate'];
    $filter_params[] = $_GET['endDate'];
    $param_types .= "ss";
}

// ✅ Apply Category Filter
if (!empty($_GET['categories'])) {
    $categories = explode(",", $_GET['categories']);
    $placeholders = implode(",", array_fill(0, count($categories), "?"));
    $query .= " AND tbl_blogs.Category IN ($placeholders)";
    $filter_params = array_merge($filter_params, $categories);
    $param_types .= str_repeat("s", count($categories));
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
?>
