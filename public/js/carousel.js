function scrollCarousel(direction) {
    const carousel = document.querySelector('.carousel');

    // Calculate the width of one item including the gap
    const itemWidth = (carousel.offsetWidth / 5); // width of 1 item + gap
    const scrollAmount = itemWidth * 5; // Scroll by 5 items

    if (direction === 'right') {
        carousel.scrollLeft += scrollAmount;
    } else if (direction === 'left') {
        carousel.scrollLeft -= scrollAmount;
    }
}