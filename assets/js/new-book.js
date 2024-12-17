document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('newBookForm');
    const coverInput = document.getElementById('coverInput');
    const coverPreview = document.getElementById('coverPreview');
    const saveButtons = document.querySelectorAll('[data-action]');

    // Handle cover image preview
    coverInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                alert('Image size should not exceed 5MB');
                coverInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                coverPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle form submission
    saveButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const isPublish = button.dataset.action === 'publish';
            const formData = new FormData(form);
            formData.append('is_published', isPublish ? '1' : '0');

            try {
                const response = await fetch('../actions/create-book.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'write.php';
                } else {
                    throw new Error(data.error || 'Failed to create book');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Failed to create book. Please try again.');
            }
        });
    });
});