<?php
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