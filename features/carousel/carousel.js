function scrollCarousel(direction) {
    const carousel = document.querySelector('.carousel-grid');
    const itemWidth = document.querySelector('.carousel-grid-item').offsetWidth + 15; // Include gap
    carousel.scrollBy({ left: direction * itemWidth, behavior: 'smooth' });
}