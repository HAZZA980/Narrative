<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .aside-writing-link {
            position: fixed;
            right: 3%;
            bottom: 20px; /* Start from the bottom right */
            width: auto;
            display: flex;
            align-items: center;
            z-index: 1000; /* Keeps it above other elements */
            height: auto;
            background: white; /* Ensure visibility */
            padding: 10px 12px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, bottom 0.3s ease-in-out;
        }

        /* Additional styles for the Write button */
        .write-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 10px;
        }

        .write-link img {
            width: 24px;
            height: 24px;
        }

        .aside-write {
            color: #333;
            font-size: 1rem;
            font-weight: bold;
            margin: 0;
        }
    </style>
</head>
<body>
<aside class="aside-writing-link">
    <a href="<?php echo BASE_URL?>user/createArticle.php" class="write-link">
        <img src="<?php echo BASE_URL?>public/images/article-layout-img/pencil-square.svg" alt="Write Icon">
        <h3 class="aside-write">Write</h3>
    </a>
</aside>
<script src="<?php echo BASE_URL?>features/write/js/fixed-write.js"></script>
</body>
</html>