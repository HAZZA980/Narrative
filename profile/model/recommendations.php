<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/narrative/config/config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: " . BASE_PATH . "user_auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get selected categories from the form
    $selected_categories = json_decode($_POST['categories'], true) ?? [];

    // Step 1: Fetch the current categories from the database
    $query = "SELECT tag FROM user_preferences WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Store the current categories in an array
    $current_categories = [];
    while ($row = $result->fetch_assoc()) {
        $current_categories[] = $row['tag'];
    }

    // Step 2: Merge the current categories with the newly selected categories
    $updated_categories = array_unique(array_merge($current_categories, $selected_categories)); // Remove duplicates

    // Step 3: Delete the old categories and insert the updated ones
    // Delete existing categories for the user
    $stmt = $conn->prepare("DELETE FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Insert the updated categories
    $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, tag) VALUES (?, ?)");
    foreach ($updated_categories as $category) {
        $stmt->bind_param("is", $user_id, $category);
        $stmt->execute();
    }

    // Step 4: Redirect to the next tab
    header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=4"); // Redirect to next tab
    exit;
}
?>
