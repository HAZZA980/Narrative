<?php
ob_start();
include $_SERVER['DOCUMENT_ROOT']. '/phpProjects/narrative/config/config.php';
include BASE_PATH . "user/model/delete.article.php";
include BASE_PATH . "user/view/delete-article-modal.html";

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    die("User not logged in. Redirecting...");
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User ID not found in session.");
}

$order = isset($_GET['order']) ? $_GET['order'] : 'date_desc'; // Default order: Date DESC
// Map the order parameter to SQL clauses
$order_sql_map = [
    'date_asc' => 'b.datePublished ASC',
    'date_desc' => 'b.datePublished DESC',
    'chronological_asc' => 'b.id ASC',
    'chronological_desc' => 'b.id DESC'
];
// Validate the order parameter and default to 'date_desc' if invalid
$order_by = isset($order_sql_map[$order]) ? $order_sql_map[$order] : 'b.datePublished DESC';

$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'date'; // Default: order by date
$order_dir = isset($_GET['order_dir']) ? $_GET['order_dir'] : 'desc'; // Default: descending order

// Map the order_by parameter to SQL columns
$order_column_map = [
    'date' => 'b.datePublished',
    'chronological' => 'b.id', // Still orders by ID
    'alphabetical' => 'b.title' // Add this for alphabetical order
];

// Validate the order_by parameter and set default if invalid
$order_column = isset($order_column_map[$order_by]) ? $order_column_map[$order_by] : 'b.datePublished';

// Validate the order_dir parameter and set default if invalid
$order_direction = ($order_dir === 'asc') ? 'ASC' : 'DESC';

// Pagination setup
$articles_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Fetch user's articles based on their user_id
$query = "SELECT b.id, b.user_id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, b.Tags, b.Image, b.private, u.username AS Author
          FROM tbl_blogs b
          JOIN users u ON b.user_id = u.user_id
          WHERE b.user_id = ? 
          ORDER BY b.datePublished DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("iii", $user_id, $articles_per_page, $offset);
$stmt->execute();
$blogs_result = $stmt->get_result();

if (!$blogs_result) {
    die("Error executing query: " . $conn->error);
}

