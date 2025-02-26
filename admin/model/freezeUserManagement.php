<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"], $_POST["freeze_status"])) {
    $user_id = intval($_POST["user_id"]);
    $freeze_status = intval($_POST["freeze_status"]);

    // Update freeze status in database
    $update_query = "UPDATE users SET freeze_user = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $freeze_status, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "freeze_status" => $freeze_status,
            "message" => $freeze_status == 1 ? "User has been frozen." : "User has been unfrozen."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to update freeze status."
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
