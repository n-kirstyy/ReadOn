document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Show corresponding content
            contents.forEach(content => {
                content.classList.add('hidden');
                if (content.id === target) {
                    content.classList.remove('hidden');
                }
            });
        });
    });

    // Publish/Unpublish book
    document.querySelectorAll('.publish, .unpublish').forEach(button => {
        button.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to change this book\'s publish status?')) {
                return;
            }

            const bookId = button.dataset.bookId;
            
            try {
                const response = await fetch('../actions/toggle-publish.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ book_id: bookId })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    throw new Error(data.error || 'Failed to update book status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to update book status. Please try again.');
            }
        });
    });

    // Delete book
    document.querySelectorAll('.delete').forEach(button => {
        button.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
                return;
            }

            const bookId = button.dataset.bookId;
            
            try {
                const response = await fetch('../actions/delete-book.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ book_id: bookId })
                });

                const data = await response.json();
                
                if (data.success) {
                    button.closest('.book-card').remove();
                    if (!document.querySelector('.book-card')) {
                        location.reload(); // Reload to show "no books" message
                    }
                } else {
                    throw new Error(data.error || 'Failed to delete book');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to delete book. Please try again.');
            }
        });
    });
});