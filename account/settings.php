<?php
include_once "../config/config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <style>
        .settings-outer-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .settings-inner-container {
            width: 73%;
            padding: 2em;
            display: flex;
            flex-direction: row;
            background-color: #f9f9f9;
        }

        .faqs-section {
            border-left: 1px solid #ddd;
            padding-left: 20px;
            margin-left: 20px;
            max-width: 320px;
        }

        .settings-container h2 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #222;
            text-align: center;
        }

        .settings-quick-links {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-content: center;
            gap: 2rem;
            width: 70%;
            /*border: black solid 2px;*/
        }


        .settings-section {
            flex: 1;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .settings-section-title {
            font-size: 20px;
            font-weight: bold;
            color: #555;
            margin-bottom: 15px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        .settings-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .settings-list li {
            margin-bottom: 15px;
        }

        .settings-link {
            text-decoration: none;
            font-size: 18px;
            color: #007bff;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .settings-link:hover {
            background-color: #f0f8ff;
            color: #0056b3;
        }

        .settings-link.delete {
            color: #dc3545;
        }

        .settings-link.delete:hover {
            background-color: #f8d7da;
            color: #c82333;
        }
    </style>
</head>
<body>
<?php include "../layouts/mastheads/articles/account-masthead.php"; ?>

<div class="settings-outer-container">
    <div class="settings-inner-container">

        <section class="settings-quick-links">
            <div class="settings-section">
                <h3 class="settings-section-title">Account Management</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>settings/changeUsername.php"
                           class="settings-link">Change Username</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/changePassword.php"
                           class="settings-link">Change Password</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/logout.php" class="settings-link">Log
                            Out</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/deleteAccount.php"
                           class="settings-link delete">Delete Account</a></li>
                </ul>
            </div>

            <div class="settings-section">
                <h3 class="settings-section-title">Article Management</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>includes/createArticle.php" class="settings-link">Write an
                            Article</a></li>
                    <li><a href="<?php echo BASE_URL; ?>model/feed.php" class="settings-link">Edit Your Articles</a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>model/feed.php" class="settings-link">Delete an Article</a></li>
                    <li><a href="<?php echo BASE_URL; ?>layouts/pages/user/settings/recommendations.php"
                           class="settings-link">Update Your Recommended Topics</a></li>
                </ul>
            </div>
        </section>

        <section class="faqs-section">
            <div class="faqs">
                <h3 id="faqs-title">FAQs</h3>
                <ul class="settings-list">
                    <li><a href="<?php echo BASE_URL; ?>includes/createArticle.php" class="settings-link">Do I need to
                            create an account to read the blog?</a></li>
                    <li><a href="<?php echo BASE_URL; ?>model/feed.php" class="settings-link">Who is my Target
                            Audience</a></li>
                    <li><a href="<?php echo BASE_URL; ?>model/feed.php" class="settings-link">Delete an Article</a></li>
                    <li><a href="<?php echo BASE_URL; ?>settings/recommendations.php"
                           class="settings-link">Update Your Recommended Topics</a></li>
                    <li><a href="<?php echo BASE_URL; ?>layouts/pages/user/settings/faqs.php" class="settings-link">More
                            FAQs</a></li>
                </ul>
            </div>
        </section>
    </div>

</div>
</body>
</html>
