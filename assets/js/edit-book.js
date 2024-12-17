document.addEventListener('DOMContentLoaded', function() {
    const form = {
        title: document.getElementById('title'),
        description: document.getElementById('description'),
        genre: document.getElementById('genre'),
        coverInput: document.getElementById('coverInput'),
        coverPreview: document.getElementById('coverPreview')
    };
    
    const saveBtn = document.getElementById('saveChanges');
    let hasChanges = false;

    // Handle cover image change
    form.coverInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                alert('Image size should not exceed 5MB');
                form.coverInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                form.coverPreview.src = e.target.result;
                hasChanges = true;
            };
            reader.readAsDataURL(file);
        }
    });

    // Track changes
    ['title', 'description', 'genre'].forEach(field => {
        form[field].addEventListener('change', () => hasChanges = true);
    });

    // Save changes
    saveBtn.addEventListener('click', async function() {
        if (!hasChanges) {
            alert('No changes to save');
            return;
        }

        const formData = new FormData();
        formData.append('book_id', bookData.id);
        formData.append('title', form.title.value);
        formData.append('description', form.description.value);
        formData.append('genre', form.genre.value);
        
        if (form.coverInput.files[0]) {
            formData.append('cover', form.coverInput.files[0]);
        }

        try {
            const response = await fetch('../actions/update-book.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                hasChanges = false;
                alert('Changes saved successfully');
            } else {
                throw new Error(data.error || 'Failed to save changes');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to save changes. Please try again.');
        }
    });

    // Handle chapter deletion
    document.querySelectorAll('.delete').forEach(button => {
        button.addEventListener('click', async function() {
            const chapterId = this.dataset.chapterId;
            
            if (!confirm('Are you sure you want to delete this chapter? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch('../actions/delete-chapter.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ chapter_id: chapterId })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.closest('.chapter-item').remove();
                    
                    // Show 'no chapters' message if last chapter was deleted
                    const remainingChapters = document.querySelectorAll('.chapter-item');
                    if (remainingChapters.length === 0) {
                        document.querySelector('.chapters-list').innerHTML = 
                            '<p class="no-chapters">No chapters yet. Create your first chapter!</p>';
                    }
                } else {
                    throw new Error(data.error || 'Failed to delete chapter');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Failed to delete chapter. Please try again.');
            }
        });
    });


    
    document.querySelectorAll('.toggle-publish').forEach(button => {
        button.addEventListener('click', async function() {
            const chapterId = this.dataset.chapterId;
            
            try {
                const response = await fetch('../actions/toggle-chapter-publish.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        chapter_id: chapterId
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Update the button icon and status text
                    const chapterItem = this.closest('.chapter-item');
                    const statusSpan = chapterItem.querySelector('.chapter-status');
                    const icon = this.querySelector('.material-symbols-outlined');
                    
                    if (data.is_published) {
                        statusSpan.textContent = 'Published';
                        statusSpan.classList.remove('draft');
                        statusSpan.classList.add('published');
                        icon.textContent = 'visibility_off';
                    } else {
                        statusSpan.textContent = 'Draft';
                        statusSpan.classList.remove('published');
                        statusSpan.classList.add('draft');
                        icon.textContent = 'visibility';
                    }
                } else {
                    throw new Error(data.error || 'Failed to toggle publish status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Failed to toggle publish status. Please try again.');
            }
        });
    });

    // Warn about unsaved changes when leaving page
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});