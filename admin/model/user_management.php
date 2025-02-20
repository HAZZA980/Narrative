<?php

// Count the number of users in the database
$query = "SELECT COUNT(*) AS total_users FROM users";
$result = $conn->query($query);
$user_count = 0;

if ($result) {
    $row = $result->fetch_assoc();
    $user_count = $row['total_users'];
}

// Fetch user data with related article information
$query = "
    SELECT 
        users.user_id, 
        users.username, 
        users.created_at AS user_since, 
        COUNT(tbl_blogs.Id) AS article_count,
        MAX(tbl_blogs.LastUpdated) AS last_updated_article
    FROM 
        users
    LEFT JOIN 
        tbl_blogs ON users.user_id = tbl_blogs.user_id
    GROUP BY 
        users.user_id, users.username, users.created_at
    ORDER BY 
        users.created_at ASC
";
$result = $conn->query($query);