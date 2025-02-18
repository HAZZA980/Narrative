<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';


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
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Overview</title>
    <style>
        /* Keep the existing outer feed and top container styles */
        .feed-outer-container {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .top-container {
            width: 73%;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: flex-start;
            background-color: #e9ecef; /* Tertiary Background */
            border-bottom: 2px solid #dee2e6;
            box-sizing: border-box;
            text-align: center;
            border-bottom: 2px solid #333;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Insights container styles */
        .insights-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px;
            background-color: #f4f5f7; /* Primary Background */
            border: 1px solid #dee2e6; /* Accent Background as a border */
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Softer shadow */
            margin-right: 20px;
            max-width: 45%; /* Adjust to ensure responsiveness */
        }

        .insight-box h2 {
            font-size: 1.2rem;
            color: #444; /* Neutral dark grey for headers */
            margin: 10px 0;
        }

        .insight-box p {
            font-size: 1.5rem;
            color: #2a9d8f; /* Professional teal for key stats */
            font-weight: bold;
            margin: 5px 0 15px;
        }

        /* Links container styles */
        .links-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 20px;
            background-color: #ffffff; /* Crisp white background */
            border: 1px solid #ccc; /* Match with insights container */
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .link-box {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px;
            background-color: #264653; /* Dark teal for buttons */
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .link-box a {
            text-decoration: none;
            color: #ffffff; /* White text for contrast */
            font-size: 1.1rem;
            font-weight: bold;
        }

        .link-box:hover {
            background-color: #1b3a4b; /* Slightly darker teal on hover */
            transform: scale(1.02); /* Subtle hover effect */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .top-container {
                flex-direction: column;
                align-items: center;
            }

            .insights-container, .links-container {
                max-width: 100%;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
<?php include "../layouts/mastheads/articles/account-masthead.php"; ?>

<div class="feed-outer-container">
    <div class="top-container">

        <!-- Insights Section -->
        <div class="insights-container">
            <div class="insight-box">
                <h2>Total Users</h2>
                <p><?php echo $user_count; ?></p>
                <h2>Total Articles</h2>
                <p><?php echo $total_articles; ?></p>
                <h2>Total Likes</h2>
                <p><?php echo $total_likes; ?></p>
                <h2>Total Comments</h2>
                <p><?php echo $total_comments; ?></p>
                <h2>Total Bookmarks</h2>
                <p><?php echo $total_bookmarks; ?></p>
            </div>
        </div>

        <!-- Links Section -->
        <div class="links-container">
            <div class="link-box">
                <a href="<?php echo BASE_URL; ?>admin/article-analysis.php">View Article Analysis</a>
            </div>
            <div class="link-box">
                <a href="<?php echo BASE_URL; ?>admin/user-management.php">View Users Details</a>
            </div>
        </div>

    </div>
</div>
</body>
</html>