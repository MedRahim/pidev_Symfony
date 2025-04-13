document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', event => {
            event.preventDefault();
            const postId = button.getAttribute('data-id');
            const likeCountElement = button.querySelector('.like-count');

            fetch(`/blog/${postId}/like`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    likeCountElement.textContent = data.likes;
                })
                .catch(error => console.error('Error:', error));
        });
    });
});
