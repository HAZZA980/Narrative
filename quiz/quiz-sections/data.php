<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';


// Retrieve stored quiz data from session
$quizType = $_SESSION['quizType'] ?? 'classic';
$quizTitle = $_SESSION['quizTitle'] ?? 'Untitled Quiz';
$quizDesc = $_SESSION['quizDesc'] ?? 'No description';
$quizCategory = $_SESSION['quizCategory'] ?? 'miscellaneous';
$quizTags = $_SESSION['quizTags'] ?? '';
$quizTimer = $_SESSION['quizTimer'] ?? '60';


// If form is submitted, clear the session data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    unset($_SESSION['quizType']);
    unset($_SESSION['quizTitle']);
    unset($_SESSION['quizDesc']);
    unset($_SESSION['quizCategory']);
    unset($_SESSION['quizTags']);

    // Optionally, redirect after clearing session data
    header("Location: " . BASE_URL . "quiz/model/save-quiz.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz - Data</title>
    <style>
        /* General Styling */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h2 {
            text-align: center;
            font-size: 28px;
            font-weight: 600;
            color: #007BFF;
            margin-bottom: 20px;
        }

        /* Quiz Container */
        .quiz-container {
            display: flex;
            min-height: 70vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #f4f4f4;
            padding: 20px;
            border-right: 2px solid #ddd;
        }

        /* Content Area */
        .content {
            flex: 1;
            padding: 20px;
        }

        /* Quiz Table */
        .quiz-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .quiz-table th, .quiz-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .quiz-table th {
            background-color: #007BFF;
            color: white;
        }

        /* Input Fields */
        .form-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Buttons */
        .btn-add, .btn-save {
            background-color: #007BFF;
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s ease;
            margin-top: 15px;
            display: inline-block;
        }

        .btn-add:hover {
            background-color: #0056b3;
        }

        .btn-save {
            background-color: #28a745;
        }

        .btn-save:hover {
            background-color: #218838;
        }


        .btn-remove {
            background-color: transparent;
            color: red;
            font-weight: bold;
            font-size: 18px;
            border: none;
            cursor: pointer;
            transition: 0.3s ease;
            padding: 5px 10px;
        }

        .btn-remove:hover {
            color: darkred;
            transform: scale(1.2);
        }

    </style>
    <script>
        function updateTable() {
            let quizType = "<?php echo $quizType; ?>";
            let tableHead = document.getElementById("quizDataHead");

            // Clear previous headers
            tableHead.innerHTML = "";

            // Define common headers (but hide the # column)
            let headers = ["", "Question", "Answer 1", "Answer 2", "Answer 3", "Answer 4"];
            if (quizType === "clickable") {
                headers.splice(1, 0, "Correct Answer"); // Add 'Correct Answer' after remove button
            }

            // Add table headers dynamically
            let headerRow = "<tr>";
            headers.forEach(header => {
                headerRow += `<th>${header}</th>`;
            });
            headerRow += "</tr>";
            tableHead.innerHTML = headerRow;

            // Add the first default row
            addRow();
        }

        function addRow() {
            let quizType = "<?php echo $quizType; ?>";
            let tableBody = document.getElementById("quizDataBody");
            let rowCount = tableBody.rows.length + 1;

            let row = document.createElement("tr");

            // Hidden column for numbering (still needed for logic)
            row.innerHTML = `<td style="display:none;">${rowCount}</td>`;

            // Remove button on the left
            let removeTd = document.createElement("td");
            removeTd.innerHTML = `<button type="button" class="btn-remove" onclick="removeRow(this)">✖</button>`;
            row.appendChild(removeTd);

            // Question and answers
            row.innerHTML += `
        <td><input type="text" name="question[]" class="form-input" required></td>
        <td><input type="text" name="answer1[]" class="form-input" required></td>
        <td><input type="text" name="answer2[]" class="form-input"></td>
        <td><input type="text" name="answer3[]" class="form-input"></td>
        <td><input type="text" name="answer4[]" class="form-input"></td>`;

            // If quiz type is clickable, add correct answer selection
            if (quizType === "clickable") {
                let correctAnswerTd = document.createElement("td");
                correctAnswerTd.innerHTML = `
            <input type="radio" name="correct_answer[${rowCount - 1}]" value="answer1" required> 1
            <input type="radio" name="correct_answer[${rowCount - 1}]" value="answer2"> 2
            <input type="radio" name="correct_answer[${rowCount - 1}]" value="answer3"> 3
            <input type="radio" name="correct_answer[${rowCount - 1}]" value="answer4"> 4
        `;
                row.appendChild(correctAnswerTd);
            }

            tableBody.appendChild(row);
        }

        function removeRow(button) {
            let row = button.parentElement.parentElement;
            let tableBody = document.getElementById("quizDataBody");

            // Ensure at least one row remains
            if (tableBody.rows.length > 1) {
                row.remove();
                updateRowNumbers();
            }
        }

        function updateRowNumbers() {
            let rows = document.querySelectorAll("#quizDataBody tr");
            rows.forEach((row, index) => {
                row.cells[0].innerText = index + 1; // Update hidden numbering
            });
        }

        window.onload = updateTable;

 </script>
</head>
<body>

<div class="quiz-container">
    <div class="content">
        <h2>Create Quiz - Data</h2>

        <!-- Questions Table -->
        <form action="<?php echo BASE_URL ?>quiz/model/save-quiz.php" method="post">
            <!-- Inside <form> tag -->
            <input type="hidden" name="quizTitle" value="<?php echo htmlspecialchars($quizTitle); ?>">
            <input type="hidden" name="quizDesc" value="<?php echo htmlspecialchars($quizDesc); ?>">
            <input type="hidden" name="quizCategory" value="<?php echo htmlspecialchars($quizCategory); ?>">
            <input type="hidden" name="quizTags" value="<?php echo htmlspecialchars($quizTags); ?>">
            <input type="hidden" name="quizType" value="<?php echo htmlspecialchars($quizType); ?>">
            <input type="hidden" name="quizTimer" value="<?php echo htmlspecialchars($quizTimer); ?>">

            <table border="1" width="100%">
                <thead id="quizDataHead"></thead>
                <tbody id="quizDataBody"></tbody>
            </table>

            <!-- Add Question Button -->
            <button type="button" class="btn-add" onclick="addRow()">Add Question</button>
            <button type="submit" class="btn-save">Save Quiz</button>
        </form>

    </div>
</div>

</body>
</html>
