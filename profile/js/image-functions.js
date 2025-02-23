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
