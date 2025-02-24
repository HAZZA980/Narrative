document.addEventListener("DOMContentLoaded", function () {
    const saveButton = document.getElementById("saveButton");
    const confirmationModal = document.getElementById("confirmationModal");
    const confirmSave = document.getElementById("confirmSave");
    const cancelSave = document.getElementById("cancelSave");
    const tagsInput = document.getElementById("tags-input");
    const suggestionsBox = document.getElementById("suggestions");
    const selectedTagsContainer = document.getElementById("selected-tags");
    const hiddenTagsInput = document.getElementById("tags-hidden");

    let selectedTags = hiddenTagsInput.value ? hiddenTagsInput.value.split(",").map(tag => tag.trim()) : [];
    let activeIndex = -1; // Tracks active suggestion

    // Show confirmation modal when clicking save
    saveButton.addEventListener("click", function (event) {
        event.preventDefault();
        confirmationModal.style.display = "block";
    });

    confirmSave.addEventListener("click", function () {
        saveButton.style.backgroundColor = "#28a745";
        saveButton.textContent = "Saving...";
        document.getElementById("updateForm").submit();
        confirmationModal.style.display = "none";
    });

    cancelSave.addEventListener("click", function () {
        confirmationModal.style.display = "none";
    });

    // Auto-expand textareas
    document.querySelectorAll("textarea.blog-content").forEach(textarea => {
        function adjustHeight(el) {
            el.style.height = "auto";
            el.style.height = `${el.scrollHeight}px`;
        }
        adjustHeight(textarea);
        textarea.addEventListener("input", () => adjustHeight(textarea));
    });

    // Format tags on blur
    tagsInput.addEventListener("blur", function () {
        tagsInput.value = tagsInput.value.split(",").map(tag => tag.trim()).join(", ");
    });

    // Render selected tags
    function renderTags() {
        selectedTagsContainer.innerHTML = "";
        selectedTags.forEach(tag => {
            if (tag.trim() !== "") {
                const tagElement = document.createElement("span");
                tagElement.classList.add("tag");
                tagElement.innerHTML = `${tag} <span class="remove-tag">&times;</span>`;
                tagElement.querySelector(".remove-tag").addEventListener("click", function () {
                    selectedTags = selectedTags.filter(t => t !== tag);
                    renderTags();
                    updateHiddenInput();
                });
                selectedTagsContainer.appendChild(tagElement);
            }
        });
        updateHiddenInput();
    }

    function updateHiddenInput() {
        hiddenTagsInput.value = selectedTags.join(", ");
    }

    // Populate suggestions
    function populateSuggestions() {
        const query = tagsInput.value.toLowerCase().trim();
        suggestionsBox.innerHTML = "";
        activeIndex = -1;

        if (query.length === 0) return;

        let matches = [];
        for (const [category, subcategories] of Object.entries(categories)) {
            subcategories.forEach(sub => {
                if (sub.toLowerCase().includes(query) && !selectedTags.includes(sub)) {
                    matches.push({ sub, category });
                }
            });
        }

        if (matches.length > 0) {
            suggestionsBox.style.display = "block";
            matches.slice(0, 10).forEach((match, index) => {
                const suggestion = document.createElement("div");
                suggestion.classList.add("suggestion-item");
                suggestion.innerHTML = `<strong>${match.sub}</strong> <small>(${match.category})</small>`;
                suggestion.setAttribute("data-index", index);
                suggestion.addEventListener("click", () => selectTag(match.sub));
                suggestionsBox.appendChild(suggestion);
            });
        }
    }

    function selectTag(tag) {
        if (!selectedTags.includes(tag)) {
            selectedTags.push(tag);
            renderTags();
        }
        tagsInput.value = "";
        clearSuggestions();
    }

    function clearSuggestions() {
        suggestionsBox.innerHTML = "";
        suggestionsBox.style.display = "none";
        activeIndex = -1;
    }

    tagsInput.addEventListener("input", populateSuggestions);

    tagsInput.addEventListener("keydown", function (event) {
        let suggestions = document.querySelectorAll(".suggestion-item");

        if (event.key === "ArrowDown") {
            event.preventDefault();
            if (suggestions.length > 0) {
                activeIndex = (activeIndex + 1) % suggestions.length;
                updateActiveSuggestion(suggestions);
            }
        } else if (event.key === "ArrowUp") {
            event.preventDefault();
            if (suggestions.length > 0) {
                activeIndex = (activeIndex - 1 + suggestions.length) % suggestions.length;
                updateActiveSuggestion(suggestions);
            }
        } else if (event.key === "Enter") {
            event.preventDefault();
            if (activeIndex >= 0 && activeIndex < suggestions.length) {
                selectTag(suggestions[activeIndex].textContent.split(" (")[0]);
            }
        } else if (event.key === "ArrowLeft" || event.key === "ArrowRight") {
            return;
        }
    });

    function updateActiveSuggestion(suggestions) {
        suggestions.forEach((suggestion, index) => {
            suggestion.classList.toggle("active", index === activeIndex);
        });
    }

    document.addEventListener("click", function (event) {
        if (!tagsInput.contains(event.target) && !suggestionsBox.contains(event.target)) {
            clearSuggestions();
        }
    });

    renderTags();
});


