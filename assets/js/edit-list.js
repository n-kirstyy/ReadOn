document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const listId = new URLSearchParams(window.location.search).get('id');
    const titleInput = document.getElementById('listTitle');
    const descriptionInput = document.getElementById('listDescription');
    const doneBtn = document.getElementById('doneBtn');
    const deleteListBtn = document.getElementById('deleteListBtn');
    const removeButtons = document.querySelectorAll('.remove-book');

    // Update list function
    async function updateList() {
        const newTitle = titleInput?.value?.trim() || '';
        const newDescription = descriptionInput?.value?.trim() || '';

        if (!newTitle) {
            alert('Title cannot be empty');
            return;
        }

        try {
            const response = await fetch('../actions/update-list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    list_id: listId,
                    title: newTitle,
                    description: newDescription
                })
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.href = 'reading-lists.php';
            } else {
                throw new Error(data.error || 'Failed to update list');
            }
        } catch (error) {
            console.error('Error updating list:', error);
            alert('Failed to update list. Please try again.');
        }
    }

    // Remove book function
    async function removeBook(bookId) {
        if (!confirm('Remove this book from the list?')) {
            return;
        }

        try {
            const response = await fetch('../actions/remove-from-list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    list_id: listId,
                    book_id: bookId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                const bookElement = document.querySelector(`.book-item[data-book-id="${bookId}"]`);
                if (bookElement) {
                    bookElement.remove();
                }
            } else {
                throw new Error(data.error || 'Failed to remove book');
            }
        } catch (error) {
            console.error('Error removing book:', error);
            alert('Failed to remove book. Please try again.');
        }
    }

    // Delete list function
    async function deleteList() {
        if (!confirm('Are you sure you want to delete this list? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch('../actions/delete-list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    list_id: listId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.href = 'reading-lists.php';
            } else {
                throw new Error(data.error || 'Failed to delete list');
            }
        } catch (error) {
            console.error('Error deleting list:', error);
            alert('Failed to delete list. Please try again.');
        }
    }

    // Event Listeners
    if (doneBtn) {
        doneBtn.addEventListener('click', updateList);
    }

    if (deleteListBtn) {
        deleteListBtn.addEventListener('click', deleteList);
    }

    removeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const bookId = button.dataset.bookId;
            if (bookId) {
                removeBook(bookId);
            }
        });
    });

    // Handle Enter key in title input
    if (titleInput) {
        titleInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                updateList();
            }
        });
    }
});