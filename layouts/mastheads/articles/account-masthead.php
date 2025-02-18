<?php
// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

// Get the current file name from the URL
$current_file = basename($_SERVER['PHP_SELF']);

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
<main class="account-header-tabs">
    <div class="account-tabs-container">
        <h3 class="account-username"><?php echo htmlspecialchars($username); ?></h3>
        <div class="account-tabs">
            <a href="<?php echo BASE_URL ?>account.php"
               class="account-tab overview <?php echo $active_tab === 'overview' ? 'active' : ''; ?>">
                Overview
            </a>
            <a href="<?php echo BASE_URL ?>account/feed.php"
               class="account-tab feed <?php echo $active_tab === 'feed' ? 'active' : ''; ?>">
                Your Feed
            </a>
            <a href="<?php echo BASE_URL ?>account/quiz-stats.php"
               class="account-tab quiz <?php echo $active_tab === 'quiz' ? 'active' : ''; ?>">
                Quiz Stats
            </a>
            <a href="<?php echo BASE_URL ?>account/settings.php"
               class="account-tab settings <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                Settings
            </a>

            <!-- Show the Admin tab only if the user is an admin -->
            <?php if ($isAdmin == 1): ?>
                <a href="<?php echo BASE_URL ?>admin/overview.php"
                   class="account-tab admin <?php echo $active_tab === 'admin' ? 'active' : ''; ?>">
                    Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</main>
<style>
    .account-header-tabs {
        width: 73%;
        margin: 30px auto 0 auto;
        background: #fff;
        padding: 20px 0px 0 0px;
    }

    .account-tabs-container {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    .account-username {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .account-tabs {
        display: flex;
        border-radius: 8px 0 0 0;
        background-color: #f1f1f1;
    }

    .account-tab {
        padding: 10px 20px;
        text-decoration: none;
        font-size: 16px;
        font-weight: bold;
        color: #555;
        transition: color 0.3s ease, border-color 0.3s ease;
    }

    .account-tab.active {
        background-color: #f9f9f9;
        border-top: 2px solid black;
        border-left: 2px solid black;
        border-right: 2px solid black;
        border-radius: 8px 8px 0 0;
    }

    .account-tab:hover {
        color: #0056b3;
    }

    /* Admin Tab Styles */
    .account-tab.admin {
        background-color: darkgrey;
        color: black; /* Reddish color for the text */
        font-weight: bold;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        transition: all 0.3s ease;
        border-bottom: none;

    }

    .account-tab.admin:hover {
        background-color: black; /* Slightly darker shade of red on hover */
        color: #ffffff; /* Darker text color on hover */
        border-color: black;
    }

    .account-tab.admin.active {
        background-color: darkgrey;
        color: black;
        border-top: 2px solid black;
        border-left: 2px solid black;
        border-right: 2px solid black;
        border-bottom: 2px solid black;

    }

</style>
