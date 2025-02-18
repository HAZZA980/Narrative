<?php
function getCurrentFile($dir){
    $dir = explode("/", $dir);
    return $dir[count($dir) - 1];
}

function getArticleDescription($conn, $currentFile) {
    $currentTag = "";
    if ($currentFile === "b-big-tech.php") {
        $currentTag = "Big Tech";
    } else if ($currentFile === "entertainment.php") {
        $currentTag = "Film & Cinema";
    } else if ($currentFile === "history.php") {
        $currentTag = "Modern History";
    } else if ($currentFile === "p-US.php") {
        $currentTag = "US";
    } else if ($currentFile === "s-f1.php") {
        $currentTag = "F1";
    } else if ($currentFile === "s-football.php") {
        $currentTag = "Football";
    } else {
        $currentTag = "Big Tech";
    }

    // If no valid tag is found, return an empty result set
    if (empty($currentTag)) {
        return [];
    }

    // Define the SQL query with a placeholder
    $sql = "SELECT id, title, LEFT(content, 250) AS summary, date, Tags, featured, Image
            FROM tbl_blogs
            WHERE Tags = ?
            ORDER BY date DESC
            LIMIT 12";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Check if preparation was successful
    if ($stmt === false) {
        die("SQL prepare failed: " . $conn->error);
    }

    // Bind the variable to the placeholder
    $stmt->bind_param("s", $currentTag); // "s" indicates the type (string)

    // Execute the query
    $stmt->execute();

    // Fetch the result
    $result = $stmt->get_result();

    // Return the result object (caller handles data fetching)
    return $result;
}
