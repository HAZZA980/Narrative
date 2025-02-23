<?php

// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Initialize the search term
$search = isset($_GET['txt-search']) ? htmlspecialchars($_GET['txt-search']) : '';

// Fetch blogs based on search query
if (!empty($search)) {
    // Use prepared statements to prevent SQL injection
    $query = "
        SELECT tbl_blogs.id, tbl_blogs.title, tbl_blogs.user_id, LEFT(tbl_blogs.content, 73) AS summary, 
               tbl_blogs.datePublished, tbl_blogs.Tags, tbl_blogs.Image, Users.username 
        FROM tbl_blogs 
        LEFT JOIN Users ON tbl_blogs.user_id = Users.user_id
        WHERE (tbl_blogs.title LIKE ? 
           OR tbl_blogs.Tags LIKE ? 
           OR tbl_blogs.content LIKE ? 
           OR tbl_blogs.datePublished LIKE ?
           OR Users.username LIKE ?)  -- Added condition to count articles matching the username 
          AND tbl_blogs.private = 0
        ORDER BY tbl_blogs.datePublished DESC 
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die("Error preparing the query: " . $conn->error);
    }

    // Use the same search term for all LIKE queries
    $search_term = "%$search%";
    $stmt->bind_param("sssssii", $search_term,$search_term, $search_term, $search_term, $search_term, $articles_per_page, $offset);
    $stmt->execute();
    $blogs_result = $stmt->get_result();
} else {
    // Default query when no search term is provided
    $query = "
        SELECT tbl_blogs.id, tbl_blogs.title, LEFT(tbl_blogs.content, 74) AS summary, 
               tbl_blogs.datePublished, tbl_blogs.Tags, tbl_blogs.Image, Users.user_id, Users.username 
        FROM tbl_blogs 
        LEFT JOIN Users ON tbl_blogs.user_id = Users.user_id
        WHERE tbl_blogs.private = 0
        ORDER BY tbl_blogs.datePublished DESC 
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die("Error preparing the query: " . $conn->error);
    }

    $stmt->bind_param("ii", $articles_per_page, $offset);
    $stmt->execute();
    $blogs_result = $stmt->get_result();
}

// Count the total number of articles for pagination
$total_query = "
    SELECT COUNT(*) as total 
    FROM tbl_blogs 
    LEFT JOIN Users ON tbl_blogs.user_id = Users.user_id
    WHERE (tbl_blogs.title LIKE ? 
       OR tbl_blogs.Tags LIKE ? 
       OR tbl_blogs.content LIKE ? 
       OR tbl_blogs.datePublished LIKE ?
       OR Users.username LIKE ?)  
      AND tbl_blogs.private = 0";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("sssss", $search_term,$search_term, $search_term, $search_term, $search_term);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $articles_per_page);
?>