// Handle toggling privacy and updating saved articles
if (isset($_POST['toggle_private']) && isset($_POST['article_id'])) {
    $article_id = intval($_POST['article_id']);
    $new_private_state = intval($_POST['toggle_private']);

    // Debugging: Check values before executing the query
    echo "Article ID: $article_id, New Private State: $new_private_state";

    // Update the private state in the database
    $stmt = $conn->prepare("UPDATE tbl_blogs SET private = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $new_private_state, $article_id, $user_id);

    if ($stmt->execute()) {
        // If article becomes private, remove it from user_bookmarks if it's saved
        if ($new_private_state == 1) {
            $remove_saved_query = "DELETE FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
            $remove_stmt = $conn->prepare($remove_saved_query);
            $remove_stmt->bind_param("ii", $article_id, $user_id);
            $remove_stmt->execute();
            $remove_stmt->close();
        } else { // If article becomes public, add it back to user_bookmarks if not already saved
            $check_saved_query = "SELECT 1 FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
            $check_stmt = $conn->prepare($check_saved_query);
            $check_stmt->bind_param("ii", $article_id, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows == 0) {
                // If it's not already saved, insert it back into the bookmarks table
                $save_query = "INSERT INTO user_bookmarks (article_id, user_id) VALUES (?, ?)";
                $save_stmt = $conn->prepare($save_query);
                $save_stmt->bind_param("ii", $article_id, $user_id);
                $save_stmt->execute();
                $save_stmt->close();
            }

            $check_stmt->close();
        }

        // Redirect back to the current page to refresh
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit(); // Ensure no further code is executed
    } else {
        echo "Error updating privacy status: " . $conn->error;
    }

    $stmt->close();
}

// Get the selected tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'public_feed';

// Default query for public feed
$where_clause = "WHERE b.user_id = ?";

// Modify query based on the selected tab
if ($tab == 'public_feed') {
    $where_clause .= " AND b.private = 0";
} elseif ($tab == 'drafts') {
    $where_clause .= " AND b.private = 1";
} elseif ($tab == 'commented_articles') {
    // Use DISTINCT to avoid duplicating articles that the user has commented on multiple times
    $where_clause = "JOIN article_comments ac ON ac.article_id = b.id WHERE ac.user_id = ? GROUP BY b.id";
} elseif ($tab == 'saved_articles') {
    // Join the user_bookmarks table to get the saved articles
    $where_clause = "JOIN user_bookmarks ub ON ub.article_id = b.id WHERE ub.user_id = ?";
}

$query = "SELECT b.id, b.user_id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, b.Tags, b.Image, b.private, u.username AS Author
          FROM tbl_blogs b
          JOIN users u ON b.user_id = u.user_id
          $where_clause
          ORDER BY $order_column $order_direction
          LIMIT ? OFFSET ?";

// Prepare statement based on the tab
$stmt = $conn->prepare($query);

// Bind parameters
$stmt->bind_param("iii", $user_id, $articles_per_page, $offset);

$stmt->execute();
$blogs_result = $stmt->get_result();

// Count the total number of articles for the active tab
$total_query = "SELECT COUNT(*) as total FROM tbl_blogs b " . $where_clause;
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("i", $user_id); // Bind user_id for all tabs
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_articles = $total_row['total'] ?? 0;
$total_pages = ceil($total_articles / $articles_per_page);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Articles | Narrative</title>
    <!--    <link rel="stylesheet" href="../public/css/styles-forYou-homepage.css">-->
    <link rel="stylesheet" href="../public/css/pagination.css">
    <link rel="stylesheet" href="../public/css/articleLayouts/styles-default-article-formation.css">
    <link rel="stylesheet" href="<?php echo BASE_URL?>user/css/delete-article-modal.css">

    <style>
        .feed-outer-container {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .personal-feed-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 73%;
            background: #fff;
            padding: 0 0 20px 0;
            border-radius: 8px;
        }


        .main-content-title {
            padding: 0;
            margin-top: 2rem;
            font-weight: 600;
        }

        /* Flex container for blogs */
        .flex-container {
            margin-left: 2em;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-content: center;
            gap: 2rem;
            width: 70%;
        }

        .divider {
            border-bottom: 1px solid lightgrey;
            width: 100%;
        }


        .top-container {
            width: 73%; /* Use full width of the container */
            padding: 20px;
            display: flex;
            flex-wrap: wrap; /* Allows content to wrap on smaller screens */
            justify-content: flex-start; /* Align tabs to the left */
            align-items: center; /* Vertically center the content */
            background-color: #f9f9f9; /* Light background for better visibility */
            box-sizing: border-box;
            text-align: center;
            border-bottom: black 2px solid;
        }

        /* Place the feed tabs at the bottom */
        .feed-tabs {
            width: 73%; /* Use full width of the container */
            padding: 20px;
            display: flex;
            flex-wrap: wrap; /* Allows content to wrap on smaller screens */
            align-items: center; /* Vertically center the content */
            box-sizing: border-box;
            text-align: center;
            background-color: #ffffff;
            margin-top: auto; /* Push the tabs to the bottom */
            justify-content: center; /* Center the tabs horizontally */
        }

        /* Feed tabs styles */
        .feed-tabs-ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            width: 100%;
            display: flex;
            border: 1px solid #ccc; /* Add a border around the tabs */
            border-radius: 5px; /* Slightly rounded corners for a modern look */
            overflow: hidden; /* Ensure no elements overflow outside the container */
        }

        .feed-tabs-li {
            flex: 1; /* Make all tabs equal width */
        }

        .feed-tabs-li a {
            display: block;
            text-align: center;
            text-decoration: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            color: #555; /* Grey text for inactive tabs */
            background-color: #f0f0f0; /* Light grey background for inactive tabs */
            border-right: 1px solid #ccc; /* Divider between tabs */
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .feed-tabs-li:last-child a {
            border-right: none; /* Remove the border for the last tab */
        }

        .feed-tabs-li a.active {
            color: #000; /* Black text for active tab */
            background-color: #fff; /* White background for active tab */
        }

        .feed-tabs-li a:hover:not(.active) {
            background-color: #e0e0e0; /* Slightly darker grey for hover */
            color: #333; /* Slightly darker text color */
        }


        .top-container p {
            width: 100%;
            margin: 10px 0;
            font-size: 16px;
            color: #555;
        }


        #orderBySelect {
            padding: 5px;
            font-size: 16px;
            margin-left: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        @media (max-width: 768px) {
            .top-container {
                flex-direction: column; /* Stack content vertically on smaller screens */
                align-items: flex-start; /* Align items to the left */
            }

            .top-container a,
            .top-container div {
                width: 100%; /* Allow full-width on smaller screens */
                margin-bottom: 10px;
            }

            .top-container p {
                text-align: left; /* Align text to the left on smaller screens */
            }

            #orderBySelect {
                width: 100%; /* Full width for select dropdown */
            }
        }

        /* Overlay styling */
        .private-overlay {
            position: absolute;
            top: 50%;
            left: -3%;
            width: 110%;
            height: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.5)); /* Fade from dark to transparent */
        }

        /* Text styling inside the overlay */
        .overlay-text {
            color: white;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.5); /* Slightly darker background behind text */
            border-radius: 5px;
        }

        .flex-item {
            position: relative; /* Make the container relative so the overlay can position absolutely */
        }

        .top-container-paragraph {
            font-family: 'Arial', sans-serif; /* Use a clean, readable font */
            font-size: 1rem; /* Set the font size to ensure readability */
            line-height: 1.6; /* Add spacing between lines for better legibility */
            color: #333; /* Use a dark grey color for the text for a softer look than pure black */
            margin: 20px 0; /* Add vertical spacing for separation from other elements */
            padding: 10px 20px; /* Add padding to create space within the paragraph */
            border-radius: 5px; /* Round the corners for a softer, modern look */
        }

        .top-container-paragraph strong {
            color: #C82333; /* Highlight important warnings in red for visibility */
        }

        .pagination-container {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 20px;
            margin-top: 20px;
            width: 70%;
        }

        .filter-order-buttons {
            display: flex;
            gap: 20px;
        }

        .filter-order-buttons a img {
            width: 24px;
            height: 24px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .filter-order-buttons a img:hover {
            transform: scale(1.1);
        }

        .pagination {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .pagination-link {
            text-decoration: none;
            color: #007bff;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .pagination-link:hover {
            background-color: #f1f1f1;
        }

        .pagination-link.current {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            pointer-events: none;
        }

        .pagination-link.prev-button,
        .pagination-link.next-button {
            font-weight: bold;
        }

        .dots {
            display: inline-block;
            padding: 5px 10px;
            color: #888;
        }


        /*    DROP DOWN MENU*/
        .order-dropdown {
            position: relative;
            display: inline-block;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;  /* Modern, clean font */
        }

        .order-dropdown .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            transition: all 0.3s ease; /* Smooth transition for hover */
        }

        /* Dropdown links */
        .order-dropdown .dropdown-content a {
            color: #333333;  /* Dark grey color for better readability */
            padding: 10px 16px;
            text-decoration: none;
            display: block;
            font-size: 14px;  /* Moderate font size */
            font-weight: 500; /* Slightly bolder font weight */
            border-radius: 4px; /* Rounded edges for each item */
            transition: background-color 0.3s ease; /* Smooth background transition */
        }

        .order-dropdown .dropdown-content a:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        .order-dropdown:hover .dropdown-content {
            display: block;
        }


        /*    FILTER OVERLAY*/
        /* Filter Overlay Styles */
        /*.filter-overlay {*/
        /*    position: fixed;*/
        /*    top: 0;*/
        /*    left: 0;*/
        /*    width: 100%;*/
        /*    height: 100%;*/
        /*    background-color: rgba(0, 0, 0, 0.5); !* Semi-transparent background *!*/
        /*    display: none; !* Hidden by default *!*/
        /*    justify-content: center;*/
        /*    align-items: center;*/
        /*    z-index: 9999;*/
        /*}*/

        /*.filter-content {*/
        /*    background-color: white;*/
        /*    padding: 20px;*/
        /*    border-radius: 8px;*/
        /*    width: 50%;*/
        /*    max-width: 500px;*/
        /*    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);*/
        /*}*/

        /*.filter-category label,*/
        /*.filter-date label {*/
        /*    display: block;*/
        /*    margin-bottom: 8px;*/
        /*}*/

        /*.filter-category input[type="checkbox"] {*/
        /*    margin-right: 10px;*/
        /*}*/

        /*.apply-filters, .close-filter {*/
        /*    margin-top: 20px;*/
        /*    padding: 10px 20px;*/
        /*    cursor: pointer;*/
        /*}*/

        /*.close-filter {*/
        /*    background-color: #f44336;*/
        /*    color: white;*/
        /*}*/

        /*.apply-filters {*/
        /*    background-color: #4CAF50;*/
        /*    color: white;*/
        /*}*/
    </style>
    <!--    <script src="--><?php //echo BASE_PATH; ?><!--public/js/save-page-position.js"></script>-->
</head>
<body>
<?php include "../layouts/mastheads/articles/account-masthead.php"; ?>


<div class="feed-outer-container">
    <div class="top-container">
        <p class="top-container-paragraph">Welcome to your personal feed! This is your central hub for managing all your
            published and unpublished
            articles. Organise, edit, and view your works based on different criteria with ease.

            <br><br> Use the options menu (three dots above each article) to make quick updates, toggle privacy
            settings, or delete articles. Whether you're working on drafts or showcasing your published masterpieces,
            this feed is tailored to help you stay in control of your content.

            <br><br> <strong>Note:</strong> Deleting an article will permanently remove it from the server. This action
            is irreversible, so proceed with caution. Start creating, sharing, and curating your stories today!</p>
    </div>

    <div class="feed-tabs" id="feed-tabs">
        <ul class="feed-tabs-ul">
            <li class="feed-tabs-li"><a href="?tab=public_feed" class="<?= $tab == 'public_feed' ? 'active' : '' ?>">Public
                    Feed</a></li>
            <li class="feed-tabs-li"><a href="?tab=drafts" class="<?= $tab == 'drafts' ? 'active' : '' ?>">Drafts</a>
            </li>
            <li class="feed-tabs-li"><a href="?tab=commented_articles"
                                        class="<?= $tab == 'commented_articles' ? 'active' : '' ?>">Commented
                    Articles</a></li>
            <li class="feed-tabs-li"><a href="?tab=saved_articles"
                                        class="<?= $tab == 'saved_articles' ? 'active' : '' ?>">Saved
                    Articles</a></li>
        </ul>
    </div>

    <div class="pagination-container">
        <!-- Filter and Order Buttons -->
        <div class="filter-order-buttons">
            <!-- Order Button -->
            <div class="order-dropdown">
                <a href="#"><img src="<?php echo BASE_URL; ?>public/images/pagination/order.svg" alt="Order"
                                 title="Order"></a>
                <div class="dropdown-content">
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=date">Order By Date</a>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=chronological">Order By ID</a>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=alphabetical">Order By Alphabetical</a>
                </div>
            </div>


            <!-- Filter Overlay (Hidden initially) -->
<!--            <div id="filter-overlay" class="filter-overlay">-->
<!--                <div class="filter-content">-->
<!--                    <h2>Filter Articles</h2>-->
<!---->
<!--                     Category Filter -->
<!--                    <div class="filter-category">-->
<!--                        <h3>Categories</h3>-->
<!--                        <form id="category-form">-->
<!--                            <label><input type="checkbox" name="categories[]" value="Actors"> Actors</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Big Tech"> Big Tech</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Book Reviews"> Book Reviews</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Computer Networks"> Computer-->
<!--                                Networks</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Erasmus Year"> Erasmus Year</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Film & Cinema"> Film &-->
<!--                                Cinema</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Greek Mythology"> Greek-->
<!--                                Mythology</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Modern History"> Modern-->
<!--                                History</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Living & Learning"> Living &-->
<!--                                Learning</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Network Security"> Network Security</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Plays"> Play Reviews</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Shakespeare Plays"> Shakespeare-->
<!--                                Plays</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Student Life"> Student Life</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Travel"> Travel</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="European Politics"> European-->
<!--                                Politics</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="US Politics"> US-->
<!--                                Politics</label><br>-->
<!--                            <label><input type="checkbox" name="categories[]" value="Writing Craft"> Writing-->
<!--                                Craft</label><br>-->
<!--                        </form>-->
<!--                    </div>-->
<!---->
<!--                        <div class="filter-date">-->
<!--                        <h3>Date Range</h3>-->
<!--                        <label for="from-date">From: </label>-->
<!--                        <input type="date" id="from-date" name="from-date"><br><br>-->
<!--                        <label for="to-date">To: </label>-->
<!--                        <input type="date" id="to-date" name="to-date"><br><br>-->
<!--                    </div>-->
<!---->
<!--                    <button id="apply-filters" class="apply-filters">Apply Filters</button>-->
<!--                    <button id="close-filter" class="close-filter">Close</button>-->
<!--                </div>-->
<!--            </div>-->

            <!-- Filter Button -->
            <a href="#" style="display: none"><img src="<?php echo BASE_URL; ?>public/images/pagination/filter.svg" alt="Filter"
                             title="Filter"></a>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <!-- Previous Button -->
                <?php if ($current_page > 1): ?>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $current_page - 1; ?>"
                       class="pagination-link prev-button"
                       aria-label="Previous Page">
                        Previous
                    </a>
                <?php endif; ?>

                <!-- First Page -->
                <?php if ($current_page > 5): ?>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=1"
                       class="pagination-link"
                       aria-label="Page 1">
                        1
                    </a>
                    <span class="dots">...</span>
                <?php endif; ?>

                <!-- Middle Pages -->
                <?php
                $start_page = max(1, $current_page - 4);
                $end_page = min($total_pages, $current_page + 4);
                for ($page = $start_page; $page <= $end_page; $page++): ?>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $page; ?>"
                       class="pagination-link <?php echo $current_page == $page ? 'current' : ''; ?>"
                       aria-label="Page <?php echo $page; ?>">
                        <?php echo $page; ?>
                    </a>
                <?php endfor; ?>

                <!-- Last Page -->
                <?php if ($current_page < $total_pages - 4): ?>
                    <span class="dots">...</span>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $total_pages; ?>"
                       class="pagination-link"
                       aria-label="Page <?php echo $total_pages; ?>">
                        <?php echo $total_pages; ?>
                    </a>
                <?php endif; ?>

                <!-- Next Button -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $current_page + 1; ?>"
                       class="pagination-link next-button"
                       aria-label="Next Page">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="asc-desc">
            <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=<?php echo htmlspecialchars($order_by); ?>&order_dir=asc">
                <img src="<?php echo BASE_URL; ?>public/images/pagination/arrow-up.svg" alt="Ascending"
                     title="Ascending">
            </a>
            <a href="?tab=<?php echo htmlspecialchars($tab); ?>&order_by=<?php echo htmlspecialchars($order_by); ?>&order_dir=desc">
                <img src="<?php echo BASE_URL; ?>public/images/pagination/arrow-down.svg" alt="Descending"
                     title="Descending">
            </a>
        </div>
    </div>

    <div class="personal-feed-container">
        <div class="flex-container" id="flex-container">
<!--                        <h1 class="main-content-title">Your Articles</h1>-->
            <?php if ($blogs_result->num_rows > 0): ?>
                <?php while ($row = $blogs_result->fetch_assoc()): ?>
                    <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                        <!-- User is the author of the article -->
                        <div class="flex-item">
                            <?php if ($row['private'] == 1): ?>
                                <div class="private-overlay">
                                    <p>This blog is set to private</p>
                                </div>
                            <?php endif; ?>
                            <div class="article-author-and-topic">
                                <div class="inter">
                                    <span class="aa" id="writing-about">You are writing about </span>
                                    <span class="aa" id="blog-tags"><?php echo htmlspecialchars($row['Tags']); ?></span>
                                </div>
                                <!-- Edit Article Icon and Dropdown Menu -->
                                <div class="edit-article">
                                    <img src="../public/images/article-layout-img/three-dots.svg" alt="Edit Menu"
                                         class="edit-menu-icon">
                                    <div class="edit-menu">
                                        <ul>
                                            <li>
                                                <a href="<?php echo BASE_URL?>user/edit-article.php?id=<?php echo $row['id']; ?>"
                                                   class="edit-article-option">Edit Article</a>
                                            </li>
                                            <li class="admin-action-item">
                                                <form action="" method="POST" style="display: inline;">
                                                    <input type="hidden" name="article_id"
                                                           value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="toggle_private"
                                                            value="<?php echo $row['private'] == 1 ? 0 : 1; ?>"
                                                            class="edit-article-option">
                                                        <?php echo $row['private'] == 1 ? 'Make Public' : 'Make Private'; ?>
                                                    </button>
                                                </form>
                                            </li>

                                            <li class="admin-action-item">
                                                <a href="javascript:void(0);" class="admin-action-link" id="deleteLink"
                                                   data-article-id="<?php echo $row['id']; ?>">Delete Article</a>
                                            </li>
                                            <?php
//                                            include BASE_PATH . "layouts/pages/user/delete-article-modal.html";
//                                            include BASE_PATH . "layouts/pages/user/delete-modal-js.php";
                                            ?>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                            <a href="<?php echo BASE_URL; ?>user/article.php?id=<?php echo $row['id']; ?>"
                               class="article-main-link">
                                <div class="blog-body">
                                    <div class="blog-details">
                                        <h2 id="blog-title"><?php echo htmlspecialchars($row['title']);?></h2>
                                        <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                                    </div>
                                    <div class="image-container">
                                        <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                            ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                            : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                                    </div>
                                </div>
                            </a>

                            <div class="blog-details-2">
                                <p id="blog-date">
                                    <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                                </p>
                                <?php
                                // Get the number of comments for this article
                                $article_id = $row['id']; // Get article ID
                                $comment_query = "SELECT COUNT(*) AS comment_count FROM article_comments WHERE article_id = ?";
                                $comment_stmt = $conn->prepare($comment_query);
                                $comment_stmt->bind_param("i", $article_id);
                                $comment_stmt->execute();
                                $comment_stmt->bind_result($comment_count);
                                $comment_stmt->fetch();
                                $comment_stmt->close();
                                ?>
                                <div class="likes-and-comments" data-article-id="<?php echo $row['id']; ?>">
                                    <div class="like">
                                        <?php
                                        // Get the current user's ID
                                        $user_id = $_SESSION['user_id']; // Or however you are retrieving the user_id from the session

                                        // Assuming you're inside the loop for displaying each article
                                        $article_id = $row['id']; // Article ID for the current post

                                        // Check if the user has already liked the article
                                        $query = "SELECT * FROM article_likes WHERE article_id = ? AND user_id = ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $article_id, $user_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        // Check if there is a like record for this article and user
                                        $article_liked = $result->num_rows > 0 ? true : false;
                                        ?>
                                        <!-- Like button with form -->
                                        <form action="<?php echo BASE_URL; ?>layouts/pages/articles/like.php"
                                              method="POST" class="like-form">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <!-- Show filled icon if the article is liked -->
                                            <button type="submit" class="like-btn" name="bookmark_action"
                                                    value="<?php echo $article_liked ? 'remove' : 'add'; ?>">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-regular.svg"
                                                     alt="Add Like" class="like-icon"
                                                     style="display: <?php echo $article_liked ? 'none' : 'block'; ?>"/>
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-solid.svg"
                                                     alt="Remove Like" class="like-icon"
                                                     style="display: <?php echo $article_liked ? 'block' : 'none'; ?>"/>
                                            </button>
                                        </form>
                                        <?php
                                        // Query to get the number of likes for the current article
                                        $like_count_query = "SELECT COUNT(*) AS like_count FROM article_likes WHERE article_id = ?";
                                        $stmt = $conn->prepare($like_count_query);
                                        $stmt->bind_param("i", $article_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $like_count = $result->fetch_assoc()['like_count']; // Fetch the count of likes
                                        ?>
                                        <p class="like-status"><?php echo $like_count; ?></p>
                                    </div>


                                    <div class="comment">
                                        <!--Comments Backend-->
                                        <?php
                                        // Get the number of comments for this article
                                        $article_id = $row['id']; // Get article ID
                                        $comment_query = "SELECT COUNT(*) AS comment_count FROM article_comments WHERE article_id = ?";
                                        $comment_stmt = $conn->prepare($comment_query);
                                        $comment_stmt->bind_param("i", $article_id);
                                        $comment_stmt->execute();
                                        $comment_stmt->bind_result($comment_count);
                                        $comment_stmt->fetch();
                                        $comment_stmt->close();
                                        ?>
                                        <a href="<?php echo BASE_URL;?>user/article.php?id=<?php echo $row['id']?>" class="comments-link">
                                        <img src="<?php echo BASE_URL ?>public/images/article-layout-img/comments-regular.svg"
                                             alt="Comments">
                                        <p class="comments-count"><?php echo $comment_count; ?></p>
                                        </a>
                                    </div>


                                    <?php
                                    // Check if the article is already bookmarked
                                    $check_query = "SELECT * FROM user_bookmarks WHERE user_id = ? AND article_id = ?";
                                    $stmt = $conn->prepare($check_query);
                                    $stmt->bind_param("ii", $user_id, $article_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $article_bookmarked = $result->num_rows > 0;
                                    ?>

                                    <div class="bookmark">
                                        <!-- Bookmark button with form -->
                                        <form action="<?php echo BASE_URL; ?>layouts/pages/articles/bookmark.php"
                                              method="POST" class="bookmark-form">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <!-- Show filled icon if the article is bookmarked -->
                                            <button type="submit" class="bookmark-btn" name="bookmark_action"
                                                    value="<?php echo $article_bookmarked ? 'remove' : 'add'; ?>">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus.svg"
                                                     alt="Add to Bookmarks" class="bookmark-icon"
                                                     style="display: <?php echo $article_bookmarked ? 'none' : 'block'; ?>"/>
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus-fill.svg"
                                                     alt="Remove from Bookmarks" class="bookmark-icon"
                                                     style="display: <?php echo $article_bookmarked ? 'block' : 'none'; ?>"/>
                                            </button>
                                        </form>
                                        <p class="bookmark-status"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="divider"></div>

                    <?php else: ?>
                        <!-- User is NOT the author of the article -->
                        <div class="flex-item">
                            <div class="article-author-and-topic">
                                <span class="aa" id="author-name"><?php echo htmlspecialchars($row['Author']) ?></span>
                                <span class="aa" id="writing-about">is writing about</span>
                                <span class="aa" id="blog-tags"><?php echo htmlspecialchars($row['Tags']); ?></span>
                            </div>

                            <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>"
                               class="article-main-link">
                                <div class="blog-body">
                                    <div class="blog-details">
                                        <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                                        <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                                    </div>
                                    <div class="image-container">
                                        <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                            ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                            : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                                    </div>
                                </div>
                            </a>

                            <div class="blog-details-2">
                                <p id="blog-date">
                                    <small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                                </p>
                                <div class="likes-and-comments" data-article-id="<?php echo $row['id']; ?>">
                                    <div class="like">
                                        <?php
                                        // Get the current user's ID
                                        $user_id = $_SESSION['user_id']; // Or however you are retrieving the user_id from the session

                                        // Assuming you're inside the loop for displaying each article
                                        $article_id = $row['id']; // Article ID for the current post

                                        // Check if the user has already liked the article
                                        $query = "SELECT * FROM article_likes WHERE article_id = ? AND user_id = ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $article_id, $user_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        // Check if there is a like record for this article and user
                                        $article_liked = $result->num_rows > 0 ? true : false;
                                        ?>
                                        <!-- Like button with form -->
                                        <form action="<?php echo BASE_URL ?>/layouts/pages/articles/like.php"
                                              method="POST" class="like-form">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <!-- Show filled icon if the article is liked -->
                                            <button type="submit" class="like-btn" name="bookmark_action"
                                                    value="<?php echo $article_liked ? 'remove' : 'add'; ?>">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-regular.svg"
                                                     alt="Add Like" class="like-icon"
                                                     style="display: <?php echo $article_liked ? 'none' : 'block'; ?>"/>
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/heart-solid.svg"
                                                     alt="Remove Like" class="like-icon"
                                                     style="display: <?php echo $article_liked ? 'block' : 'none'; ?>"/>
                                            </button>
                                        </form>
                                        <?php
                                        // Query to get the number of likes for the current article
                                        $like_count_query = "SELECT COUNT(*) AS like_count FROM article_likes WHERE article_id = ?";
                                        $stmt = $conn->prepare($like_count_query);
                                        $stmt->bind_param("i", $article_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $like_count = $result->fetch_assoc()['like_count']; // Fetch the count of likes
                                        ?>
                                        <p class="like-status"><?php echo $like_count; ?></p>
                                    </div>


                                    <div class="comment">
                                        <!--Comments Backend-->
                                        <?php
                                        // Get the number of comments for this article
                                        $article_id = $row['id']; // Get article ID
                                        $comment_query = "SELECT COUNT(*) AS comment_count FROM article_comments WHERE article_id = ?";
                                        $comment_stmt = $conn->prepare($comment_query);
                                        $comment_stmt->bind_param("i", $article_id);
                                        $comment_stmt->execute();
                                        $comment_stmt->bind_result($comment_count);
                                        $comment_stmt->fetch();
                                        $comment_stmt->close();
                                        ?>
                                        <a href="<?php echo BASE_URL;?>user/article.php?id=<?php echo $row['id']?>" class="comments-link">
                                            <img src="<?php echo BASE_URL ?>public/images/article-layout-img/comments-regular.svg"
                                                 alt="Comments">
                                            <p class="comments-count"><?php echo $comment_count; ?></p>
                                        </a>
                                    </div>


                                    <?php
                                    // Check if the article is already bookmarked
                                    $check_query = "SELECT * FROM user_bookmarks WHERE user_id = ? AND article_id = ?";
                                    $stmt = $conn->prepare($check_query);
                                    $stmt->bind_param("ii", $user_id, $article_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $article_bookmarked = $result->num_rows > 0;
                                    ?>

                                    <div class="bookmark">
                                        <!-- Bookmark button with form -->
                                        <form action="<?php echo BASE_URL ?>/layouts/pages/articles/bookmark.php"
                                              method="POST" class="bookmark-form">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                            <!-- Show filled icon if the article is bookmarked -->
                                            <button type="submit" class="bookmark-btn" name="bookmark_action"
                                                    value="<?php echo $article_bookmarked ? 'remove' : 'add'; ?>">
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus.svg"
                                                     alt="Add to Bookmarks" class="bookmark-icon"
                                                     style="display: <?php echo $article_bookmarked ? 'none' : 'block'; ?>"/>
                                                <img src="<?php echo BASE_URL ?>public/images/article-layout-img/file-earmark-plus-fill.svg"
                                                     alt="Remove from Bookmarks" class="bookmark-icon"
                                                     style="display: <?php echo $article_bookmarked ? 'block' : 'none'; ?>"/>
                                            </button>
                                        </form>
                                        <p class="bookmark-status"></p>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <div class="divider"></div>
                    <?php endif; ?>

                <?php endwhile; ?>
                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($total_pages > 1): ?>
                        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                            <a href="?tab=<?php echo htmlspecialchars($tab); ?>&page=<?php echo $page; ?>"
                               class="pagination-link <?php echo $current_page == $page ? 'current' : ''; ?>"
                               aria-label="Page <?php echo $page; ?>">
                                <?php echo $page; ?>
                            </a>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No articles found.</p>
            <?php endif; ?>


        </div>
    </div>
</div>
<script>
    var BASE_URL = "<?php echo BASE_URL; ?>";
</script>
<script src="<?php echo BASE_URL?>user/js/delete-article.js"></script>
<script src="<?php echo BASE_URL?>public/js/save-page-position.js"></script>

</body>
</html>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Add event listeners for dropdown toggling
        const editIcons = document.querySelectorAll('.edit-menu-icon');
        const menus = document.querySelectorAll('.edit-menu');

        // Show the menu when the three dots are clicked
        editIcons.forEach((icon, index) => {
            const menu = menus[index];

            icon.addEventListener('click', (event) => {
                event.stopPropagation(); // Prevent click from closing menu immediately
                // Toggle the visibility of the menu
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Close menu when clicking outside of the menu
        document.addEventListener('click', () => {
            menus.forEach(menu => {
                menu.style.display = 'none';
            });
        });

        // Prevent click on menu from closing it immediately
        menus.forEach(menu => {
            menu.addEventListener('click', (event) => {
                event.stopPropagation();
            });
        });
    });
</script>
<script>
    // When the page loads or reloads, scroll to the flex-container
    window.onload = function () {
        const container = document.getElementById('feed-tabs');
        if (container) {
            container.scrollIntoView({behavior: 'smooth'});
        }
    };
</script>
<script>
    // JavaScript to handle filter button click and overlay visibility
    document.getElementById('filter-button').addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('filter-overlay').style.display = 'flex';
    });

    // Close filter overlay when clicking close button
    document.getElementById('close-filter').addEventListener('click', function () {
        document.getElementById('filter-overlay').style.display = 'none';
    });

    // Apply the selected filters
    document.getElementById('apply-filters').addEventListener('click', function () {
        const selectedCategories = [];
        const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]:checked');
        categoryCheckboxes.forEach(function (checkbox) {
            selectedCategories.push(checkbox.value);
        });

        const fromDate = document.getElementById('from-date').value;
        const toDate = document.getElementById('to-date').value;

        // Create the filter query string
        let filterQuery = '';
        if (selectedCategories.length > 0) {
            filterQuery += `&categories=${selectedCategories.join(',')}`;
        }
        if (fromDate) {
            filterQuery += `&from_date=${fromDate}`;
        }
        if (toDate) {
            filterQuery += `&to_date=${toDate}`;
        }

        // Redirect with the new filters
        window.location.href = window.location.pathname + '?tab=<?php echo htmlspecialchars($tab); ?>' + filterQuery;
    });
</script>
