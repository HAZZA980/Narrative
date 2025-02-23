document.addEventListener("DOMContentLoaded", function () {

    const categoryButtons = document.querySelectorAll(".category-button");
    const finishButton = document.querySelector(".finish-btn");
    const categoriesInput = document.querySelector("#categories-input");
    const tabButtons = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");

    // Switch between tabs
    tabButtons.forEach(button => {
        button.addEventListener("click", () => {
            const tabName = button.getAttribute("data-tab");

            tabButtons.forEach(b => b.classList.remove("active"));
            button.classList.add("active");

            tabContents.forEach(content => {
                content.classList.remove("active");
                if (content.id === tabName) {
                    content.classList.add("active");
                }
            });
        });
    });

    // Apply the 'selected' class to buttons that are pre-selected
    categoryButtons.forEach(button => {
        const category = button.dataset.category;
        if (selectedCategories.includes(category)) {
            button.classList.add("selected");
        }
    });

    // Category selection logic
    categoryButtons.forEach(button => {
        button.addEventListener("click", () => {
            const category = button.dataset.category;
            if (selectedCategories.includes(category)) {
                // Unselect category
                selectedCategories = selectedCategories.filter(c => c !== category);
                button.classList.remove("selected");
            } else {
                // Select category
                selectedCategories.push(category);
                button.classList.add("selected");
            }
            categoriesInput.value = JSON.stringify(selectedCategories);
            finishButton.disabled = selectedCategories.length === 0;
        });
    });
});
