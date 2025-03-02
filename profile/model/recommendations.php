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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get selected categories
    $selected_categories = json_decode($_POST['categories'], true) ?? [];

    // Check if no categories were selected
    if (empty($selected_categories)) {
        header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=3&error=select_category");
        exit;
    }

    // Delete old categories
    $stmt = $conn->prepare("DELETE FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Insert new categories
    $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, tag) VALUES (?, ?)");
    foreach ($selected_categories as $category) {
        $stmt->bind_param("is", $user_id, $category);
        $stmt->execute();
    }

    header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=4"); // Next tab
    exit;
}
?>
