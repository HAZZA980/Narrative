<?php
// Assuming you have a database connection set up
include "../../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_title = trim($_POST['quiz_title']);

    // Ensure the quiz title is not empty
    if (empty($quiz_title)) {
        die("Quiz title is required.");
    }

    // Insert the new quiz into the database
    $query = "INSERT INTO tbl_quizzes (quiz_title) VALUES (?)";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $quiz_title);
        if ($stmt->execute()) {
            $quiz_id = $stmt->insert_id; // Get the last inserted quiz ID
            // Redirect to MCQ form where the user can add questions
            header("Location: mcq_form.php?quiz_id=$quiz_id");
            exit();
        } else {
            die("Error inserting quiz: " . $stmt->error);
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
</head>
<body>

<h1>Create a New Quiz</h1>
<form action="create_quiz.php" method="POST">
    <label for="quiz_title">Quiz Title:</label><br>
    <input type="text" id="quiz_title" name="quiz_title" required><br><br>
    <button type="submit">Create Quiz</button>
</form>

</body>
</html>
