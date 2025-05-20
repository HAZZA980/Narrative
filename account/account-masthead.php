<?php
include BASE_PATH . 'features/write/write-icon-fixed.php';
include BASE_PATH . 'account/model/account_masthead-logic.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo BASE_URL?>account/css/account-masthead.css">

</head>
<body>

</body>
</html>
<main class="account-header-tabs">
    <div class="account-tabs-container">
        <h3 class="account-username">
            <a href="<?php echo BASE_URL?>feed.php?username=<?php echo urlencode($username); ?>">
                <?php echo htmlspecialchars($username); ?>
            </a>
        </h3>
        <div class="account-tabs">
            <a href="<?php echo BASE_URL ?>account.php"
               class="account-tab overview <?php echo $active_tab === 'overview' ? 'active' : ''; ?>">
                Overview
            </a>
            <a href="<?php echo BASE_URL ?>account/feed.php"
               class="account-tab feed <?php echo $active_tab === 'feed' ? 'active' : ''; ?>">
                Your Feed
            </a>
<!--            <a href="--><?php //echo BASE_URL ?><!--account/quiz-stats.php"-->
<!--               class="account-tab quiz --><?php //echo $active_tab === 'quiz' ? 'active' : ''; ?><!--">-->
<!--                Quiz Stats-->
<!--            </a>-->
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

