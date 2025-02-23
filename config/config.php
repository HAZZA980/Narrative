<?php
// config.php

// Start the session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
loadHeader();
register_shutdown_function(function () {
    include BASE_PATH . "includes/footer.php";
});
?>
