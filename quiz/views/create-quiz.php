<?php
// quiz-create.php - Quiz Creation Page
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /features/login/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a Quiz</title>
<!--    <link rel="stylesheet" href="/quiz/css/quiz-create.css">-->
</head>
<body>
<h1>Create a New Quiz</h1>
<form id="quizForm">
    <label for="quizTitle">Quiz Title:</label>
    <input type="text" id="quizTitle" name="quizTitle" required>

    <label for="quizDescription">Description:</label>
    <textarea id="quizDescription" name="quizDescription"></textarea>

    <div id="questionsContainer">
        <!-- Questions will be added dynamically -->
    </div>

    <button type="button" id="addQuestion">Add Question</button>
    <button type="submit">Create Quiz</button>
</form>
<script>
    document.getElementById("addQuestion").addEventListener("click", function() {
        let questionContainer = document.getElementById("questionsContainer");
        let questionDiv = document.createElement("div");
        questionDiv.classList.add("question");
        questionDiv.innerHTML = `
                <label>Question:</label>
                <input type="text" name="questions[]" required>
                <label>Answer:</label>
                <input type="text" name="answers[]" required>
                <button type="button" class="removeQuestion">Remove</button>
            `;
        questionContainer.appendChild(questionDiv);
    });

    document.addEventListener("click", function(event) {
        if (event.target.classList.contains("removeQuestion")) {
            event.target.parentElement.remove();
        }
    });

    document.getElementById("quizForm").addEventListener("submit", function(event) {
        event.preventDefault();
        let formData = new FormData(this);
        fetch("/quiz/api/create-quiz-api.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.href = "/quiz/quiz-home.php";
                }
            })
            .catch(error => console.error("Error:", error));
    });
</script>
</body>
</html>
