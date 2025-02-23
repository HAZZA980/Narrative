<?php
ob_start();
//Add this because I was having trouble redirecting from change password back to settings

include BASE_PATH . 'includes/model/header_file_mapping.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>includes/css/header.css">
    <style>
        /* When the user is logged in, show the account link */
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
        .account-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .account-link {
            display: inline-block;
            width: 10rem;
        }

        .header-account-name {
            position: absolute;
            top: 50%;
            width: 100%;
            left: 40%;
        }

        /* Dropdown menu styling */
        .account-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 4.5em;
            background: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 1000;
            min-width: 120px;
        }

        .account-dropdown a {
            display: block;
            padding: 10px;
            color: black;
            text-decoration: none;
        }

        .account-dropdown a:hover {
            background-color: #f8f9fa;
        }

        /* Show dropdown on hover */
        .account-container:hover .account-dropdown {
            display: block;
        }
        <?php endif; ?>
    </style>
</head>
<body>

<nav class="global-navigation">
    <div class="orb-container">
        <div class="orb-nav-title">
            <a href="<?php echo isset($_SESSION['logged_in']) && $_SESSION['logged_in'] ? BASE_URL . 'forYou.php' : BASE_URL . 'home.php'; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/header-img/narrative-logo-small.png" alt="Logo">
            </a>
        </div>

        <ul class="orbit-header-links">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <div class="account-container">
                    <a class="account-link" href="<?php echo BASE_URL; ?>account.php">
                        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <img src="<?php echo BASE_URL; ?>public/images/header-img/profile-icon.webp" alt="account-icon"
                             id="account-icon">
                        <span class="header-account-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </a>
                    <div class="account-dropdown">
                        <a href="<?php echo BASE_URL; ?>settings/logout.php">Log Out</a>
                    </div>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>user_auth.php" class="orb-nav orb-nav-logout">
                    <li class="header-account-name">Sign In</li>
                </a>
            <?php endif; ?>

            <div class="orbit-sublink">
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <a href="<?php echo BASE_URL; ?>forYou.php" class="orb-nav orb-nav-blogs">
                        <li>
                            <img class="header-links-img"
                                 src="<?php echo BASE_URL; ?>public/images/header-img/quill.jpeg" alt="">For You
                        </li>
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>explore/home.php" class="orb-nav orb-nav-articles">
                    <li>
                        <img class="header-links-img" src="<?php echo BASE_URL; ?>public/images/header-img/article.png" alt="">Explore
                    </li>
                </a>

                <div class="dropdown-menu">
                    <h3 class="dropdown-header-3">Categories</h3>
                    <?php foreach ($categories as $category => $data): ?>
                        <div>
                        <a href="<?php echo BASE_URL; ?>explore/<?php echo $data['file']; ?>"
                           class="dropdown-menu-items">
                            <img src="<?php echo BASE_URL . $data['image']; ?>" alt="<?php echo htmlspecialchars($category); ?>" class="category-icon">
                            <?php echo htmlspecialchars($category); ?>
                        </a>
                        </div>
                    <?php endforeach; ?>
                </div>



                <a href="<?php echo BASE_URL; ?>layouts/pages/quizzes/quizzes-home.php" class="orb-nav orb-nav-learn">
                    <li>
                        <img class="header-links-img"
                             src="<?php echo BASE_URL; ?>public/images/header-img/lightbulb.png" alt="">Quizzes
                    </li>
                </a>
            </div>
        </ul

        <div class="orb-nav-search">
            <form action="<?php echo BASE_URL;?>search.php" method="get" class="orb-nav-searchbar"
                  id="searchForm">
                <div><img id="search-img" src="<?php echo BASE_URL; ?>public/images/header-img/search.png" alt="Search">
                </div>
                <input
                        type="text"
                        name="txt-search"
                        id="header-search-bar"
                        placeholder="Search for a Post"
                        autocomplete="off"
                        value="<?php echo isset($_GET['txt-search']) ? htmlspecialchars($_GET['txt-search']) : ''; ?>"
                >
                <button type="submit" id="search">
                    <p>Search</p>
                </button>
            </form>
        </div>

    </div>
</nav>
<script src="<?php echo BASE_URL; ?>includes/js/searchQueryChecker.js"></script>
</body>
</html>