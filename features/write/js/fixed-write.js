document.addEventListener("DOMContentLoaded", function () {
    const writeButton = document.querySelector(".aside-writing-link");
    const footer = document.querySelector("footer");

    function adjustWriteButton() {
        const footerRect = footer.getBoundingClientRect();
        const windowHeight = window.innerHeight;

        if (footerRect.top < windowHeight) {
            const offset = windowHeight - footerRect.top;
            writeButton.style.bottom = `${20 + offset}px`; // Moves up with the footer
        } else {
            writeButton.style.bottom = '20px'; // Stays fixed at bottom right
        }
    }

    window.addEventListener("scroll", adjustWriteButton);
    adjustWriteButton(); // Initial check
});