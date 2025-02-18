<?php
session_start();  // Always start the session at the very top
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

<style>
    .aside-writing-link {
        position: fixed;
        right: 3%;
        bottom: 20px; /* Start from the bottom right */
        width: auto;
        display: flex;
        align-items: center;
        z-index: 1000; /* Keeps it above other elements */
        height: auto;
        background: white; /* Ensure visibility */
        padding: 10px 12px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out, bottom 0.3s ease-in-out;
    }

    /* Additional styles for the Write button */
    .write-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        gap: 10px;
    }

    .write-link img {
        width: 24px;
        height: 24px;
    }

    .aside-write {
        color: #333;
        font-size: 1rem;
        font-weight: bold;
        margin: 0;
    }
</style>

<aside class="aside-writing-link">
    <a href="<?php echo BASE_URL?>user/createArticle.php" class="write-link">
        <img src="<?php echo BASE_URL?>public/images/article-layout-img/pencil-square.svg" alt="Write Icon">
        <h3 class="aside-write">Write</h3>
    </a>
</aside>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const writeButton = document.querySelector(".aside-writing-link");
        const footer = document.querySelector("footer");

        function adjustWriteButton() {
            const footerRect = footer.getBoundingClientRect();
            const windowHeight = window.innerHeight;

            if (footerRect.top < windowHeight) {
                const offset = windowHeight - footerRect.top;
                writeButton.style.bottom = `${20 + offset}px`; // Moves up with the footer
            } else {
                writeButton.style.bottom = '20px'; // Stays fixed at bottom right
            }
        }

        window.addEventListener("scroll", adjustWriteButton);
        adjustWriteButton(); // Initial check
    });
</script>
