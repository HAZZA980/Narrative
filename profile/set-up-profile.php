<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/narrative/config/config.php';
include BASE_PATH . "profile/model/finalise.php";
session_start();
// Assuming you have already fetched the username
$username = $_SESSION['username']; // Replace this with actual fetching logic

// Determine which tab is selected via URL, default to Personal Details (tab 1)
$tab = isset($_GET['tab']) ? (int)$_GET['tab'] : 1;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set Up Profile</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>profile/css/recommendations.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>profile/css/tab1.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>profile/css/tab2.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>profile/css/tab3.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>profile/css/tab4.css">
    <style>
        /* Container Styling */
        .container {
            width: 70%;
            margin: 0 auto;
            padding: 20px;
            border: 2px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Tab Navigation Bar (Top Bar) */
        .tabs {
            display: flex;
            width: 100%;
            margin-bottom: 20px;
        }

        .tabs a {
            flex-grow: 1; /* Allow tabs to grow equally */
            text-align: center;
            padding: 10px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            border-radius: 5px 5px 0 0;
        }

        .tabs a:hover {
            background-color: #f1f1f1;
        }

        .tabs .active {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            width: 40%; /* Active tab takes 40% width */
        }

        .tabs .inactive {
            width: 20%; /* Inactive tabs take 20% width */
        }

        /* Tab Content */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Button Styling for previous and next */
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .nav-buttons button, #next-btn {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid #ccc;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            width: 8rem;
            text-align: left;
        }

        .arrow-btns {
            display: ;
        }


    </style>
</head>
<body>

<!-- Profile Setup Container -->
<div class="container">

    <!-- Tab Navigation Bar (Top) -->
    <div class="tabs">
        <a href="?tab=1" class="<?php echo ($tab === 1) ? 'active' : 'inactive'; ?>">Personal Details</a>
        <a href="?tab=2" class="<?php echo ($tab === 2) ? 'active' : 'inactive'; ?>">Bio</a>
        <a href="?tab=3" class="<?php echo ($tab === 3) ? 'active' : 'inactive'; ?>">Recommendations</a>
        <a href="?tab=4" class="<?php echo ($tab === 4) ? 'active' : 'inactive'; ?>">Overview</a>
    </div>


    <!-- Personal Details Tab -->
    <div id="personal-container" class="tab-content <?php echo ($tab === 1) ? 'active' : ''; ?>">
        <div class="personal-detail-container">
            <div class="left-section">
                <p>Hi, <?php echo htmlspecialchars($username); ?></p>
                <p>Welcome to Narrative. We have a few things for you to complete in order to set up your profile. If
                    you're
                    in a rush you can skip ahead and fill in these details in your settings.</p>

                <form action="<?php echo BASE_URL ?>profile/model/submit_profile.php" method="POST"
                      enctype="multipart/form-data" class="details-container">
                    <?php
                    // Fetch user details from the database
                    $user_id = $_SESSION['user_id'] ?? null;
                    $dob = null;

                    if ($user_id) {
                        $query = "SELECT dob FROM user_details WHERE user_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $dob = $row['dob']; // Store the dob value from the database
                        }
                    }
                    ?>

                    <div class="user-info">
                        <label for="dob">Date of Birth:</label>
                        <?php if ($dob): ?>
                            <!-- If dob exists, show the dob in the input field -->
                            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($dob); ?>" required><br><br>
                        <?php else: ?>
                            <!-- If dob doesn't exist, show the empty date input field -->
                            <input type="date" id="dob" name="dob" required><br><br>
                        <?php endif; ?>
                    </div>

            </div>

            <div class="right-section">


                <?php
                // Fetch user details from the database
                $user_id = $_SESSION['user_id'] ?? null;
                $profile_picture = null;

                if ($user_id) {
                    $query = "SELECT profile_picture FROM user_details WHERE user_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $profile_picture = $row['profile_picture'];
                    }
                }

                // Define the profile picture path
                $profilePicturePath = null;
                if (!empty($profile_picture)) {
                    // Check if the file exists in the directory
                    $profilePicturePath = BASE_PATH . "public/images/users/$user_id/" . $profile_picture;
                    if (!file_exists($profilePicturePath)) {
                        $profilePicturePath = null; // If the file doesn't exist, reset the path
                    }
                }

                // If a valid path exists, use that in the image tag
                $profilePictureURL = $profilePicturePath ? BASE_URL . "public/images/users/$user_id/" . htmlspecialchars($profile_picture) : null;
                ?>



                <div class="profile-section">
                    <!-- Clickable Profile Picture Display -->
                    <div id="profile-placeholder" class="profile-placeholder">
                        <!-- If there's already an image stored in the database, show it -->
                        <img id="profile-img" src="<?php echo $profilePictureURL; ?>" alt="Profile Picture" style="display: <?php echo ($profilePictureURL) ? 'block' : 'none'; ?>;">
                        <!-- Initials if there's no profile picture -->
                        <span id="profile-initial" style="display: <?php echo ($profilePictureURL) ? 'none' : 'block'; ?>;"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                        <div class="remove-overlay" id="remove-overlay" style="display: <?php echo ($profilePictureURL) ? 'block' : 'none'; ?>;"><br>X</div>
                    </div>

                    <input type="file" id="profile-pic" name="profile-pic" accept="image/*" hidden>
                    <label for="profile-pic-input" class="file-label">Profile Picture</label>
                    <input type="file" id="profile-pic-input" name="profile-pic" accept="image/*" class="file-input">

                </div>

            </div>
        </div>

        <div class="nav-buttons">
            <!--        <button type="button" id="next-btn" onclick="navigateTab('next')">Next</button>-->
            <button type="button" id="prev-btn" onclick="navigateTab('prev')" style="visibility: hidden">Previous</button>
            <button type="submit" id="next-btn">Next</button>
            </form>
        </div>

    </div>

    <!-- Bio Tab -->
    <div id="bio" class="tab-content <?php echo ($tab === 2) ? 'active' : ''; ?>">
        <h3>Bio</h3>

        <p>Tell your readers a bit about yourself: hobbies, interests, why you write...</p>

        <form action="<?php echo BASE_URL ?>profile/model/bio.php" method="POST">

            <!-- Fixed size textarea -->
            <textarea id="bio-text" name="bio-text" rows="4" cols="50" maxlength="1000"
                      placeholder="Write a short bio about yourself" oninput="countWords()"></textarea><br><br>

            <!-- Word Count Display -->
            <div id="word-count">Words: 0 / 100</div>
            <br><br>

            <div class="nav-buttons">
                <!--        <button type="button" id="next-btn" onclick="navigateTab('next')">Next</button>-->
                <button type="button" id="prev-btn" onclick="navigateTab('prev')">Previous</button>
                <button type="submit" id="next-btn">Next</button>
        </form>
    </div>
