function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('profile-preview');
        output.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}

