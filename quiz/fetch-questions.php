<?php
// Fetch quiz questions from the database

header('Content-Type: application/json'); // Ensure the response is treated as JSON

// Assuming $conn is your database connection
$category = $_GET['category'] ?? ''; // Fetch category from query string

if ($category) {
    $stmt = $conn->prepare("SELECT q.id, q.question_text, a.answer_text 
                            FROM `quiz-questions` q
                            LEFT JOIN `quiz-answers` a ON q.id = a.question_id AND a.is_correct = 1
                            WHERE q.category = ?");
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $result = $stmt->get_result();

    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            'question' => $row['question_text'],
            'answer' => $row['answer_text'] // Correct answer
        ];
    }

    echo json_encode($questions); // Output the questions as a JSON array
} else {
    echo json_encode(['error' => 'Category not found']); // Return an error message if category is not provided
}
?>
