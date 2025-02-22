<?php
ob_start();
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: user_auth.php");
    exit;
}

// Assuming you have already fetched the username
$username = $_SESSION['username']; // Replace this with actual fetching logic
$user_id = $_SESSION['user_id'];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Interests</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<!--    <link rel="stylesheet" href="--><?php //echo BASE_URL ?><!--user/css/set-up-account.css"><!-- Link to external stylesheet -->-->
    <style>
        .profile-placeholder, .profile-section img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: bold;
            color: white;
            background-color: #<?php echo substr(md5($username), 0, 6); ?>;
            margin: auto;
        }


        /* Progress Bar */
        .progress-bar-container {
            width: 100%;
            height: 8px;
            background-color: #f3f3f3;
            border-radius: 5px;
            margin-top: 20px;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background-color: #007BFF;
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .navigation-buttons .finish-btn {
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

        .navigation-buttons .finish-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .previous-btn {
            margin-right: auto;
        }

        .next-btn {
            margin-left: auto;
        }

        /* Profile Picture container */
        .profile-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: bold;
            color: white;
            background-color: #<?php echo substr(md5($username), 0, 6); ?>;
            margin: auto;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: background 0.3s ease-in-out;
        }
    </style>
</head>
<body>

<!-- Tabs Container -->
<div class="tabs-container">
    <div class="tabs">
        <button class="tab-button active" data-tab="personal-details" onclick="changeTab('personal-details')">1.
            Personal Details
        </button>
        <button class="tab-button" data-tab="bio" onclick="changeTab('bio')">2. Bio</button>
        <button class="tab-button" data-tab="recommendations" onclick="changeTab('recommendations')">3.
            Recommendations
        </button>
        <button class="tab-button" data-tab="finalizing-details" onclick="changeTab('finalizing-details')">4. Finalising
            Details
        </button>
    </div>

    <div id="personal-details" class="tab-content active">
        <!-- Full-width Welcome Section -->
        <div class="pb-welcoming-details">
            <h2>Hi, <?php echo htmlspecialchars($username); ?></h2>
            <p>Welcome to Narrative. We have a few things for you to complete in order to set up your profile. If you're in a rush, you can skip ahead and fill in these details in your settings.</p>
        </div>

        <form class="details-container">
            <!-- Left Side: User Info -->
            <div class="user-info">
                <div class="user-info-row">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                <div class="user-info-row">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
            </div>

            <!-- Right Side: Profile Picture -->
            <div class="profile-section">
                <label class="profile-label">Profile Picture</label>
                <input type="file" id="profile-pic" name="profile-pic" accept="image/*" hidden>

                <!-- Clickable Profile Picture Display -->
                <div id="profile-placeholder" class="profile-placeholder">
                    <span id="profile-initial"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                    <img id="profile-img" src="#" alt="Profile Picture" style="display: none;">

                    <!-- Hover Icon (+ or X) -->
                    <div class="hover-icon" id="hover-icon">+</div>
                </div>
            </div>
        </form>


        <!-- Next button -->
        <div class="form-footer">
            <button type="button" class="finish-btn next-btn">Next</button>
        </div>
    </div>

    <div id="bio" class="tab-content">
        <h3>Your Bio</h3>
        <form>
            <label for="bio-text">Tell your readers a bit about yourself. How you started writing, hobbies, interests...</label><br>
            <textarea id="bio-text" name="bio-text" rows="8" cols="50" maxlength="1000" oninput="updateWordCount()" placeholder="But do it in 150 words!"></textarea>
            <p id="word-count">0 / 150 words</p>

            <div class="navigation-buttons">
<!--                <button type="button" class="finish-btn previous-btn" onclick="previousTab('personal-details')">-->
<!--                    Previous-->
                </button>
                <button type="button" class="finish-btn next-btn" onclick="nextTab('recommendations')">Next</button>
            </div>
        </form>
    </div>
    <script>
        function updateWordCount() {
            const bioText = document.getElementById("bio-text");
            const wordCountDisplay = document.getElementById("word-count");

            // Count words
            let words = bioText.value.trim().split(/\s+/);
            let wordCount = words[0] === "" ? 0 : words.length;

            // Limit to 150 words
            if (wordCount > 150) {
                words = words.slice(0, 150);
                bioText.value = words.join(" ");
                wordCount = 150;
            }

            wordCountDisplay.textContent = `${wordCount} / 150 words`;
        }
    </script>

    <div id="recommendations" class="tab-content">
        <?php include BASE_PATH . "features/submitProfile/model/profile-recommendations.php"; ?>
        <h3>Select Your Interests</h3>
        <h5>(We'll recommend articles based on your reading history)</h5>
        <div class="categories">
            <?php foreach ($categories as $category): ?>
                <button type="button" class="category-button"
                        data-category="<?php echo htmlspecialchars($category); ?>">
                    <?php echo htmlspecialchars($category); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <form id="category-form" method="POST" action="<?php echo BASE_URL?>features/submitProfile/model/profile-recommendations.php">
            <input type="hidden" name="categories" id="categories-input" value="">
            <div class="navigation-buttons">
                <button type="button" class="finish-btn previous-btn" onclick="previousTab('bio')">Previous</button>
                <button type="submit" class="finish-btn next-btn" disabled>Next</button>
            </div>
        </form>
    </div>

    <div id="finalizing-details" class="tab-content">
        <h3>Review Your Profile</h3>

        <!-- User Profile Container -->
        <div class="final-profile-container">
            <!-- Profile Picture -->
            <div class="final-profile-picture">
                <img id="final-profile-img" src="#" alt="Profile Picture">
                <div id="final-profile-placeholder">
                    <span id="final-profile-initial"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                </div>
            </div>

            <!-- User Info -->
            <div class="final-user-info">
                <h4><?php echo htmlspecialchars($username); ?></h4>
                <p><strong>Date of Birth:</strong> <span id="final-dob"></span></p>
            </div>
        </div>

        <!-- Bio Section -->
        <div class="final-bio">
            <h4>Bio</h4>
            <p id="final-bio-text"></p>
        </div>

        <!-- Preferences Section -->
        <div class="final-preferences">
            <h4>Your Interests</h4>
            <p id="final-preferences-list"></p>
        </div>

        <!-- Form Submission -->
        <form action="<?php echo BASE_URL ?>features/submitProfile/model/submitProfile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="dob" id="dob-input">
            <input type="hidden" name="bio-text" id="bio-input">
            <input type="hidden" name="categories" id="categories-input">
            <input type="file" name="profile-pic" id="profile-pic-input" style="display: none;">

            <!-- Save Profile Button -->
            <button type="submit" class="finish-btn save-btn">Save Profile</button>
        </form>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <button type="button" class="finish-btn previous-btn" onclick="previousTab('recommendations')">Previous</button>
        </div>
    </div>


    <!-- Progress Bar -->
    <div class="progress-bar-container">
        <div id="progress-bar" class="progress-bar"></div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tabButtons = document.querySelectorAll(".tab-button");
        const tabContents = document.querySelectorAll(".tab-content");
        const progressBar = document.getElementById("progress-bar");

        let tabIndex = 0;
        const tabs = ["personal-details", "bio", "recommendations", "finalizing-details"];

        // Function to handle tab switching
        function changeTab(tabId) {
            tabIndex = tabs.indexOf(tabId);

            // Update the progress bar based on the current tab index
            const progressPercentage = (tabIndex / (tabs.length - 1)) * 100;
            progressBar.style.width = `${progressPercentage}%`;

            // Update URL hash
            window.location.hash = tabId;

            // Remove active class from all tab buttons and contents
            tabButtons.forEach(button => button.classList.remove("active"));
            tabContents.forEach(content => content.classList.remove("active"));

            // Activate the clicked tab
            document.querySelector(`[data-tab="${tabId}"]`).classList.add("active");
            document.getElementById(tabId).classList.add("active");
        }

        // Move to next tab
        function nextTab() {
            if (tabIndex < tabs.length - 1) {
                tabIndex++; // Move forward
                changeTab(tabs[tabIndex]);
            } else if (tabIndex === 2) {
                // If we're on the "recommendations" tab (index 2), go directly to "finalizing-details"
                changeTab("finalizing-details");
            }
        }

        // Move to previous tab
        function previousTab() {
            if (tabIndex > 0) {
                tabIndex--; // Move backward
                changeTab(tabs[tabIndex]);
            }
        }

        // Attach event listeners to Next and Previous buttons
        document.querySelectorAll(".next-btn").forEach(button => {
            button.addEventListener("click", nextTab);
        });

        document.querySelectorAll(".previous-btn").forEach(button => {
            button.addEventListener("click", previousTab);
        });

        // Check URL on page load
        if (window.location.hash) {
            const activeTab = window.location.hash.substring(1); // Remove `#`
            changeTab(activeTab);
        } else {
            changeTab("personal-details"); // Default to first tab
        }
    });






    document.addEventListener("DOMContentLoaded", function () {
        const categoryButtons = document.querySelectorAll(".category-button");
        const categoriesInput = document.getElementById("categories-input");
        const submitButton = document.querySelector("#category-form .next-btn");

        let selectedCategories = [];

        categoryButtons.forEach(button => {
            button.addEventListener("click", () => {
                const category = button.getAttribute("data-category");

                if (selectedCategories.includes(category)) {
                    selectedCategories = selectedCategories.filter(item => item !== category);
                    button.classList.remove("selected");
                } else {
                    selectedCategories.push(category);
                    button.classList.add("selected");
                }

                categoriesInput.value = JSON.stringify(selectedCategories);
                submitButton.disabled = selectedCategories.length === 0;
            });
        });

        // Redirect user to the last tab after form submission
        submitButton.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent form from reloading the page
            document.getElementById("category-form").submit(); // Submit the form
            setTimeout(() => {
                window.location.href = "set-up-account.php#last-tab"; // Replace 'last-tab' with the actual tab ID
            }, 500);
        });
    });




    document.addEventListener("DOMContentLoaded", function () {
        // Set the final profile details
        document.getElementById("final-dob").textContent = document.getElementById("dob").value;
        document.getElementById("final-bio-text").textContent = document.getElementById("bio-text").value;

        // Display selected categories
        let categories = JSON.parse(document.getElementById("categories-input").value || "[]");
        document.getElementById("final-preferences-list").textContent = categories.length > 0 ? categories.join(", ") : "No preferences selected.";

        }

</script>


<script>document.addEventListener("DOMContentLoaded", function () {
        const profilePicInput = document.getElementById("profile-pic");
        const profilePlaceholder = document.getElementById("profile-placeholder");
        const profileImg = document.getElementById("profile-img");
        const profileInitial = document.getElementById("profile-initial");
        const hoverIcon = document.getElementById("hover-icon");

        // Clickable placeholder triggers file input
        profilePlaceholder.addEventListener("click", function () {
            profilePicInput.click();
        });

        // Handle file selection
        profilePicInput.addEventListener("change", function (event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    profileImg.src = e.target.result;
                    profileImg.style.display = "block"; // Show the image
                    profileInitial.style.display = "none"; // Hide initial
                    hoverIcon.textContent = "✖"; // Change to remove icon
                };

                reader.readAsDataURL(file);
            }
        });

        // Remove image on clicking "X"
        hoverIcon.addEventListener("click", function (event) {
            event.stopPropagation(); // Prevent triggering file input
            profilePicInput.value = ""; // Clear file input
            profileImg.src = "#";
            profileImg.style.display = "none"; // Hide image
            profileInitial.style.display = "block"; // Show initial
            hoverIcon.textContent = "+"; // Reset icon
        });
    });
</script>

</body>
</html>
