// JavaScript to handle filter button click and overlay visibility
document.getElementById('filter-button').addEventListener('click', function (e) {
    e.preventDefault();
    document.getElementById('filter-overlay').style.display = 'flex';
});

// Close filter overlay when clicking close button
document.getElementById('close-filter').addEventListener('click', function () {
    document.getElementById('filter-overlay').style.display = 'none';
});

// Apply the selected filters
document.getElementById('apply-filters').addEventListener('click', function () {
    const selectedCategories = [];
    const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]:checked');
    categoryCheckboxes.forEach(function (checkbox) {
        selectedCategories.push(checkbox.value);
    });

    const fromDate = document.getElementById('from-date').value;
    const toDate = document.getElementById('to-date').value;

    // Create the filter query string
    let filterQuery = '';
    if (selectedCategories.length > 0) {
        filterQuery += `&categories=${selectedCategories.join(',')}`;
    }
    if (fromDate) {
        filterQuery += `&from_date=${fromDate}`;
    }
    if (toDate) {
        filterQuery += `&to_date=${toDate}`;
    }

    // Redirect with the new filters
    window.location.href = window.location.pathname + '?tab=<?php echo htmlspecialchars($tab); ?>' + filterQuery;
});