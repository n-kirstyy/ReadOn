document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('chapterTitle');
    const contentTextarea = document.getElementById('chapterContent');
    const saveBtn = document.getElementById('saveChanges');
    let hasChanges = false;

    // Track changes
    [titleInput, contentTextarea].forEach(element => {
        element.addEventListener('input', () => hasChanges = true);
    });

    // Save function
    async function saveChapter() {
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
                    chapter_id: chapterData.chapterId,
                    book_id: chapterData.bookId,
                    number: chapterData.number,
                    title: titleInput.value.trim(),
                    content: contentTextarea.value.trim(),
                    is_published: chapterData.isPublished
                })
            });

            const data = await response.json();
            
            if (data.success) {
                hasChanges = false;
                alert('Changes saved successfully');
            } else {
                throw new Error(data.error || 'Failed to save chapter');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to save changes. Please try again.');
        }
    }

    // Handle save button
    saveBtn.addEventListener('click', saveChapter);

    // Warn about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});