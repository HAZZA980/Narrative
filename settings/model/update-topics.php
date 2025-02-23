<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: " . BASE_PATH . "user_auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

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

    $previous_page = $_SERVER['HTTP_REFERER'] ?? '';

    if (strpos($previous_page, BASE_URL . "settings/content-preferences.php?accountManagement=update-topics") !== false) {
        // Redirect back to update-topics with success message
        header("Location: " . $previous_page . "&success=true");
    } else {
        // Redirect to next tab with success message
        header("Location: " . BASE_URL . "profile/set-up-profile.php?tab=4&success=true");
    }
    exit;
}

ob_end_flush();
?>
