document.addEventListener("DOMContentLoaded", function () {

    const tagsInput = document.getElementById("tags-input");
    const suggestionsBox = document.getElementById("suggestions");
    const selectedTagsContainer = document.getElementById("selected-tags");
    const hiddenTagsInput = document.getElementById("tags-hidden");
    let selectedTags = [];

    function selectTag(tag) {
        if (!selectedTags.includes(tag)) {
            selectedTags.push(tag);
            updateSelectedTags();
        }
        tagsInput.value = "";
        suggestionsBox.innerHTML = "";
    }

    function updateSelectedTags() {
        selectedTagsContainer.innerHTML = "";
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
            selectedTagsContainer.appendChild(tagElement);
        });
        hiddenTagsInput.value = selectedTags.join(", ");
    }

    tagsInput.addEventListener("input", function () {
        const query = tagsInput.value.toLowerCase().trim();
        suggestionsBox.innerHTML = "";

        if (query.length === 0) return;

        let matches = [];
        for (const [category, subcategories] of Object.entries(categories)) {
            subcategories.forEach(sub => {
                if (sub.toLowerCase().includes(query) && !selectedTags.includes(sub)) {
                    matches.push(sub);
                }
            });
        }

        matches.slice(0, 10).forEach(match => {
            const suggestion = document.createElement("div");
            suggestion.classList.add("suggestion-item");
            suggestion.textContent = match;
            suggestion.addEventListener("click", () => selectTag(match));
            suggestionsBox.appendChild(suggestion);
        });
    });
});