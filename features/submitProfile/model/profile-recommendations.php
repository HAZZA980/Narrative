<?php
$categories = ["Lifestyle", "Writing Craft", "Travel", "Reviews", "History & Culture", "Entertainment", "Business", "Technology",
    "Politics", "Science", "Sports", "Health & Fitness", "Food & Drink"];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_categories = json_decode($_POST['categories'], true) ?? [];

    // Update the database with category preferences
    $stmt = $conn->prepare("DELETE FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, tag) VALUES (?, ?)");
    foreach ($selected_categories as $category) {
        $stmt->bind_param("is", $user_id, $category);
        $stmt->execute();
    }

    header("Location: " . BASE_URL . "user/set-up-account.php#finalizing-details");
    exit;
}
?>