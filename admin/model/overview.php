<?php



// Count the number of users in the database
$query = "SELECT COUNT(*) AS total_users FROM users";
$result = $conn->query($query);
$user_count = 0;

if ($result) {
    $row = $result->fetch_assoc();
    $user_count = $row['total_users'];
}

// Total number of articles
$query_articles = "SELECT COUNT(*) AS total_articles FROM tbl_blogs";
$result_articles = $conn->query($query_articles);
$total_articles = 0;
if ($result_articles) {
    $row_articles = $result_articles->fetch_assoc();
    $total_articles = $row_articles['total_articles'];
}

// Total number of likes
$query_likes = "SELECT COUNT(*) AS total_likes FROM article_likes";
$result_likes = $conn->query($query_likes);
$total_likes = 0;
if ($result_likes) {
    $row_likes = $result_likes->fetch_assoc();
    $total_likes = $row_likes['total_likes'];
}

// Total number of comments
$query_comments = "SELECT COUNT(*) AS total_comments FROM article_comments";
$result_comments = $conn->query($query_comments);
$total_comments = 0;
if ($result_comments) {
    $row_comments = $result_comments->fetch_assoc();
    $total_comments = $row_comments['total_comments'];
}

// Total number of bookmarks
$query_bookmarks = "SELECT COUNT(*) AS total_bookmarks FROM user_bookmarks";
$result_bookmarks = $conn->query($query_bookmarks);
$total_bookmarks = 0;
if ($result_bookmarks) {
    $row_bookmarks = $result_bookmarks->fetch_assoc();
    $total_bookmarks = $row_bookmarks['total_bookmarks'];
}

// Most liked article
$query_most_liked = "SELECT article_id, COUNT(*) AS like_count FROM article_likes GROUP BY article_id ORDER BY like_count DESC LIMIT 1";
$result_most_liked = $conn->query($query_most_liked);
$most_liked_article = "";
if ($result_most_liked && $result_most_liked->num_rows > 0) {
    $row_most_liked = $result_most_liked->fetch_assoc();
    $most_liked_article = $row_most_liked['article_id'];
}

// Most commented article
$query_most_commented = "SELECT article_id, COUNT(*) AS comment_count FROM article_comments GROUP BY article_id ORDER BY comment_count DESC LIMIT 1";
$result_most_commented = $conn->query($query_most_commented);
$most_commented_article = "";
if ($result_most_commented && $result_most_commented->num_rows > 0) {
    $row_most_commented = $result_most_commented->fetch_assoc();
    $most_commented_article = $row_most_commented['article_id'];
}