// Check if the query parameter exists
const urlParams = new URLSearchParams(window.location.search);
const searchQuery = urlParams.get('txt-search');

// If the search query exists, clear the search bar after page load
if (searchQuery) {
    window.onload = () => {
        document.getElementById('header-search-bar').value = ''; // Clear search bar input
    };
}