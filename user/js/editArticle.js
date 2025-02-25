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
    const previewContainer = document.getElementById("image-preview-container");
    const previewImage = document.getElementById("image-preview");
    const removeButton = document.getElementById("remove-image");
    const imageInput = document.getElementById("image");
    const removeImageInput = document.getElementById("remove_image"); // Hidden input field

    // Function to preview the selected image
    function previewImageHandler(event) {
        const file = event.target.files[0];

        if (file) {
            const allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!allowedFileTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPEG, PNG, GIF, or WEBP).');
                event.target.value = "";
                previewContainer.classList.remove("show");
                previewImage.src = "";
                removeButton.style.display = "none";
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewContainer.classList.add("show");
            };
            reader.readAsDataURL(file);

            removeButton.style.display = "block";
            removeImageInput.value = "0"; // Reset removal flag
        } else {
            previewContainer.classList.remove("show");
            previewImage.src = "";
            removeButton.style.display = "none";
        }
    }

    // Function to handle image removal
    function removeImageHandler() {
        previewImage.src = "";
        previewContainer.classList.remove("show");
        imageInput.value = ""; // Reset file input
        removeButton.style.display = "none";

        // Set the hidden input value to indicate removal
        removeImageInput.value = "1"; // 1 means image should be removed
    }

    // Attach the preview function to the file input's onchange event
    if (imageInput) {
        imageInput.addEventListener("change", previewImageHandler);
    }

    // Attach the remove function to the "X" button
    if (removeButton) {
        removeButton.addEventListener("click", removeImageHandler);
    }

    // Preload existing image
    const existingImageUrl = document.getElementById("existing-image-url")?.value;
    if (existingImageUrl) {
        previewImage.src = existingImageUrl;
        previewContainer.classList.add("show");
        removeButton.style.display = "block";
    }

    // Track changes in the form fields
    let isFormDirty = false;
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
});







