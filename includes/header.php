<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Ensure body has no margins or padding */
        body {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        /* Header */
        .global-navigation {
            position: relative; /* Ensures we can position elements like the username absolutely */
            background-color: white;
        }

        .orb-container {
            width: 73%;
            height: 4rem;
            margin: 0 auto;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: baseline; /* Ensure vertical alignment */
            line-height: 1px;
            font-size: 0.8rem;
            font-weight: 700;

        }

        .orb-nav-title {
            display: flex; /* Make sure this element aligns its content with flex */
            align-items: center; /* Vertically center the content */
            height: 4rem; /* Set the height to match the container */
        }

        .orb-nav-title img {
            height: 30px;
            width: auto;
        }

        .orbit-header-links {
            display: flex;
            justify-content: space-between; /* Centers the items horizontally */
            align-items: center; /* Ensures the items are vertically centered */
            width: 60%;
            padding: 0;
            list-style-type: none;
            font-size: 0.8rem;
            height: 4rem; /* Match the height of the container */
        }

        .orbit-header-links a {
            position: relative; /* Make sure this element can position its pseudo-element */
            padding: 0 1rem;
            text-decoration: none; /* Remove underline for links */
            color: inherit; /* Keep the color the same as the parent element */
            cursor: pointer;
        }

        .orbit-header-links a::after {
            content: '';
            position: absolute;
            bottom: 3px;
            left: 55%;
            width: 30%;
            height: 2px; /* Thickness of the border */
            background: white; /* Match the color of the text */
            transform: translateX(-50%); /* Centers the element horizontally */
            transition: width 0.3s ease-in, background-color 0.3s ease;
        }

        .header-links-img {
            height: 30px;
            margin: 1em;
            width: auto;
            background-color: white;
        }

        /* Optional: If you want to change the color of the links on hover, add this */
        .orbit-header-links li:hover a {
            color: inherit; /* Change the text color when hovering over the li */
        }

        .orb-nav-blogs:hover::after {
            background-color: blue;
            width: 65%;
            text-decoration: none; /* Ensure underline is not added on hover */
        }

        .orb-nav-articles:hover, .header-links-img:hover {
            background-color: #f4f4f4;
        }

        .orb-nav-articles:hover::after {
            background-color: red;
            width: 65%;
            text-decoration: none; /* Ensure underline is not added on hover */
        }

        .orb-nav-learn:hover::after {
            background-color: green;
            width: 65%;
            text-decoration: none; /* Ensure underline is not added on hover */
        }

        .orb-nav-search {
            width: 20rem;
            background-color: #f1f1f1;
            position: relative;
            top: 0.75rem;
        }

        #header-search-bar {
            background: #f1f1f1;
            padding-left: 1em;
            height: 2.5em;
            width: 18em;
            border: none; /* Remove border */
            outline: none; /* Remove the blue focus outline */
        }

        #header-search-bar:hover {
            border: none; /* Remove border */
            outline: none; /* Remove the blue focus outline */
        }

        .orb-nav-searchbar {
            width: 20rem;
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            color: black;
            flex-direction: row;
            align-items: flex-end;
            justify-content: flex-start;
        }

        /* Style for the button within the search bar */
        .orb-nav-searchbar button {
            background: none; /* Remove default button styling */
            border: none; /* Remove border */
            cursor: pointer; /* Add pointer cursor for interactivity */
            display: flex; /* Align content like the original */
            align-items: center; /* Vertically align content */
            padding: 0; /* Remove default padding */
            color: inherit; /* Match the color of the text */
            position: relative;
            top: -15px;
        }

        .orb-nav-searchbar button p {
            margin: 0; /* Remove margin for consistent alignment */
            padding-left: 0.5em; /* Match the original padding */
            font-weight: 700; /* Keep font weight consistent */
            font-size: 0.8rem; /* Match the font size */
        }

        .orb-nav-searchbar button:hover {
            text-decoration: none; /* Remove underline */
            color: black; /* Ensure the text color remains consistent */
        }

        .orb-nav-searchbar:hover {
            color: black;
            text-decoration: none;
            text-underline: none;
        }

        .orb-nav-searchbar img {
            left: 0;
            width: 2.5em;
            margin: 0;
            padding: 0;
        }

        .orb-nav:hover {
            color: black;
            text-decoration: none;
            text-underline: none;
        }

        /* Username display */
        .user-logged-in {
            font-size: 0.8rem;
            color: #fff;
            padding-left: 1rem;
            position: absolute; /* Position it to the top-right */
            top: 10px; /* Adjust as necessary */
            right: 10px; /* Position it on the top-right */
        }

        .account-link img {
            height: 2.5em;
        }

        .orbit-sublink {
            display: flex;
            flex-direction: row;
        }


        /* When the user is logged out, show the Sign In button in the same place */
        .orb-nav-logout {
            display: inline-block;
            margin-left: 3rem;
            font-size: 0.8rem;
        }

        .orb-nav-logout li {
            line-height: 4rem; /* Match the height of the container */
        }


        .orb-nav-logout li:hover {
            color: #007bff;
            cursor: pointer;
        }

        /* Ensure that account link only shows when logged in */
        .account-link {
            display: none;
        }

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


        /* Default hidden state */
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 90%;
            left: 0em;
            background-color: white;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            z-index: 1000;
            min-width: 100%;
            padding: 15px;
            opacity: 0;
            transition: opacity 0.3s ease-in-out, transform 0.2s ease-in-out;
        }

        /* Show the menu on hover */
        .orb-nav-articles:hover + .dropdown-menu,
        .dropdown-menu:hover {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Adjust the number of columns */
            gap: 12px;
            height: auto;
            width: 40rem;
            opacity: 1;
            transform: translateY(0);
            background-color: #f4f4f4;
            padding: 20px;
            border: 1px solid black;
        }

        .dropdown-header-3 {
            border-bottom: brown 3px solid;
            grid-column-start: 1;
            grid-column-end: 4;
            font-family: inherit;
        }

        /* Dropdown Items */
        .dropdown-menu-items {
            font-family: inherit;
            display: flex;
            height: 3rem;
            align-items: center;
            padding: 12px 18px;
            font-size: 15px;
            font-weight: 600;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease-in-out;
            justify-content: flex-start;
            min-width: 140px; /* Ensures uniform size */
            border: 1px solid red;
            background-color: white;
        }

        /* Hover Effect */
        .dropdown-menu-items:hover {
            background-color: #eaeaea;
            transform: scale(1.03);
        }

        .dropdown-menu-items img {
            width: 20px;
            margin-right: 1rem;
        }


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
                <a href="<?php echo BASE_URL; ?>layouts/pages/user/signIn_register.php" class="orb-nav orb-nav-logout">
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
                    <?php
                    $categories = [
                        "Business" => ["file" => "business.php", "image" => "public/images/header-icons/business.png"],
                        "Entertainment" => ["file" => "entertainment.php", "image" => "public/images/header-icons/entertainment.png"],
                        "Food & Drink" => ["file" => "food-and-drink.php", "image" => "public/images/header-icons/food-and-drink.png"],
                        "Health & Fitness" => ["file" => "health.php", "image" => "public/images/header-icons/healthcare.png"],
                        "History & Culture" => ["file" => "history.php", "image" => "public/images/header-icons/history.png"],
                        "Lifestyle" => ["file" => "lifestyle.php", "image" => "public/images/header-icons/lifestyles.png"],
                        "Politics" => ["file" => "politics.php", "image" => "public/images/header-icons/politics.png"],
                        "Reviews" => ["file" => "reviews.php", "image" => "public/images/header-icons/rating.png"],
                        "Science" => ["file" => "science.php", "image" => "public/images/header-icons/science.png"],
                        "Sports" => ["file" => "sports.php", "image" => "public/images/header-icons/sports.png"],
                        "Technology" => ["file" => "technology.php", "image" => "public/images/header-icons/technology.png"],
                        "Travel" => ["file" => "travel.php", "image" => "public/images/header-icons/travel.png"],
                        "Writing Craft" => ["file" => "writing-craft.php", "image" => "public/images/header-icons/writing-craft.png"]
                    ];
                    ?>

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

        <script>
            // Check if the query parameter exists
            const urlParams = new URLSearchParams(window.location.search);
            const searchQuery = urlParams.get('txt-search');

            // If the search query exists, clear the search bar after page load
            if (searchQuery) {
                window.onload = () => {
                    document.getElementById('header-search-bar').value = ''; // Clear search bar input
                };
            }
        </script>
    </div>
</nav>
</body>
</html>