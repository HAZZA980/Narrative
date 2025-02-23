document.addEventListener("DOMContentLoaded", function () {
    var passwordField = document.getElementById("register-password");
    var confirmPasswordField = document.getElementById("register-confirm-password");
    var passwordMatchMessage = document.getElementById("password-match-message");
    var passwordStrengthContainer = document.getElementById("password-strength-container");
    var passwordRequirementsBox = document.getElementById("requirements-box");
    var strengthBar = document.getElementById("strength-bar");
    var strengthText = document.getElementById("strength-text");
    var submitButton = document.getElementById("submit-button");

    // Show password strength container and requirements box when the user starts typing
    passwordField.addEventListener("focus", function () {
        passwordStrengthContainer.classList.remove("hidden");
        passwordStrengthContainer.classList.add("fade-in");
        if (!isPasswordValid()) {
            passwordRequirementsBox.classList.remove("hidden");
            passwordRequirementsBox.classList.add("fade-in");
        }
    });

    // Update password strength meter and check requirements
    passwordField.addEventListener("input", function () {
        checkPasswordStrength();
        checkPasswordMatch(); // Also check password match when typing
        if (isPasswordValid()) {
            passwordRequirementsBox.classList.add("fade-out");
            setTimeout(function () {
                passwordRequirementsBox.classList.add("hidden");
                passwordRequirementsBox.classList.remove("fade-out");
            }, 500);  // Delay to match fade-out duration
        } else {
            passwordRequirementsBox.classList.remove("hidden");
            passwordRequirementsBox.classList.add("fade-in");
        }
        enableSubmitButton(); // Check if the form can be submitted
    });

    // Check password validity based on length, uppercase letter, and number
    function isPasswordValid() {
        var password = passwordField.value;
        return password.length >= 8 && /[A-Z]/.test(password) && /\d/.test(password);
    }

    // Function to check password strength
    function checkPasswordStrength() {
        let password = passwordField.value;
        let strength = 0;

        // Password strength calculation
        if (password.length >= 4) strength++;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;

        // Update strength bar and text
        let progress = 0;
        let color = "red";
        let text = "Weak";

        switch (strength) {
            case 0:
            case 1:
                progress = 25;
                color = "red";
                text = "Weak";
                break;
            case 2:
                progress = 50;
                color = "orange";
                text = "Getting There";
                break;
            case 3:
                progress = 75;
                color = "orange";
                text = "Getting There";
                break;
            case 4:
                progress = 100;
                color = "green";
                text = "Secure";
                break;
        }

        // Smooth loading of the bar
        strengthBar.style.transition = "width 0.5s ease-in-out";
        strengthBar.style.width = `${progress}%`;
        strengthBar.style.backgroundColor = color;
        strengthText.innerText = text;
        strengthText.style.color = color;

        // Show/hide the requirements box based on validity
        if (isPasswordValid()) {
            passwordRequirementsBox.classList.add("hidden");
        } else {
            passwordRequirementsBox.classList.remove("hidden");
        }
    }

    // Check if passwords match only when the user types in confirm password field
    confirmPasswordField.addEventListener("input", function () {
        checkPasswordMatch();
        enableSubmitButton(); // Check if the form can be submitted
    });

    // Function to check if passwords match
    function checkPasswordMatch() {
        var password = passwordField.value;
        var confirmPassword = confirmPasswordField.value;

        // Show "Passwords don't match" only when the user starts typing into the confirm password box
        if (confirmPassword !== "" && password !== confirmPassword) {
            passwordMatchMessage.textContent = "Passwords don't match";
            passwordMatchMessage.style.color = "red";
        } else if (confirmPassword !== "") {
            passwordMatchMessage.textContent = "Passwords match";
            passwordMatchMessage.style.color = "green";
        } else {
            passwordMatchMessage.textContent = ""; // Clear the message if the user clears the confirm password field
        }
    }

    // Enable submit button only when the form is valid
    function enableSubmitButton() {
        var password = passwordField.value;
        var confirmPassword = confirmPasswordField.value;

        // Enable the submit button only if the password is valid and passwords match
        if (isPasswordValid() && password === confirmPassword) {
            submitButton.disabled = false; // Enable button
        } else {
            submitButton.disabled = true; // Disable button
        }
    }

    // Validate form before submission
    function validateForm() {
        var password = passwordField.value;
        var confirmPassword = confirmPasswordField.value;

        // Ensure passwords match
        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return false; // Prevent form submission
        }

        // Ensure password meets the strength requirements
        if (!isPasswordValid()) {
            alert("Password does not meet strength requirements.");
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }

    // Hide password strength and requirements box when clicking outside the input
    document.addEventListener("click", function (event) {
        if (!passwordField.contains(event.target) && passwordField.value.trim() === "") {
            passwordStrengthContainer.classList.add("hidden");
            passwordRequirementsBox.classList.add("hidden");
        }
    });
});