document.addEventListener("DOMContentLoaded", function () {
    // Function to preview the selected image
    function previewImage(event) {
        const file = event.target.files[0];
        const previewContainer = document.getElementById("image-preview-container");
        const previewImage = document.getElementById("image-preview");
        const removeButton = document.getElementById("remove-image");

        if (file) {
            // Define the allowed file types (e.g., image/jpeg, image/png)
            const allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif'];

            // Check if the selected file is of the allowed type
            if (!allowedFileTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPEG, PNG, or GIF).');
                // Clear the input value and stop further processing
                event.target.value = "";
                previewContainer.classList.remove("show");
                previewImage.src = "";
                removeButton.style.display = "none";
                return;
            }

            // Create a URL for the selected file and set it as the source for the image preview
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewContainer.classList.add("show"); // Show the preview container
            };
            reader.readAsDataURL(file);

            // Show the remove button
            removeButton.style.display = "block";

            // Add event listener to remove the image when the "X" is clicked
            removeButton.onclick = function () {
                // Clear the image preview and hide the container
                previewImage.src = "";
                previewContainer.classList.remove("show");
                // Optionally reset the input field
                document.getElementById("image").value = "";
                removeButton.style.display = "none"; // Hide the remove button
            };
        } else {
            // If no file is selected, hide the preview container
            previewContainer.classList.remove("show");
            previewImage.src = ""; // Clear the image source
            removeButton.style.display = "none"; // Hide the remove button
        }
    }

    // Check if there's an existing image URL for preloaded image
    const existingImageUrl = document.getElementById("existing-image-url").value;
    if (existingImageUrl) {
        // Show the existing image in the preview container
        const previewContainer = document.getElementById("image-preview-container");
        const previewImage = document.getElementById("image-preview");
        const removeButton = document.getElementById("remove-image");

        previewImage.src = existingImageUrl;
        previewContainer.classList.add("show"); // Make the preview container visible
        removeButton.style.display = "block"; // Show the "X" button

        // Add functionality to the "X" button to remove the image
        removeButton.onclick = function () {
            previewImage.src = ""; // Clear the image
            previewContainer.classList.remove("show"); // Hide the preview container
            document.getElementById("image").value = ""; // Reset the file input
            removeButton.style.display = "none"; // Hide the "X" button

            // Optionally, clear the existing image URL in backend data (if you need to update it)
            document.getElementById("existing-image-url").value = "";
        };
    }

    // Attach the previewImage function to the file input's onchange event
    const imageInput = document.getElementById("image");
    if (imageInput) {
        imageInput.addEventListener("change", previewImage);
    }
});



// Track changes in the form fields
let isFormDirty = false;

// Flag to prevent beforeunload popup when certain buttons are clicked
let isSubmitting = false;

const formElements = [
    document.getElementById('blog-title'),
    document.getElementById('content'),
    document.getElementById('tags-input'),
    document.getElementById('image')
];

// Function to set isFormDirty flag when changes occur
function markFormAsDirty() {
    isFormDirty = true;
}

// Add event listeners to form elements
formElements.forEach(element => {
    if (element) {
        element.addEventListener('input', markFormAsDirty);
    }
});

// If the image is selected or removed, consider the form as dirty
document.getElementById('image').addEventListener('change', markFormAsDirty);
document.getElementById('remove-image').addEventListener('click', () => {
    isFormDirty = true; // Image removal counts as a change
});

// Prevent beforeunload prompt if a submit button is clicked
document.getElementById('saveButton')?.addEventListener('click', () => {
    isSubmitting = true;
});

// Prevent beforeunload prompt when "Publish/Change to Private" button is clicked
document.querySelector('#toggle-private-btn')?.addEventListener('click', () => {
    isSubmitting = true;
});

// Prevent beforeunload prompt if delete button is clicked
document.querySelector('.delete-link')?.addEventListener('click', () => {
    isSubmitting = true;
});

// Listen for form submission for "Publish/Change to Private"
const togglePrivateForm = document.querySelector('form[action=""]');
if (togglePrivateForm) {
    togglePrivateForm.addEventListener('submit', () => {
        isSubmitting = true;
    });
}

// Listen for beforeunload event to prompt user
window.addEventListener('beforeunload', (event) => {
    if (isFormDirty && !isSubmitting) {
        const message = "Are you sure you want to leave? Changes have not been saved.";
        event.returnValue = message; // Standard for most browsers
        return message; // For some browsers (e.g., Safari)
    }
});
