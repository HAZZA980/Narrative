document.addEventListener("DOMContentLoaded", function () {
    const profilePlaceholder = document.getElementById("profile-placeholder");
    const profileImg = document.getElementById("profile-img");
    const profileInitial = document.getElementById("profile-initial");
    const fileInput = document.getElementById("profile-pic");
    const removeOverlay = document.getElementById("remove-overlay");
    const form = document.querySelector(".settings-form");
    const submitButton = form.querySelector('button[type="submit"]'); // Get the submit button

    // Create hidden input for image removal
    let removeImageInput = document.createElement("input");
    removeImageInput.type = "hidden";
    removeImageInput.name = "remove_image";
    removeImageInput.value = "false"; // Default to false
    form.appendChild(removeImageInput);

    // Initially disable the submit button
    submitButton.disabled = true;

    // Function to enable the submit button
    function enableSubmitButton() {
        submitButton.disabled = false;
    }

    // Show overlay on hover if an image exists
    profilePlaceholder.addEventListener("mouseenter", function () {
        if (profileImg.src && profileImg.style.display !== "none") {
            removeOverlay.style.display = "flex";
            profileImg.style.opacity = "0.5";
        }
    });

    // Hide overlay when mouse leaves
    profilePlaceholder.addEventListener("mouseleave", function () {
        removeOverlay.style.display = "none";
        profileImg.style.opacity = "1";
    });

    // Remove profile picture when "X" is clicked
    removeOverlay.addEventListener("click", function (event) {
        event.stopPropagation(); // Prevent triggering the file picker
        profileImg.style.display = "none";
        profileImg.src = "";
        profileInitial.style.display = "block";
        removeOverlay.style.display = "none";
        fileInput.value = ""; // Reset file input
        removeImageInput.value = "true"; // Mark image for deletion
        enableSubmitButton(); // Enable the submit button after interaction
    });

    // Open file picker when clicking the placeholder (only if no image exists)
    profilePlaceholder.addEventListener("click", function () {
        if (!profileImg.src || profileImg.style.display === "none") {
            fileInput.click();
        }
    });

    // Preview new image when selected
    fileInput.addEventListener("change", function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                profileImg.src = e.target.result;
                profileImg.style.display = "block";
                profileInitial.style.display = "none";
                removeImageInput.value = "false"; // User is adding an image, so don't remove
                enableSubmitButton(); // Enable the submit button after image selection
            };
            reader.readAsDataURL(file);
        }
    });
});
