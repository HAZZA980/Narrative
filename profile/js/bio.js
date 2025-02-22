// Function to count words in the textarea and disable the button if the word limit is reached
function countWords() {
    var textarea = document.getElementById('bio-text');
    var wordCountDisplay = document.getElementById('word-count');
    var saveButton = document.getElementById('save-button');

    // Split the text by spaces and filter out empty strings to get word count
    var words = textarea.value.trim().split(/\s+/).filter(function(word) {
        return word.length > 0;
    });

    // Count the number of words
    var wordCount = words.length;

    // Update the word count display
    wordCountDisplay.textContent = "Words: " + wordCount + " / 100";

    // Enable or disable the button based on word count
    if (wordCount > 100) {
        // Limit the input to 100 words by trimming the input
        textarea.value = words.slice(0, 100).join(' ');
        wordCount = 100;
    }

    // Enable the save button if the word count is greater than 0
    saveButton.disabled = wordCount === 0;
}