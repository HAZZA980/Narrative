<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'features/write/write-icon-fixed.php';
include BASE_PATH . 'account/account-masthead.php';

include BASE_PATH . "admin/model/article_analysis.php";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_URL;?>admin/css/article_analysis.css">
    <title>Articles Summary</title>
</head>
<body>
<?php include BASE_PATH . "layouts/mastheads/articles/account-masthead.php"; ?>

<div class="feed-outer-container">
    <div class="top-container">
        <h1>Articles Summary</h1>
        <table>
            <thead>
            <tr>
                <th>Article Title</th>
                <th>Author</th>
                <th>Total Comments</th>
                <th>Total Likes</th>
                <th>No. of Bookmarks</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['article_id']; ?>">
                            <?php echo htmlspecialchars($row['article_title']); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo BASE_URL?>feed.php?username=<?php echo urlencode($row['author_username']); ?>">
                        <?php echo htmlspecialchars($row['author_username']); ?>
                        </a>
                    </td>

                    <td><?php echo htmlspecialchars($row['total_comments']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_likes']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_bookmarks']); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>