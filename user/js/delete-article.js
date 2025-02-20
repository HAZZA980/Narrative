document.querySelectorAll('.delete-link').forEach(function(deleteButton) {
    deleteButton.addEventListener('click', function() {
        // Get the article ID from the clicked button
        var articleId = this.getAttribute('data-article-id');

        // Show the confirmation modal
        document.getElementById('deleteModal').style.display = 'flex';

        // Add the article ID to the confirm delete button
        document.getElementById('confirmDelete').setAttribute('data-article-id', articleId);
    });
});


document.getElementById('cancelDelete').addEventListener('click', function() {
    // Hide the confirmation modal without deleting
    document.getElementById('deleteModal').style.display = 'none';
});

document.getElementById('confirmDelete').addEventListener('click', function() {
    // Get the article ID from the confirm button's data attribute
    var articleId = this.getAttribute('data-article-id');

    // Hide the modal
    document.getElementById('deleteModal').style.display = 'none';

    // Redirect to the delete-article.php with the article ID to perform the deletion
        window.location.href = BASE_URL + "user/model/delete-article.php?id=" + articleId;
});