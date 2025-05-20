<?php

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Get logged-in user's ID if available


// Get the tag from the URL (if set)
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : null;
// Ensure that the tag is provided in the URL, else exit
if (!$tag) {
    die("Tag not provided.");
}

// Pagination setup
$articles_per_page = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Add wildcards to search for the tag within the comma-separated list
$tagParam = '%' . $tag . '%';

// Debugging: Output the tagParam to ensure it's correct
//echo "Tag Parameter: " . htmlspecialchars($tagParam) . "<br>";



///----------------------------------------------------------------------------Sorting out the ordering of the articles
// Prepare the query
$query = "SELECT b.id, b.user_id, b.title, LEFT(b.content, 100) AS summary, b.datePublished, 
       b.Category, b.Tags, b.Image, u.username AS Author 
FROM tbl_blogs b
JOIN users u ON b.user_id = u.user_id
WHERE b.Private = 0 AND b.Tags LIKE ? 
ORDER BY 
    (CASE 
        WHEN b.Tags = ? THEN 1  -- Exact match first
        WHEN b.Tags LIKE CONCAT(?, ',%') THEN 2  -- Tag at the beginning
        WHEN b.Tags LIKE CONCAT('%', ?, '%') THEN 3  -- Tag appears anywhere
        ELSE 4  
    END), 
    b.datePublished DESC  -- Sort by date
LIMIT ? OFFSET ?;";

// Prepare the statement
$stmt = $conn->prepare($query);

// Debugging: Check if the query preparation is successful
if (!$stmt) {
    die("Query preparation error: " . $conn->error);
}

$tagParam = '%' . $tag . '%';  // For WHERE condition
$exactTag = $tag;  // For exact match
$firstTagParam = $tag . ',';  // For ordering first tag
$anyTagParam = $tag;  // For ordering if tag is anywhere

// Correct number of bind_param values
$stmt->bind_param("ssssii", $tagParam, $exactTag, $firstTagParam, $anyTagParam, $articles_per_page, $offset);

// Execute the query
if (!$stmt->execute()) {
    die("Execution error: " . $stmt->error);
}

// Fetch result
$blogs_result = $stmt->get_result();

// Close the statement
$stmt->close();
?>