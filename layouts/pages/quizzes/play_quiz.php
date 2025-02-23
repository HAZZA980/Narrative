<?php
// Include the database connection
include $_SERVER['DOCUMENT_ROOT'] . "/phpProjects/narrative/config/config.php";

// Step 1: Fetch all quizzes from the database
$query = "SELECT id, quiz_title FROM tbl_quizzes";
$result = $conn->query($query);

if (!$result) {
    die("Error fetching quizzes: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Quizzes</title>
</head>
<body>

<h1>Available Quizzes</h1>

<?php
// Step 2: Check if there are any quizzes
if ($result->num_rows > 0) {
    // Step 3: Display each quiz as a link
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        $quiz_id = $row['id'];
        $quiz_title = htmlspecialchars($row['quiz_title']);
        echo "<li><a href='quiz_play.php?quiz_id=$quiz_id'>$quiz_title</a></li>";
    }
    echo "</ul>";
} else {
    echo "<p>No quizzes available. Please create some quizzes first.</p>";
}
?>

</body>
</html>
