// Save scroll position in localStorage when the user scrolls
window.addEventListener('scroll', function () {
    localStorage.setItem('scrollPosition', window.scrollY);
});

// When the page is loaded, scroll back to the last saved position
window.addEventListener('load', function () {
    const scrollPosition = localStorage.getItem('scrollPosition');
    if (scrollPosition) {
        window.scrollTo(0, scrollPosition);
        localStorage.removeItem('scrollPosition'); // Clear the saved scroll position after loading
    }
});
