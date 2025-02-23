document.addEventListener("DOMContentLoaded", function () {
    const bioTextArea = document.getElementById("bio"); // Bio textarea
    const submitButton = document.querySelector(".settings-form button[type='submit']"); // Submit button
    const wordCountDisplay = document.createElement("p"); // Element to show the word count
    const maxWords = 100; // Max allowed words

    // Append the word count display below the textarea
    bioTextArea.parentNode.appendChild(wordCountDisplay);
    wordCountDisplay.style.fontSize = "14px";
    wordCountDisplay.style.color = "#555";
    wordCountDisplay.style.marginTop = "5px";

    // Initially disable the submit button
    submitButton.disabled = true;

    // Function to count words and update the word count display
    function countWords() {
        const bioText = bioTextArea.value.trim();
        const words = bioText.split(/\s+/).filter(function (word) {
            return word.length > 0;
        });

        // Limit to 100 words
        const wordCount = words.length;
        if (wordCount > maxWords) {
            words.length = maxWords; // Truncate to 100 words
            bioTextArea.value = words.join(" "); // Update textarea with truncated text
        }

        // Update the word count display
        wordCountDisplay.textContent = `Word Count: ${words.length}/${maxWords}`;

        // Enable the submit button if there's any change in the text
        if (bioText !== bioTextArea.defaultValue) {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }
    }

    // Event listener to count words as user types
    bioTextArea.addEventListener("input", countWords);

    // Initialize word count on page load
    countWords();
});
