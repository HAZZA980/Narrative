<?php
// quiz-database.php - Handles database interactions for quizzes

// Database connection
$conn = new mysqli("localhost", "root", "", "db_narrative");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

define('BASE_URL', 'http://localhost/phpProjects/Narrative/');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/');

$current_page = basename($_SERVER['PHP_SELF'], ".php");

function loadHeader()
{
    include BASE_PATH . 'includes/header.php';
}

// Function to get all quizzes
function getAllQuizzes() {
    global $conn;
    $sql = "SELECT id, title, creator FROM quiz_quizzes ORDER BY created_at DESC";
    $result = $conn->query($sql);

    $quizzes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $quizzes[] = $row;
        }
    }
    return $quizzes;
}

// Function to get quiz by ID
function getQuizById($quiz_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM quiz_quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to create a new quiz
function createQuiz($title, $description, $creator) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO quiz_quizzes (title, description, creator) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $description, $creator);
    return $stmt->execute();
}
?>