</div>

<!-- JavaScript to handle word counting -->
<script>
    // Function to count words in the textarea and update word count
    function countWords() {
        var textarea = document.getElementById('bio-text');
        var wordCountDisplay = document.getElementById('word-count');
        var saveButton = document.getElementById('save-button');

        // Split the text by spaces and filter out empty strings to count actual words
        var words = textarea.value.trim().split(/\s+/).filter(function (word) {
            return word.length > 0;
        });

        // Get the word count
        var wordCount = words.length;

        // Update the word count display
        wordCountDisplay.textContent = "Words: " + wordCount + " / 100";

        // Ensure the user can't exceed 100 words
        if (wordCount > 100) {
            // Limit the input to the first 100 words
            textarea.value = words.slice(0, 100).join(' ');
            wordCount = 100;
        }

        // Enable the save button if there are words
        saveButton.disabled = wordCount === 0;
    }
</script>


<!-- Recommendations Tab (Placeholder) -->
<div id="recommendations" class="tab-content <?php echo ($tab === 3) ? 'active' : ''; ?>">
    <h3>Select Your Interests</h3>
    <h5>(We'll recommend articles based on your reading history)</h5>
    <div class="categories">

        <?php
        $categories = ["Lifestyle", "Writing Craft", "Travel", "Reviews", "History & Culture", "Entertainment", "Business", "Technology",
            "Politics", "Science", "Sports", "Health & Fitness", "Food & Drink"];

        foreach ($categories as $category): ?>
            <button type="button" class="category-button"
                    data-category="<?php echo htmlspecialchars($category); ?>">
                <?php echo htmlspecialchars($category); ?>
            </button>
        <?php endforeach; ?>
    </div>
    <form id="category-form" method="POST" action="<?php echo BASE_URL; ?>profile/model/recommendations.php">
        <input type="hidden" name="categories" id="categories-input" value="">
<!--        <button type="submit" class="finish-btn" disabled>Finish</button>-->
<!--    </form>-->

        <div class="nav-buttons">
            <!--        <button type="button" id="next-btn" onclick="navigateTab('next')">Next</button>-->
            <button type="button" id="prev-btn" onclick="navigateTab('prev')">Previous</button>
            <button type="submit" id="next-btn">Next</button>
    </form>
</div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        let selectedCategories = [];
        const categoryButtons = document.querySelectorAll(".category-button");
        const finishButton = document.querySelector(".finish-btn");
        const categoriesInput = document.querySelector("#categories-input");
        const tabButtons = document.querySelectorAll(".tab-button");
        const tabContents = document.querySelectorAll(".tab-content");

        // Switch between tabs
        tabButtons.forEach(button => {
            button.addEventListener("click", () => {
                const tabName = button.getAttribute("data-tab");

                tabButtons.forEach(b => b.classList.remove("active"));
                button.classList.add("active");

                tabContents.forEach(content => {
                    content.classList.remove("active");
                    if (content.id === tabName) {
                        content.classList.add("active");
                    }
                });
            });
        });

        // Category selection logic
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

<div id="overview" class="tab-content <?php echo ($tab === 4) ? 'active' : ''; ?>">

    <ul class="profile-details">
        <div class="overview-image-section">
            <!-- Clickable Profile Picture Display -->
            <div id="profile-placeholder" class="profile-placeholder">
                <!-- If there's already an image stored in the database, show it -->
                <img id="profile-img" src="<?php echo $profilePictureURL; ?>" alt="Profile Picture" style="display: <?php echo ($profilePictureURL) ? 'block' : 'none'; ?>;">
                <!-- Initials if there's no profile picture -->
                <span id="profile-initial" style="display: <?php echo ($profilePictureURL) ? 'none' : 'block'; ?>;"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
            </div>

            <h3><strong><?php echo $username; ?></strong></h3>
            <p><strong>D.O.B:</strong> <?php echo !empty($dob) ? htmlspecialchars($dob) : 'Not provided'; ?></p>
        </div>


        <li class="profile-info">

            <p><strong>Bio:</strong> <?php echo !empty($bio) ? nl2br(htmlspecialchars($bio)) : 'Not provided'; ?></p>
            <p class="user-preference">Reading Preferences:</p>
            <ul class="profile-category-list">
                <?php foreach ($preferred_categories as $category): ?>
                    <li class="user-preferences"><?php echo htmlspecialchars($category); ?></li>
                <?php endforeach; ?>
            </ul>
        </li>
    </ul>


    <div class="nav-buttons">
        <!--        <button type="button" id="next-btn" onclick="navigateTab('next')">Next</button>-->
        <button type="button" id="prev-btn" onclick="navigateTab('prev')">Previous</button>
<!--        <button type="submit" id="next-btn">Next</button>-->
        <button onclick="window.location.href='<?php echo BASE_URL?>forYou.php'">Finish</button>

        </form>
    </div>
</div>

<!---->
<!--    <div class="nav-buttons">-->
<!--        <button type="button" id="prev-btn" onclick="navigateTab('prev')">Previous</button>-->
<!--    </div>-->

</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const profilePicInput = document.getElementById("profile-pic-input");
        const profileImg = document.getElementById("profile-img");
        const profileInitial = document.getElementById("profile-initial");
        const profilePlaceholder = document.getElementById("profile-placeholder");
        const removeOverlay = document.getElementById("remove-overlay");

        // Open file picker when clicking placeholder if no image is set
        profilePlaceholder.addEventListener("click", function () {
            if (profileImg.style.display === "none") {
                profilePicInput.click(); // Trigger file input click
            }
        });

        // Handle image selection
        profilePicInput.addEventListener("change", function (event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    // Set the image source and make it visible
                    profileImg.src = e.target.result;
                    profileImg.style.display = "block"; // Show the selected image
                    profileInitial.style.display = "none"; // Hide the initials
                    removeOverlay.style.display = "block"; // Show the "X" button
                };

                reader.readAsDataURL(file); // Read the image file as a data URL
            }
        });

        // Remove image when "X" is clicked
        removeOverlay.addEventListener("click", function (event) {
            event.stopPropagation(); // Prevent triggering file input when clicking "X"

            // Reset the UI
            profileImg.src = ""; // Remove image source
            profileImg.style.display = "none"; // Hide the image
            profileInitial.style.display = "block"; // Show initials
            removeOverlay.style.display = "none"; // Hide the "X" button
            profilePicInput.value = ""; // Clear file input
        });
    });

</script>
<script>
    function navigateTab(direction) {
        let currentTab = <?php echo $tab; ?>;
        if (direction === 'next' && currentTab < 4) {
            currentTab++;
        } else if (direction === 'prev' && currentTab > 1) {
            currentTab--;
        }
        window.location.href = '?tab=' + currentTab;
    }
</script>

<script src="<?php echo BASE_URL ?>profile/js/profile-details.js"></script>
<script src="<?php echo BASE_URL ?>profile/js/bio.js"></script>

</body>
</html>
