<?php

// Handle the form submission for changing the DOB
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dob'])) {
    // Get the user ID from session
    $user_id = $_SESSION['user_id'];

    // Get the new Date of Birth from the form
    $dob = $_POST['dob'];

    // Validate the date of birth (for example, it shouldn't be empty)
    if (empty($dob)) {
        $_SESSION['dob_error'] = "Date of birth is required.";
        header("Location: ?accountManagement=dob");  // Stay on the same page
        exit;
    }

    // Update the DOB in the user_details table
    $query = "UPDATE user_details SET dob = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        // Bind parameters and execute the query
        $stmt->bind_param("si", $dob, $user_id);

        if ($stmt->execute()) {
            // Success
            $_SESSION['dob_success'] = "Date of birth updated successfully.";
        } else {
            // Error during update
            $_SESSION['dob_error'] = "Error updating the date of birth.";
        }

        // Close the statement
        $stmt->close();
    } else {
        // Query preparation failed
        $_SESSION['dob_error'] = "Error preparing the query.";
    }

    // Redirect back to the settings page
    header("Location: ?accountManagement=dob");
    exit;
}

?>