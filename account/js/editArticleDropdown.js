document.addEventListener('DOMContentLoaded', () => {
    // Add event listeners for dropdown toggling
    const editIcons = document.querySelectorAll('.edit-menu-icon');
    const menus = document.querySelectorAll('.edit-menu');

    // Show the menu when the three dots are clicked
    editIcons.forEach((icon, index) => {
        const menu = menus[index];

        icon.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent click from closing menu immediately
            // Toggle the visibility of the menu
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });
    });

    // Close menu when clicking outside of the menu
    document.addEventListener('click', () => {
        menus.forEach(menu => {
            menu.style.display = 'none';
        });
    });

    // Prevent click on menu from closing it immediately
    menus.forEach(menu => {
        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });
});

// When the page loads or reloads, scroll to the flex-container
window.onload = function () {
    const container = document.getElementById('feed-tabs');
    if (container) {
        container.scrollIntoView({behavior: 'smooth'});
    }
};