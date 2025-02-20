const saveButton = document.getElementById('saveButton');
const confirmationModal = document.getElementById('confirmationModal');
const confirmSave = document.getElementById('confirmSave');
const cancelSave = document.getElementById('cancelSave');

// Show modal when save button is clicked
saveButton.addEventListener('click', function (event) {
    event.preventDefault();  // Prevent the form from submitting immediately
    confirmationModal.style.display = 'block';  // Show the modal
});

// If user confirms saving, submit the form and redirect
confirmSave.addEventListener('click', function () {
    // Change the button color to green and update text to 'Saving...'
    saveButton.style.backgroundColor = '#28a745';
    saveButton.textContent = 'Saving...';

    // Proceed with the form submission by clicking the hidden submit button
    document.getElementById('updateForm').submit();

    // Hide the modal after confirmation
    confirmationModal.style.display = 'none';
});

// If user cancels, hide the modal
cancelSave.addEventListener('click', function () {
    confirmationModal.style.display = 'none';  // Hide the modal
});


document.addEventListener('DOMContentLoaded', () => {
    const textareas = document.querySelectorAll('textarea.blog-content');

    function adjustHeight(el) {
        // Temporarily set the height to auto to reset it
        el.style.height = 'auto';

        // Set the height based on the scrollHeight
        el.style.height = `${el.scrollHeight}px`;
    }

    // For each textarea, adjust height dynamically
    textareas.forEach(textarea => {
        // Adjust height initially if content is already present
        adjustHeight(textarea);

        // Adjust height on input (while typing)
        textarea.addEventListener('input', () => adjustHeight(textarea));
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const tagsInput = document.getElementById('tags');

    tagsInput.addEventListener('blur', function () {
        // Trim and format user input
        let formattedTags = tagsInput.value.split(',').map(tag => tag.trim()).join(', ');
        tagsInput.value = formattedTags;
    });
});


document.addEventListener("DOMContentLoaded", function () {
    const tagsInput = document.getElementById("tags-input");
    const suggestionsBox = document.getElementById("suggestions");
    const selectedTagsContainer = document.getElementById("selected-tags");
    const hiddenTagsInput = document.getElementById("tags-hidden");

    let selectedTags = hiddenTagsInput.value ? hiddenTagsInput.value.split(",").map(tag => tag.trim()) : [];

    // Function to render selected tags
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

    // Function to update hidden input value
    function updateHiddenInput() {
        hiddenTagsInput.value = selectedTags.join(", ");
    }

    // Show suggestions when typing
    tagsInput.addEventListener("input", function () {
        const query = tagsInput.value.toLowerCase().trim();
        suggestionsBox.innerHTML = "";

        if (query.length === 0) return;

        let matches = [];
        for (const [category, subcategories] of Object.entries(categories)) {
            subcategories.forEach(sub => {
                if (sub.toLowerCase().includes(query) && !selectedTags.includes(sub)) {
                    matches.push({sub, category});
                }
            });
        }

        // Display suggestions
        matches.slice(0, 10).forEach(match => {
            const suggestion = document.createElement("div");
            suggestion.classList.add("suggestion-item");
            suggestion.innerHTML = `<strong>${match.sub}</strong> <small>(${match.category})</small>`;
            suggestion.addEventListener("click", () => selectTag(match.sub));
            suggestionsBox.appendChild(suggestion);
        });
    });

    // Function to select a tag
    function selectTag(tag) {
        if (!selectedTags.includes(tag)) {
            selectedTags.push(tag);
            renderTags();
        }
        tagsInput.value = "";
        suggestionsBox.innerHTML = "";
    }

    // Render existing tags on load
    renderTags();
});