<?php
// Query to fetch the required data
$query = "
    SELECT 
        b.id AS article_id, 
        b.title AS article_title, 
        u.username AS author_username,
        COUNT(DISTINCT ac.id) AS total_comments,
        COUNT(DISTINCT al.id) AS total_likes,
        COUNT(DISTINCT ub.id) AS total_bookmarks
    FROM tbl_blogs b
    LEFT JOIN users u ON b.user_id = u.user_id
    LEFT JOIN article_comments ac ON b.id = ac.article_id
    LEFT JOIN article_likes al ON b.id = al.article_id
    LEFT JOIN user_bookmarks ub ON b.id = ub.article_id
    GROUP BY b.id, b.title, u.username
    ORDER BY total_comments DESC, total_likes DESC, total_bookmarks DESC
";

$result = $conn->query($query);

if (!$result) {
    die("Error fetching articles summary: " . $conn->error);
}


?>