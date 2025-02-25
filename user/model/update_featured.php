<?php
session_start();
include_once $_SERVER["DOCUMENT_ROOT"] . "/phpProjects/narrative/config/config.php";

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['isAdmin'] != 1) {
    die(json_encode(["error" => "Unauthorized access"]));
}

// Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;
    $featured = isset($_POST['featured']) ? intval($_POST['featured']) : 0;

    if ($article_id > 0) {
        $stmt = $conn->prepare("UPDATE tbl_blogs SET featured = ? WHERE id = ?");
        $stmt->bind_param("ii", $featured, $article_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Featured status updated successfully"]);
        } else {
            echo json_encode(["error" => "Database update failed"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
