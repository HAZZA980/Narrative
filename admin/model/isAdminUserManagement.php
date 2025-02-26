<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"], $_POST["isAdmin"])) {
    $user_id = intval($_POST["user_id"]);
    $isAdmin = intval($_POST["isAdmin"]);

    // Update isAdmin status in database
    $update_query = "UPDATE users SET isAdmin = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $isAdmin, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "admin_status" => $isAdmin,
            "message" => $isAdmin == 1 ? "User has been made an admin." : "User has been removed from admin."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to update admin status."
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
}
?>
