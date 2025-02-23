<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . "account/account-masthead.php";
include BASE_PATH . "settings/model/bio.php";
include BASE_PATH . 'features/write/write-icon-fixed.php';


// Determine which section to show
$section = $_GET['profileSettings'] ?? 'profile_picture';  // Default to 'profile_picture' if not set

// Include the appropriate logic files
if ($section === 'bio') {
    include BASE_PATH . "settings/model/bio.php";
} elseif ($section === 'media_links') {
    include BASE_PATH . "settings/model/media_links.php";
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
    <link rel="stylesheet" href="<?php echo BASE_URL ?>profile/css/tab1.css">
    <title>Profile Settings</title>

    <style>
        .profile-placeholder {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f3f3f3;
            cursor: pointer;
        }

        .profile-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }

        .remove-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            font-size: 20px;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .remove-overlay:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }


        #bio {
            width: 100%; /* Full width */
            max-width: 100%; /* Ensure it doesn't exceed container width */
            height: 150px; /* Fixed height */
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: none; /* Disable resizing */
            box-sizing: border-box; /* Includes padding in width calculation */
        }


        button[type="submit"]:hover {
            background-color: #0056b3;
        }

    </style>
    <style>

    </style>
</head>
<body>

<div class="settings-outer-container">
    <div class="settings-inner-container">

        <div class="settings-container">
            <!-- Sidebar Menu -->
            <nav class="settings-sidebar">
                <h3 class="settings-section-title">Profile Settings</h3>
                <ul class="settings-menu">
                    <li><a href="?profileSettings=profile_picture"
                           class="<?= $section === 'profile_picture' ? 'active' : '' ?>">Update Profile Picture</a></li>
                    <li><a href="?profileSettings=bio" class="<?= $section === 'bio' ? 'active' : '' ?>">Update
                            Biography</a></li>
                    <li><a href="?profileSettings=media_links"
                           class="<?= $section === 'media_links' ? 'active' : '' ?>">Update Media Links</a></li>
                </ul>
            </nav>

            <!-- Content Section -->
            <main class="settings-content">

                <!-- Profile Picture Update -->
                <?php if ($section === 'profile_picture'): ?>

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


                <section class="settings-section">
                    <form method="post" action="model/profile_picture.php" class="settings-form"
                          enctype="multipart/form-data">
                        <div class="profile-section">
                            <!-- Clickable Profile Picture Display -->
                            <div id="profile-placeholder" class="profile-placeholder">
                                <img id="profile-img" src="<?php echo $profilePictureURL; ?>" alt="Profile Picture"
                                     style="display: <?php echo ($profilePictureURL) ? 'block' : 'none'; ?>;">
                                <span id="profile-initial"
                                      style="display: <?php echo ($profilePictureURL) ? 'none' : 'block'; ?>;"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                                <div class="remove-overlay" id="remove-overlay" style="display: none;">X</div>
                            </div>

                            <input type="file" id="profile-pic" name="profile-pic" accept="image/*" hidden>
                            <label for="profile-pic" class="file-label">Change Profile Picture</label>
                        </div>

                        <button type="submit" name="update_profile_picture">Update</button>
                    </form>
                </section>


                <?php elseif ($section === 'bio'): ?>
                <!-- Biography Update -->
                <section class="settings-section">
                    <h2>Update Biography</h2>
                    <?php
                    // Fetch current bio from database
                    $user_id = $_SESSION['user_id'] ?? null;
                    $current_bio = ''; // Default to empty

                    if ($user_id) {
                        $query = "SELECT bio FROM user_details WHERE user_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $current_bio = $row['bio'];
                        }
                    }
                    ?>

                    <?php if (isset($_SESSION['bio_success'])): ?>
                        <p class="success-message"><?= $_SESSION['bio_success']; unset($_SESSION['bio_success']); ?></p>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['bio_error'])): ?>
                        <p class="error-message"><?= $_SESSION['bio_error']; unset($_SESSION['bio_error']); ?></p>
                    <?php endif; ?>

                    <form method="post" action="<?php echo BASE_URL?>settings/model/bio.php" class="settings-form">
                        <label for="bio">New Bio:</label>
                        <textarea id="bio" name="bio" rows="4" required><?php echo htmlspecialchars($current_bio); ?></textarea>
                        <button type="submit" name="update_bio">Update</button>
                    </form>

                </section>


                <!-- Media Links Update -->
                <?php elseif ($section === 'media_links'): ?>
                <section class="settings-section">
                    <h2>Update Media Links</h2>
                    <?php if (isset($_SESSION['media_success'])): ?>
                    <p class="success-message"><?= $_SESSION['media_success'];
                        unset($_SESSION['media_success']); ?></p>
                    <?php endif; ?>
                        <?php if (isset($_SESSION['media_error'])): ?>
                    <p class="error-message"><?= $_SESSION['media_error'];
                        unset($_SESSION['media_error']); ?></p>
                    <?php endif; ?>

                    <form method="post" class="settings-form">
                        <label for="twitter">Twitter:</label>
                        <input type="url" id="twitter" name="twitter">

                        <label for="instagram">Instagram:</label>
                        <input type="url" id="instagram" name="instagram">

                        <label for="linkedin">LinkedIn:</label>
                        <input type="url" id="linkedin" name="linkedin">

                        <button type="submit" name="update_media_links">Update</button>
                    </form>
                </section>

                <?php endif; ?>
            </main>
        </div>

    </div>
</div>
<script src="<?php echo BASE_URL ?>settings/js/bio.js"></script>

<script src="<?php echo BASE_URL ?>settings/js/profileImage.js"></script>

</body>
</html>