<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
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
    <title>Admin Overview</title>
    <style>
        .feed-outer-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        /* Modal Background */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.3); /* Subtle dark background */
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        /* Modal Content Box */
        .modal-content {
            top: 25%;
            left: 40%;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            width: 400px; /* Fixed width for a small modal */
            max-width: 90%; /* Ensures it is responsive */
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Modal Title */
        .modal-content h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
        }

        /* Modal Action Buttons */
        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px; /* Spacing between the buttons */
        }

        .modal-actions button {
            padding: 12px 25px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }


        /* Update Button Specific Styling */
        .btn-confirm {
            background: linear-gradient(to bottom, forestgreen, darkgreen); /* Darker red gradient on hover */
            box-shadow: 0 5px 10px rgba(178, 34, 34, 0.3); /* Red shadow on hover */
            color: white;
        }

        .btn-confirm:hover {
            background-color: darkgreen; /* Slightly darker green */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Cancel Button Specific Styling */
        .btn-cancel {
            background: linear-gradient(to bottom, #8B0000, #660000); /* Darker red gradient on hover */
            box-shadow: 0 5px 10px rgba(178, 34, 34, 0.3); /* Red shadow on hover */
            color: white;
            margin-left: 10px;
        }

        .btn-cancel:hover {
            background-color: darkred; /* Slightly darker red */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .feed-outer-container {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .top-container {
            width: 73%;
            background: linear-gradient(to bottom, darkgrey 0%, white 40px, white 100%);

        }

        .admin-insights {
            margin: 2rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .admin-insights p {
            margin: 0.5rem 0;
        }

        .admin-insights a {
            text-decoration: none;
            color: #0056b3;
        }

        .admin-insights a:hover {
            text-decoration: underline;
        }

        h2 {
            text-align: center;
            color: #212529;
            margin-top: 20px;
            margin-bottom: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .users-table-container {
            width: 100%;
            margin: auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            padding: 20px;
        }

        /* Table Layout */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 1rem;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Table Headers */
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        th {
            background-color: #0056b3;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        td {
            color: #495057;
        }

        td:last-child, td:nth-last-child(2) {
            text-align: center;
        }

        /* Zebra Striping */
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Hover Effect for Table Rows */
        tr:hover {
            background-color: #e9ecef;
            cursor: default; /* Make sure the cursor isn't pointer unless there's a link/button */
            transition: background-color 0.3s ease;
        }

        /* Avoid entire row becoming clickable when clicking checkboxes */
        form {
            display: inline-block;
        }

        /* Freeze and Admin Checkboxes */
        input[type="checkbox"] {
            margin: 0; /* Avoid extra space around checkboxes */
            cursor: pointer;
        }

        /* Buttons - Delete and Freeze */
        button.delete-btn, .freeze-btn {
            padding: 6px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: #ffffff;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .freeze-btn {
            background-color: #f39c12;
            color: #ffffff;
        }

        .freeze-btn:hover {
            background-color: #e67e22;
        }

        /* Prevent unwanted hover effects on checkboxes */
        input[type="checkbox"]:hover {
            cursor: pointer; /* Ensures only checkboxes change the cursor */
        }

        /* Ensuring the whole row isn't treated as clickable */
        tr td button, tr td input[type="checkbox"] {
            cursor: pointer; /* Only buttons and checkboxes should be clickable, not the entire row */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 10px;
            }
        }

    </style>
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