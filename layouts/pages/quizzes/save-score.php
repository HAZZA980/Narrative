<?php
session_start();
// Check if the score and category are sent
if (isset($_POST['score']) && isset($_POST['category'])) {
    // Save the score in the session for each category
    $_SESSION['scores'][$_POST['category']] = $_POST['score'];
}

?>
