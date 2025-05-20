<?php
// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
header("Location: " . BASE_URL . "user_auth.php");
exit;
}

// Get the current file name from the URL
$current_file = basename($_SERVER['PHP_SELF']);

$username = $_SESSION['username'];
// Define an array of tabs and their corresponding files
$tabs = [
'overview' => 'account.php',
'feed' => 'feed.php',
'quiz' => 'quiz-stats.php',
'settings' => 'settings.php',
'admin' => 'overview.php', // Include 'admin' in the tabs for checking the active tab
];

// Determine the active tab
$active_tab = array_search($current_file, $tabs);

// Get the user_id from the session
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

// Fetch the user's information (username and isAdmin status)
$query = "SELECT username, isAdmin FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_row = $user_result->fetch_assoc();

$username = $user_row['username']; // Fetch the username
$isAdmin = $user_row['isAdmin']; // Fetch the isAdmin status
?>