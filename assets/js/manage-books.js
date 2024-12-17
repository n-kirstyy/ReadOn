document.addEventListener('DOMContentLoaded', function() {
    const bookSearch = document.getElementById('bookSearch');
    const table = document.querySelector('.books-table tbody');
    const addGenreBtn = document.getElementById('addGenreBtn');
    const genreModal = document.getElementById('genreModal');
    const addGenreForm = document.getElementById('addGenreForm');
    const cancelBtn = document.querySelector('.cancel-btn');

    // Search functionality
    bookSearch?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            const title = row.querySelector('.book-info').textContent.toLowerCase();
            const author = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || author.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Modal controls
    addGenreBtn?.addEventListener('click', () => {
        genreModal.classList.add('show');
    });

    cancelBtn?.addEventListener('click', () => {
        genreModal.classList.remove('show');
    });

    // Close modal when clicking outside
    genreModal?.addEventListener('click', (e) => {
        if (e.target === genreModal) {
            genreModal.classList.remove('show');
        }
    });

    // Add genre form submission
    addGenreForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const genreName = document.getElementById('genreName').value.trim();
        
        try {
            const response = await fetch('../actions/add-genre.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: genreName })
            });

            const data = await response.json();
            
            if (data.success) {
                alert('Genre added successfully');
                window.location.reload();
            } else {
                throw new Error(data.error || 'Failed to add genre');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to add genre');
        }
    });
});

// Delete book function
async function deleteBook(bookId) {
    if (!confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('../actions/delete-book.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ book_id: bookId })
        });

        const data = await response.json();
        
        if (data.success) {
            const bookRow = document.querySelector(`tr[data-book-id="${bookId}"]`);
            if (bookRow) {
                bookRow.remove();
            }
        } else {
            throw new Error(data.error || 'Failed to delete book');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Failed to delete book');
    }
}