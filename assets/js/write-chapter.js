document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('chapterTitle');
    const contentTextarea = document.getElementById('chapterContent');
    const actionButtons = document.querySelectorAll('[data-action]');
    
    let hasChanges = false;

    // Track changes
    [titleInput, contentTextarea].forEach(element => {
        element.addEventListener('input', () => hasChanges = true);
    });

    // Save function
    async function saveChapter(isPublished) {
        if (!titleInput.value.trim()) {
            alert('Please enter a chapter title');
            titleInput.focus();
            return;
        }

        if (!contentTextarea.value.trim()) {
            alert('Please enter chapter content');
            contentTextarea.focus();
            return;
        }

        try {
            const response = await fetch('../actions/save-chapter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    book_id: chapterData.bookId,
                    chapter_id: chapterData.chapterId,
                    number: chapterData.number,
                    title: titleInput.value.trim(),
                    content: contentTextarea.value.trim(),
                    is_published: isPublished
                })
            });

            const data = await response.json();
            
            if (data.success) {
                hasChanges = false;
                window.location.href = `edit-book.php?id=${chapterData.bookId}`;
            } else {
                throw new Error(data.error || 'Failed to save chapter');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to save chapter. Please try again.');
        }
    }

    // Handle save buttons
    actionButtons.forEach(button => {
        button.addEventListener('click', () => {
            const isPublish = button.dataset.action === 'publish';
            saveChapter(isPublish);
        });
    });

    // Warn about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});