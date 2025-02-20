<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "admin/model/user_management.php";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Overview</title>
    <style>
        .feed-outer-container {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .top-container {
            width: 73%;
            height: 40rem;
            border: ;
        }

        .admin-insights {
            margin: 2rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .admin-insights p {
            margin: 0.5rem 0;
        }

        .admin-insights a {
            text-decoration: none;
            color: #0056b3;
        }

        .admin-insights a:hover {
            text-decoration: underline;
        }

        h2 {
            text-align: center;
            color: #212529;
            margin-top: 20px;
            margin-bottom: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .users-table-container {
            width: 100%;
            margin: auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.95rem;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        th {
            background-color: #0056b3;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        td {
            color: #495057;
        }

        td:last-child, td:nth-last-child(2) {
            text-align: center;
        }

        .freeze-btn, .delete-btn {
            padding: 6px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .freeze-btn {
            background-color: #f39c12;
            color: #ffffff;
        }

        .freeze-btn:hover {
            background-color: #e67e22;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: #ffffff;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        @media (max-width: 768px) {
            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
<?php include BASE_PATH . "layouts/mastheads/articles/account-masthead.php"; ?>
<div class="feed-outer-container">
    <div class="top-container">
        <h2>Admin: User Management</h2>
        <div class="users-table-container">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>No. of Articles Written</th>
                    <th>Date of Last Updated Article</th>
                    <th>User Since</th>
                    <th>Freeze Account</th>
                    <th>Delete Account</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo $row['article_count']; ?></td>
                            <td><?php echo $row['last_updated_article'] ? htmlspecialchars($row['last_updated_article']) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($row['user_since']); ?></td>
                            <td>
                                <form action="freeze-account.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                    <button type="submit" class="freeze-btn">Freeze</button>
                                </form>
                            </td>
                            <td>
                                <form action="<?php echo BASE_URL ?>layouts/pages/user/settings/" method="POST"
                                      style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6'>No users found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
</body>
</html>