document.addEventListener('DOMContentLoaded', function() {
    // Handle like button clicks
    document.querySelectorAll('.btn-like').forEach(button => {
        button.addEventListener('click', function() {
            handleRecipeAction(this, 'like');
        });
    });

    // Handle save button clicks
    document.querySelectorAll('.btn-save').forEach(button => {
        button.addEventListener('click', function() {
            handleRecipeAction(this, 'save');
        });
    });
});

function handleRecipeAction(button, action) {
    const recipeId = button.dataset.recipeId;
    const formData = new FormData();
    formData.append('recipe_id', recipeId);
    formData.append('action', action);

    fetch('handle_recipe_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('active');
            const span = button.querySelector('span');
            if (action === 'like') {
                span.textContent = button.classList.contains('active') ? 'Liked' : 'Like';
            } else {
                span.textContent = button.classList.contains('active') ? 'Saved' : 'Save';
            }
        } else {
            if (data.message === 'Please login first') {
                window.location.href = 'login.php';
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
