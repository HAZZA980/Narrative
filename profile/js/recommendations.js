document.addEventListener("DOMContentLoaded", function () {
    let selectedCategories = [];
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

    // Category selection logic
    categoryButtons.forEach(button => {
        button.addEventListener("click", () => {
            const category = button.dataset.category;
            if (selectedCategories.includes(category)) {
                selectedCategories = selectedCategories.filter(c => c !== category);
                button.classList.remove("selected");
            } else {
                selectedCategories.push(category);
                button.classList.add("selected");
            }
            categoriesInput.value = JSON.stringify(selectedCategories);
            finishButton.disabled = selectedCategories.length === 0;
        });
    });
});