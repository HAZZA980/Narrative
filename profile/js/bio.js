function countWords() {
    var textarea = document.getElementById('bio-text');
    var wordCountDisplay = document.getElementById('word-count');
    var nextButton = document.getElementById('next-btn');

    // Get words from the textarea
    var words = textarea.value.trim().split(/\s+/).filter(word => word.length > 0);
    var wordCount = words.length;

    // Update word count display
    wordCountDisplay.textContent = "Words: " + wordCount + " / 100";

    // Change word count color
    wordCountDisplay.style.color = wordCount >= 101 ? "red" : "green";

    // Disable typing if word count reaches 100
    if (wordCount >= 100) {
        let limitedText = words.slice(0, 101).join(' ');
        textarea.value = limitedText; // Trim text to 100 words
    }

    // Disable Next button if word count is 0
    nextButton.disabled = wordCount === 0;
}

document.addEventListener("DOMContentLoaded", function () {
    var textarea = document.getElementById('bio-text');

    // Initial word count check on page load
    countWords();

    // Attach input event to textarea
    textarea.addEventListener('input', countWords);
});
