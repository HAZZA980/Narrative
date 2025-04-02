<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if ($_SESSION['quizType'] !== 'classic') {
    header("Location: " . BASE_URL . "quiz/basic-info.php");
    exit();
}

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
        let quizData = {}; // This will store the entered data for each column

        // Function to update the number of tables based on the selected columns
        function updateTables() {
            let numColumns = document.getElementById('numColumns').value;
            let contentDiv = document.getElementById('quizContent');

            // Clear previous content
            contentDiv.innerHTML = '';

            // Create the tables for the specified number of columns
            for (let i = 0; i < numColumns; i++) {
                let table = document.createElement('table');
                table.classList.add('quiz-table');
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Column ${i + 1}</th>
                            <th>Question</th>
                            <th>Answer 1</th>
                            <th>Answer 2</th>
                            <th>Answer 3</th>
                            <th>Answer 4</th>
                        </tr>
                    </thead>
                    <tbody id="columnBody${i}">
                    </tbody>
                `;
                contentDiv.appendChild(table);

                // Add rows from stored data if they exist
                if (quizData[i]) {
                    quizData[i].forEach((rowData, index) => {
                        addRow(i, rowData);
                    });
                } else {
                    // Add a default row if no data exists
                    addRow(i);
                }

                // Add Add Question button below each table
                let addButton = document.createElement('button');
                addButton.textContent = 'Add Question';
                addButton.classList.add('btn-add');
                addButton.setAttribute('type', 'button');
                addButton.onclick = function() { addRow(i); };
                contentDiv.appendChild(addButton);
            }
        }

        // Function to add a new row for a specific column
        function addRow(columnIndex, rowData = {}) {
            let tableBody = document.getElementById('columnBody' + columnIndex);
            let rowCount = tableBody.rows.length + 1;
            let row = document.createElement("tr");

            row.innerHTML = `
                <td><input type="text" name="question[${columnIndex}][]" class="form-input" value="${rowData.question || ''}" required></td>
                <td><input type="text" name="answer1[${columnIndex}][]" class="form-input" value="${rowData.answer1 || ''}" required></td>
                <td><input type="text" name="answer2[${columnIndex}][]" class="form-input" value="${rowData.answer2 || ''}"></td>
                <td><input type="text" name="answer3[${columnIndex}][]" class="form-input" value="${rowData.answer3 || ''}"></td>
                <td><input type="text" name="answer4[${columnIndex}][]" class="form-input" value="${rowData.answer4 || ''}"></td>
                <td><button type="button" class="btn-remove" onclick="removeRow(this, ${columnIndex})">✖</button></td>
            `;
            tableBody.appendChild(row);

            // Store the row data in quizData
            storeRowData(columnIndex);
        }

        // Function to remove a row from a specific column
        function removeRow(button, columnIndex) {
            let row = button.parentElement.parentElement;
            let tableBody = document.getElementById('columnBody' + columnIndex);

            // Ensure at least one row remains
            if (tableBody.rows.length > 1) {
                row.remove();
                storeRowData(columnIndex); // Update stored data
            }
        }

        // Function to store the data of each column into the quizData object
        function storeRowData(columnIndex) {
            let tableBody = document.getElementById('columnBody' + columnIndex);
            let rows = tableBody.rows;
            quizData[columnIndex] = [];

            for (let row of rows) {
                let inputs = row.querySelectorAll('input');
                quizData[columnIndex].push({
                    question: inputs[0].value,
                    answer1: inputs[1].value,
                    answer2: inputs[2].value,
                    answer3: inputs[3].value,
                    answer4: inputs[4].value
                });
            }
        }

        // Update the tables on page load and when the number of columns is changed
        window.onload = function() {
            updateTables();
            document.getElementById('numColumns').addEventListener('change', updateTables);
        }
    </script>
</head>
<body>

<div class="quiz-container">
    <div class="content">
        <h2>Create Quiz - Data</h2>

        <!-- Number of columns dropdown -->
        <label for="numColumns">Number of Columns:</label>
        <select id="numColumns" name="numColumns">
            <option value="1">1 Column</option>
            <option value="2">2 Columns</option>
            <option value="3">3 Columns</option>
            <option value="4">4 Columns</option>
        </select>

        <!-- Dynamic Quiz Content -->
        <form action="<?php echo BASE_URL ?>quiz/model/save-quiz.php" method="post">
            <input type="hidden" name="quizTitle" value="<?php echo htmlspecialchars($quizTitle); ?>">
            <input type="hidden" name="quizDesc" value="<?php echo htmlspecialchars($quizDesc); ?>">
            <input type="hidden" name="quizCategory" value="<?php echo htmlspecialchars($quizCategory); ?>">
            <input type="hidden" name="quizTags" value="<?php echo htmlspecialchars($quizTags); ?>">
            <input type="hidden" name="quizType" value="<?php echo htmlspecialchars($quizType); ?>">
            <input type="hidden" name="quizTimer" value="<?php echo htmlspecialchars($quizTimer); ?>">

            <!-- Dynamic tables for questions and answers based on the number of columns -->
            <div id="quizContent"></div>

            <!-- Save Quiz Button -->
            <button type="submit" class="btn-save">Save Quiz</button>
        </form>

    </div>
</div>

</body>
</html>
