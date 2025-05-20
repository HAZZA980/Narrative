<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "admin/model/overview.php";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo BASE_URL?>admin/css/admin-overview.css">
    <title>Admin Overview</title>
</head>
<body>
<?php include BASE_PATH . "account/account-masthead.php"; ?>

<div class="feed-outer-container">
    <div class="top-container">

        <!-- Insights Section -->
        <div class="insights-container">
            <div class="insight-box">
                <h2><a href="<?php echo BASE_URL; ?>admin/user-management.php">Total Users</a></h2>
                <p><?php echo $user_count; ?></p>
                <h2><a href="<?php echo BASE_URL; ?>admin/article-analysis.php">Total Articles</a></h2>
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