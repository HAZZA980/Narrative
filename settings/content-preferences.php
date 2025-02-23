<?php
// Avoid starting the session again by using require_once
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

// Now you can access the session variables and constants defined in config.php
$user_id = $_SESSION['user_id'];

// Continue with the rest of the logic for update-topics...
include BASE_PATH . "account/account-masthead.php";
include BASE_PATH . "settings/model/update-topics.php";

// Determine which section to show
$section = $_GET['accountManagement'] ?? 'update-topics';

// Include the appropriate logic files for username, password, dob, or content preferences
if ($section === 'notification-preferences') {
    include BASE_PATH . "settings/model/notification-preferences.php";
} elseif ($section === 'language-preferences') {
    include BASE_PATH . "settings/model/language-preferences.php";
} elseif ($section === 'update-topics') {
    include BASE_PATH . "settings/model/update-topics.php";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>settings/css/settings.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>account/css/styles-register-password.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>account/css/account-management-template.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>profile/css/recommendations.css">
    <style>
        .categories{
            margin-bottom: 3rem;
        }
        .category-button {
            padding: 10px 20px;
            background-color: #f1f1f1;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            /* Default size and color */
            color: #333;
            font-size: 14px;
        }

        .category-button:hover {
            background-color: #ddd;
        }

        /* Style for selected categories */
        .category-button.selected {
            background-color: #007bff; /* Blue background for selected categories */
            color: white; /* White text */
            border-color: #0056b3; /* Darker blue border */
            transform: scale(1.05); /* Slightly scale up the selected button */
        }

        .category-button.selected:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Style for unselected categories - same color and size */
        .category-button:not(.selected) {
            background-color: #f1f1f1;
            color: #333; /* Text color for unselected tags */
            border: 1px solid #ccc; /* Border for unselected tags */
            font-size: 14px; /* Normal font size for unselected tags */
            transform: scale(1); /* No scaling for unselected buttons */
        }

    </style>

    <title>Account Settings</title>
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>

<div class="settings-outer-container">
    <div class="settings-inner-container">

        <div class="settings-container">
            <!-- Sidebar Menu -->
            <nav class="settings-sidebar">
                <h3 class="settings-section-title">Account Management</h3>
                <ul class="settings-menu">
                    <li><a href="?accountManagement=update-topics"
                           class="<?= $section === 'update-topics' ? 'active' : '' ?>">Article Recommendations</a></li>
                    <li><a href="?accountManagement=notification-preferences"
                           class="<?= $section === 'notification-preferences' ? 'active' : '' ?>">Notification
                            Preferences</a></li>
                    <li><a href="?accountManagement=language-preferences"
                           class="<?= $section === 'language-preferences' ? 'active' : '' ?>">Language Preferences</a>
                    </li>
                </ul>
            </nav>

            <!-- Content Section -->
            <main class="settings-content">
                <?php if ($section === 'update-topics'): ?>
                <section class="settings-section">
                    <h3>Update Your Favourite Topics</h3>

                    <p class="topic-desc">Let us know what subjects you prefer to read. It'll help us recommend articles that you
                        might find interesting. You can turn personalised recommendations off in your privacy
                        settings. <a href="#">See How We Use Your Data</a></p>

                    <?php
                    // Retrieve selected categories from the database
                    $user_id = $_SESSION['user_id'];

                    $query = "SELECT tag FROM user_preferences WHERE user_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $selectedCategoriesFromDatabase = [];
                    while ($row = $result->fetch_assoc()) {
                        $selectedCategoriesFromDatabase[] = $row['tag'];
                    }
                    ?>

                    <div class="categories">
                        <?php
                        $categories = ["Lifestyle", "Writing Craft", "Travel", "Reviews", "History & Culture", "Entertainment", "Business", "Technology", "Politics", "Science", "Sports", "Health & Fitness", "Food & Drink"];

                        foreach ($categories as $category): ?>
                            <button type="button" class="category-button"
                                    data-category="<?php echo htmlspecialchars($category); ?>">
                                <?php echo htmlspecialchars($category); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($_GET['success']) && $_GET['success'] === 'true'): ?>
                        <p class="success-message" style="color: green; font-weight: bold;">TOPICS have been updated successfully!</p>
                    <?php endif; ?>
                    <form id="category-form" class="settings-form" method="POST"
                          action="<?php echo BASE_URL; ?>settings/model/update-topics.php">
                        <input type="hidden" name="categories" id="categories-input" value="">
                        <button type="submit" name="update_topics">Update Topics</button>
                    </form>
                </section>


                <?php elseif ($section === 'notification-preferences'): ?>
                    <section class="settings-section">
                        <h2>Notification Preferences</h2>
                        <!-- Notification Preferences Form -->
                        <form method="post" class="settings-form">
                            <!-- Add form fields for notification preferences -->
                            <button type="submit" name="change_notification_preferences">Update Preferences</button>
                        </form>
                    </section>

                <?php elseif ($section === 'language-preferences'): ?>
                    <section class="settings-section">
                        <h2>Language Preferences</h2>
                        <!-- Language Preferences Form -->
                        <form method="post" class="settings-form">
                            <!-- Add form fields for language preferences -->
                            <button type="submit" name="change_language_preferences">Update Language</button>
                        </form>
                    </section>


                <?php endif; ?>
            </main>
        </div>
    </div>
</div>
<script>
    // Add event listener for category button clicks
    document.querySelectorAll('.category-button').forEach(function (button) {
        button.addEventListener('click', function () {
            // Get the category name from the button's data attribute
            var category = this.getAttribute('data-category');
            var isSelected = this.classList.contains('selected-category'); // Check if it's already selected

            // Toggle the 'selected-category' class for visual feedback
            this.classList.toggle('selected-category');

            // Send AJAX request to update the user's preferences in the database
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_user_preferences.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status == 200) {
                    // Handle success
                    console.log('Preferences updated successfully!');
                } else {
                    // Revert the toggle if there was an error
                    console.error('Error updating preferences');
                    this.classList.toggle('selected-category');
                }
            };
            xhr.send('category=' + encodeURIComponent(category) + '&isSelected=' + (isSelected ? 0 : 1)); // 0 to unselect, 1 to select
        });
    });


    let selectedCategories = <?php echo json_encode($selectedCategoriesFromDatabase); ?>; // The selected categories from the database
</script>

<script src="<?php echo BASE_URL; ?>settings/js/update-topics.js"></script>

</body>
</html>
