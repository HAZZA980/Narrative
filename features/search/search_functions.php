<?php

function searchArticles(mysqli $conn, array $params): array
{
    $articles_per_page = 15;
    $current_page = isset($params['page']) && is_numeric($params['page']) ? max(1, (int)$params['page']) : 1;
    $offset = ($current_page - 1) * $articles_per_page;

    $search = isset($params['txt-search']) ? trim($params['txt-search']) : '';
    $search_term = !empty($search) ? "%$search%" : null;

    $order_by = $params['order_by'] ?? 'datePublished';
    $order_dir = $params['order_dir'] ?? 'DESC';

    $allowed_order_by = ['datePublished', 'chronological', 'alphabetical'];
    $allowed_order_dir = ['ASC', 'DESC'];

    if (!in_array($order_by, $allowed_order_by)) {
        $order_by = 'datePublished';
    }
    if (!in_array($order_dir, $allowed_order_dir)) {
        $order_dir = 'DESC';
    }

    $order_column = ($order_by === 'chronological') ? 'tbl_blogs.id' :
        (($order_by === 'alphabetical') ? 'tbl_blogs.title' : 'tbl_blogs.datePublished');

    $base_query = "
        FROM tbl_blogs 
        LEFT JOIN Users ON tbl_blogs.user_id = Users.user_id
        WHERE tbl_blogs.private = 0";

    $filter_params = [];
    $param_types = "";
    $filter_conditions = [];

    if (!empty($search)) {
        $base_query .= " AND (tbl_blogs.title LIKE ? 
                              OR tbl_blogs.Tags LIKE ? 
                              OR tbl_blogs.content LIKE ? 
                              OR tbl_blogs.datePublished LIKE ? 
                              OR Users.username LIKE ?)";
        $filter_params = array_fill(0, 5, $search_term);
        $param_types = str_repeat("s", 5);
    }

    if (!empty($params['author']) && $params['author'] == "1") {
        $filter_conditions[] = "Users.username LIKE ?";
        $filter_params[] = $search_term;
        $param_types .= "s";
    }

    if (!empty($params['title']) && $params['title'] == "1") {
        $filter_conditions[] = "tbl_blogs.title LIKE ?";
        $filter_params[] = $search_term;
        $param_types .= "s";
    }

    if (!empty($params['content']) && $params['content'] == "1") {
        $filter_conditions[] = "tbl_blogs.content LIKE ?";
        $filter_params[] = $search_term;
        $param_types .= "s";
    }

    if (!empty($filter_conditions)) {
        $base_query .= " AND (" . implode(" OR ", $filter_conditions) . ")";
    }

    if (!empty($params['startDate']) && !empty($params['endDate'])) {
        $base_query .= " AND tbl_blogs.datePublished BETWEEN ? AND ?";
        $filter_params[] = $params['startDate'];
        $filter_params[] = $params['endDate'];
        $param_types .= "ss";
    }

    if (!empty($params['categories'])) {
        $categories = explode(",", $params['categories']);
        $placeholders = implode(",", array_fill(0, count($categories), "?"));
        $base_query .= " AND tbl_blogs.Category IN ($placeholders)";
        $filter_params = array_merge($filter_params, $categories);
        $param_types .= str_repeat("s", count($categories));
    }

    // Total count query
    $total_query = "SELECT COUNT(*) " . $base_query;
    $total_stmt = $conn->prepare($total_query);
    if ($param_types) {
        $total_stmt->bind_param($param_types, ...$filter_params);
    }
    $total_stmt->execute();
    $total_stmt->bind_result($total_articles);
    $total_stmt->fetch();
    $total_stmt->close();

    $total_pages = ceil($total_articles / $articles_per_page);

    // Final article fetch
    $main_query = "SELECT tbl_blogs.id, tbl_blogs.title, tbl_blogs.user_id, 
       LEFT(tbl_blogs.content, 73) AS summary, 
       tbl_blogs.datePublished, tbl_blogs.Tags, tbl_blogs.Image, 
       tbl_blogs.Category,
       Users.username
 " . $base_query . " 
                   ORDER BY $order_column $order_dir 
                   LIMIT ? OFFSET ?";

    $filter_params[] = $articles_per_page;
    $filter_params[] = $offset;
    $param_types .= "ii";

    $stmt = $conn->prepare($main_query);
    $stmt->bind_param($param_types, ...$filter_params);
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = $result->fetch_all(MYSQLI_ASSOC);

    return [
        'articles' => $articles,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'total_articles' => $total_articles,
    ];
}
