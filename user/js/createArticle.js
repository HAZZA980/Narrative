document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("updateForm");
    const titleInput = document.getElementById("blog-title");
    const contentInput = document.getElementById("content");
    const tagsInput = document.getElementById("tags-input");
    const hiddenTagsInput = document.getElementById("tags-hidden");
    const submitButtons = document.querySelectorAll("#submit-article");
    const suggestionsBox = document.getElementById("suggestions");
    const tagsContainer = document.getElementById("selected-tags");

    let selectedTags = [];
    let activeIndex = -1; // Tracks which suggestion is highlighted

    // Prevent form submission if fields are empty
    function validateForm(event) {
        let errors = [];

        if (titleInput.value.trim() === "") {
            errors.push("Title is required.");
        }

        if (contentInput.value.trim() === "") {
            errors.push("Content cannot be empty.");
        }

        if (selectedTags.length === 0) {
            errors.push("At least one tag must be added.");
        }

        if (errors.length > 0) {
            event.preventDefault(); // Prevent form submission
            alert(errors.join("\n")); // Show errors in an alert
            return false;
        }

        return true;
    }

    // Attach event listeners to submission buttons
    submitButtons.forEach(button => {
        button.addEventListener("click", validateForm);
    });

    // Select a tag
    function selectTag(tag) {
        if (!selectedTags.includes(tag)) {
            selectedTags.push(tag);
            updateSelectedTags();
        }
        tagsInput.value = "";
        suggestionsBox.innerHTML = "";
        suggestionsBox.style.display = "none";
        activeIndex = -1; // Reset selection
    }

    // Update selected tags in UI
    function updateSelectedTags() {
        tagsContainer.innerHTML = "";
        selectedTags.forEach(tag => {
            let tagElement = document.createElement("div");
            tagElement.classList.add("selected-tag");
            tagElement.textContent = tag;

            let removeButton = document.createElement("span");
            removeButton.textContent = " ×";
            removeButton.classList.add("remove-tag");
            removeButton.onclick = function () {
                selectedTags = selectedTags.filter(t => t !== tag);
                updateSelectedTags();
            };

            tagElement.appendChild(removeButton);
            tagsContainer.appendChild(tagElement);
        });

        hiddenTagsInput.value = selectedTags.join(", ");
    }

    // Handle tag input
    tagsInput.addEventListener("input", function () {
        const query = tagsInput.value.toLowerCase().trim();
        suggestionsBox.innerHTML = "";
        activeIndex = -1;

        if (query.length === 0) {
            suggestionsBox.style.display = "none";
            return;
        }

        let matches = [];
        for (const [category, subcategories] of Object.entries(categories)) {
            subcategories.forEach(sub => {
                if (sub.toLowerCase().includes(query) && !selectedTags.includes(sub)) {
                    matches.push(sub);
                }
            });
        }

        if (matches.length === 0) {
            suggestionsBox.style.display = "none";
        } else {
            suggestionsBox.style.display = "block";
            matches.slice(0, 10).forEach((match, index) => {
                const suggestion = document.createElement("div");
                suggestion.classList.add("suggestion-item");
                suggestion.textContent = match;
                suggestion.setAttribute("data-index", index);

                suggestion.addEventListener("click", () => selectTag(match));

                suggestionsBox.appendChild(suggestion);
            });
        }
    });

    // Handle arrow key navigation and Enter key selection
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
                selectTag(suggestions[activeIndex].textContent);
            }
        }
    });

    function updateActiveSuggestion(suggestions) {
        suggestions.forEach((suggestion, index) => {
            if (index === activeIndex) {
                suggestion.classList.add("active");
            } else {
                suggestion.classList.remove("active");
            }
        });

        // Scroll the suggestions box to keep the selected item in view
        if (activeIndex >= 0 && activeIndex < suggestions.length) {
            const activeItem = suggestions[activeIndex];
            const itemHeight = activeItem.offsetHeight;
            const containerHeight = suggestionsBox.offsetHeight;
            const scrollTop = suggestionsBox.scrollTop;

            if (activeItem.offsetTop + itemHeight > scrollTop + containerHeight) {
                suggestionsBox.scrollTop = activeItem.offsetTop + itemHeight - containerHeight;
            } else if (activeItem.offsetTop < scrollTop) {
                suggestionsBox.scrollTop = activeItem.offsetTop;
            }
        }
    }
});


document.addEventListener("DOMContentLoaded", function () {
    // Function to preview the selected image
    function previewImage(event) {
        const file = event.target.files[0];
        const previewContainer = document.getElementById("image-preview-container");
        const previewImage = document.getElementById("image-preview");
        const removeButton = document.getElementById("remove-image");

        if (file) {
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
document.querySelectorAll('button[type="submit"]').forEach(button => {
    button.addEventListener('click', () => {
        isSubmitting = true;
    });
});

// Listen for beforeunload event to prompt user
window.addEventListener('beforeunload', (event) => {
    if (isFormDirty && !isSubmitting) {
        const message = "Are you sure you want to leave? Changes have not been saved.";
        event.returnValue = message; // Standard for most browsers
        return message; // For some browsers (e.g., Safari)
    }
});
