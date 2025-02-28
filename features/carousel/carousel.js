document.addEventListener("DOMContentLoaded", function () {
    const carousel = document.querySelector('.carousel-grid');
    const leftArrow = document.querySelector('.carousel-button-left');
    const rightArrow = document.querySelector('.carousel-button-right');

    function scrollCarousel(direction) {
        const itemWidth = document.querySelector('.carousel-grid-item').offsetWidth + 15; // Include gap
        carousel.scrollBy({ left: direction * itemWidth, behavior: 'smooth' });
    }

    // Ensure event listeners are properly set
    if (leftArrow && rightArrow) {
        leftArrow.addEventListener("click", () => scrollCarousel(-1));
        rightArrow.addEventListener("click", () => scrollCarousel(1));
    }
});
