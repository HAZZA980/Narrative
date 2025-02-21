<?php
ob_start();
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: user_auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$categories = ["Lifestyle", "Writing Craft", "Travel", "Reviews", "History & Culture", "Entertainment", "Business", "Technology",
    "Politics", "Science", "Sports", "Health & Fitness", "Food & Drink"];

// Handle form submission
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

    header("Location: " . BASE_URL . "forYou.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Interests</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f9fc;
            text-align: center;
            padding: 20px;
        }
        h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        .categories {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        .category-button {
            padding: 12px 20px;
            border-radius: 25px;
            border: 2px solid #007BFF;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            background-color: white;
            color: #007BFF;
        }
        .category-button.selected {
            background-color: #007BFF;
            color: white;
        }
        .finish-btn {
            margin-top: 20px;
            padding: 12px 25px;
            font-size: 1.2rem;
            font-weight: bold;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }
        .finish-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<h2>Select Your Interests</h2>
<h5>(We'll recommend articles based on your reading history)</h5>
<div class="categories">
    <?php foreach ($categories as $category): ?>
        <button type="button" class="category-button" data-category="<?php echo htmlspecialchars($category); ?>">
            <?php echo htmlspecialchars($category); ?>
        </button>
    <?php endforeach; ?>
</div>
<form id="category-form" method="POST" action="recommendations.php">
    <input type="hidden" name="categories" id="categories-input" value="">
    <button type="submit" class="finish-btn" disabled>Finish</button>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let selectedCategories = [];
        const categoryButtons = document.querySelectorAll(".category-button");
        const finishButton = document.querySelector(".finish-btn");
        const categoriesInput = document.querySelector("#categories-input");

        categoryButtons.forEach(button => {
            button.addEventListener("click", () => {
                const category = button.dataset.category;
                if (selectedCategories.includes(category)) {
                    selectedCategories = selectedCategories.filter(c => c !== category);
                    button.classList.remove("selected");
                } else {
                    selectedCategories.push(category);
                    button.classList.add("selected");
                }
                categoriesInput.value = JSON.stringify(selectedCategories);
                finishButton.disabled = selectedCategories.length === 0;
            });
        });
    });
</script>
</body>
</html>
