<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

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
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles Summary</title>
    <style>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
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
                <th>Total Saves</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['article_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['author_username']); ?></td>
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