// const saveButton = document.getElementById('saveButton');
// const confirmationModal = document.getElementById('confirmationModal');
// const confirmSave = document.getElementById('confirmSave');
// const cancelSave = document.getElementById('cancelSave');
//
// // Show modal when save button is clicked
// saveButton.addEventListener('click', function (event) {
//     event.preventDefault();  // Prevent the form from submitting immediately
//     confirmationModal.style.display = 'block';  // Show the modal
// });
//
// // If user confirms saving, submit the form and redirect
// confirmSave.addEventListener('click', function () {
//     // Change the button color to green and update text to 'Saving...'
//     saveButton.style.backgroundColor = '#28a745';
//     saveButton.textContent = 'Saving...';
//
//     // Proceed with the form submission by clicking the hidden submit button
//     document.getElementById('updateForm').submit();
//
//     // Hide the modal after confirmation
//     confirmationModal.style.display = 'none';
// });
//
// // If user cancels, hide the modal
// cancelSave.addEventListener('click', function () {
//     confirmationModal.style.display = 'none';  // Hide the modal
// });
