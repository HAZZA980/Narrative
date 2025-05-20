<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'account/account-masthead.php';
include BASE_PATH . "admin/model/user_management.php";
include BASE_PATH . "admin/view/delete-user-modal.html";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo BASE_URL?>user/css/delete-article-modal.css">
    <link rel="stylesheet" href="<?php echo BASE_URL;?>admin/css/user_management.css">
    <title>Admin Overview</title>
</head>
<body>
<?php include BASE_PATH . "layouts/mastheads/articles/account-masthead.php"; ?>
<div class="feed-outer-container">
    <div class="top-container">
        <h2>Admin: User Management</h2>
        <div class="users-table-container">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Admin User</th>
                    <th>No. of Articles Written</th>
                    <th>Date of Last Updated Article</th>
                    <th>User Since</th>
                    <th>Freeze Account</th>
                    <th>Delete Account</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $query = "SELECT u.user_id, u.username, u.freeze_user, u.isAdmin, u.created_at AS user_since,
                            COUNT(b.user_id) AS article_count,
                            MAX(b.LastUpdated) AS last_updated_article
                            FROM users u
                            LEFT JOIN 
                            tbl_blogs b ON u.user_id = b.user_id
                            GROUP BY 
                            u.user_id, u.username, u.freeze_user, u.created_at
                            ";

                $result = $conn->query($query);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo BASE_URL?>feed.php?username=<?php echo urlencode($row['username']); ?>">
                                    <?php echo htmlspecialchars($row['username']); ?>
                                </a>
                            </td>
                            <td>
                                <form id="admin-user-form-<?php echo $row['user_id']; ?>" method="POST">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>"/>
                                    <input type="checkbox"
                                           class="admin-checkbox"
                                           id="admin-user-<?php echo $row['user_id']; ?>"
                                           data-user-id="<?php echo $row['user_id']; ?>"
                                        <?php echo ($row['isAdmin'] == 1 ? 'checked' : ''); ?> />
                                </form>
                            </td>

                            <td><?php echo $row['article_count']; ?></td>
                            <td><?php echo $row['last_updated_article'] ? htmlspecialchars($row['last_updated_article']) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($row['user_since']); ?></td>

                            <td>
                                <form id="freeze-user-form-<?php echo $row['user_id']; ?>" method="POST">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>"/>
                                    <input type="checkbox"
                                           class="freeze-checkbox"
                                           id="freeze-user-<?php echo $row['user_id']; ?>"
                                           data-user-id="<?php echo $row['user_id']; ?>"
                                        <?php echo ($row['freeze_user'] == 1 ? 'checked' : ''); ?>
                                    />

                                </form>
                            </td>

                            <td>
                                <form id="delete-user-form-<?php echo $row['user_id']; ?>" action="<?php echo BASE_URL?>admin/model/deleteUserManagement.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                    <button type="button" class="delete-btn" onclick="openDeleteModal(<?php echo $row['user_id']; ?>)">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6'>No users found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".freeze-checkbox").forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                let userId = this.getAttribute("data-user-id");
                let freezeStatus = this.checked ? 1 : 0;

                let formData = new FormData();
                formData.append("user_id", userId);
                formData.append("freeze_status", freezeStatus);

                let xhr = new XMLHttpRequest();
                xhr.open("POST", "<?php echo BASE_URL ?>admin/model/freezeUserManagement.php", true);

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            let response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                showPopup(response.message);
                            } else {
                                console.error("Error:", response.message);
                            }
                        } catch (e) {
                            console.error("Invalid JSON response:", xhr.responseText);
                        }
                    }
                };

                xhr.send(formData);
            });
        });
    });



    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".admin-checkbox").forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                let userId = this.getAttribute("data-user-id");
                let adminStatus = this.checked ? 1 : 0; // Correct variable name

                let formData = new FormData();
                formData.append("user_id", userId);
                formData.append("isAdmin", adminStatus); // Ensure the name matches the backend

                let xhr = new XMLHttpRequest();
                xhr.open("POST", "<?php echo BASE_URL ?>admin/model/isAdminUserManagement.php", true);

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            let response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                // Update the checkbox state and table dynamically
                                showPopup(response.message);

                                // Optionally change text or any other element related to the checkbox
                                if (adminStatus === 1) {
                                    // If checked, you can add additional logic here if necessary
                                } else {
                                    // If unchecked, add logic here if needed
                                }
                            } else {
                                console.error("Error:", response.message);
                            }
                        } catch (e) {
                            console.error("Invalid JSON response:", xhr.responseText);
                        }
                    }
                };

                xhr.send(formData);
            });
        });
    });



    function showPopup(message) {
        var existingPopup = document.querySelector(".popup");
        if (existingPopup) existingPopup.remove();

        var popup = document.createElement("div");
        popup.classList.add("popup");
        popup.innerHTML = `<p>${message}</p>`;
        document.body.appendChild(popup);

        setTimeout(() => {
            popup.classList.add("visible");
        }, 50);

        setTimeout(() => {
            popup.classList.remove("visible");
            setTimeout(() => popup.remove(), 500);
        }, 3000);
    }

    function openDeleteModal(userId) {
        // Show modal
        const modal = document.getElementById("deleteModal");
        modal.style.display = "block";

        // Set up the delete confirmation
        document.getElementById("confirmDelete").onclick = function() {
            // Trigger form submission for deletion
            document.getElementById("delete-user-form-" + userId).submit();
        };

        // Cancel deletion
        document.getElementById("cancelDelete").onclick = function() {
            modal.style.display = "none"; // Hide modal
        };
    }

    // Close modal if clicked outside of the modal content
    window.onclick = function(event) {
        const modal = document.getElementById("deleteModal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
</script>
</body>
</html>