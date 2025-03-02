document.addEventListener("DOMContentLoaded", function () {
    let selectedCategories = [];
    const categoryButtons = document.querySelectorAll(".category-button");
    const finishButton = document.querySelector(".next-btn");
    const categoriesInput = document.querySelector("#categories-input");
    const categoryForm = document.querySelector("#category-form");

    const errorMessage = document.createElement('p');
    errorMessage.textContent = "Please select at least one category.";
    errorMessage.style.color = 'red';
    errorMessage.style.display = 'none';
    categoryForm.appendChild(errorMessage);

    // Function to update categories
    function updateCategories() {
        categoriesInput.value = JSON.stringify(selectedCategories);
        finishButton.disabled = selectedCategories.length === 0;
        errorMessage.style.display = selectedCategories.length === 0 ? 'block' : 'none';
    }

    // Category selection logic (Fixed single-click issue)
    categoryButtons.forEach(button => {
        button.addEventListener("click", function () {
            const category = this.dataset.category;
            const isSelected = this.classList.toggle("selected");

            if (isSelected) {
                selectedCategories.push(category);
            } else {
                selectedCategories = selectedCategories.filter(c => c !== category);
            }

            updateCategories();
        });
    });

    // Handle form submission
    categoryForm.addEventListener("submit", function (event) {
        if (selectedCategories.length === 0) {
            event.preventDefault();
            errorMessage.style.display = 'block';
        }
    });

    // Ensure previously selected categories remain selected (if applicable)
    document.querySelectorAll(".category-button.selected").forEach(button => {
        selectedCategories.push(button.dataset.category);
    });

    updateCategories();
});
