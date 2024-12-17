
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

    // Bio editing
    const editBio = document.getElementById('edit-bio');
    const bioText = document.getElementById('bio-text');
    const bioEditor = document.getElementById('bio-editor');
    const saveBio = document.getElementById('save-bio');

    if (editBio) {
        editBio.addEventListener('click', () => {
            bioText.classList.add('hidden');
            bioEditor.classList.remove('hidden');
            saveBio.classList.remove('hidden');
            editBio.classList.add('hidden');
        });
    }

    if (saveBio) {
        saveBio.addEventListener('click', async () => {
            const newBio = bioEditor.value;
            
            try {
                const response = await fetch('../actions/update-bio.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ bio: newBio })
                });

                const data = await response.json();
                
                if (data.success) {
                    bioText.innerHTML = newBio.replace(/\n/g, '<br>');
                    bioText.classList.remove('hidden');
                    bioEditor.classList.add('hidden');
                    saveBio.classList.add('hidden');
                    editBio.classList.remove('hidden');
                } else {
                    throw new Error(data.error || 'Failed to update bio');
                }
            } catch (error) {
                console.error('Error updating bio:', error);
                alert('Failed to update bio. Please try again.');
            }
        });
    }

    // Profile picture upload
    const profileUpload = document.getElementById('profile-upload');
    if (profileUpload) {
        profileUpload.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('profile', file);

            try {
                const response = await fetch('../actions/update-pfp.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    document.querySelector('.profile-pic').src = data.profile_url;
                    // Update navbar profile picture if it exists
                    const navProfilePic = document.querySelector('.profile-pic-nav');
                    if (navProfilePic) {
                        navProfilePic.src = data.profile_url;
                    }
                } else {
                    throw new Error(data.error || 'Failed to update profile picture');
                }
            } catch (error) {
                console.error('Error uploading profile picture:', error);
                alert('Failed to upload profile picture. Please try again.');
            }
        });
    }

    // Reading Lists Expansion
    const listCards = document.querySelectorAll('.list-card');
    listCards.forEach(card => {
        let booksLoaded = false;
        
        card.addEventListener('click', async function() {
            const listId = this.dataset.listId;
            const booksContainer = this.querySelector('.list-books');
            const isHidden = booksContainer.classList.contains('hidden');

            // Hide all other expanded lists
            document.querySelectorAll('.list-books').forEach(container => {
                if (container !== booksContainer) {
                    container.classList.add('hidden');
                }
            });

            // If we're showing the books and haven't loaded them yet
            if (isHidden && !booksLoaded) {
                try {
                    booksContainer.innerHTML = `
                        <div class="loading-indicator">
                            <span class="material-symbols-outlined">sync</span>
                            Loading books...
                        </div>
                    `;
                    booksContainer.classList.remove('hidden');

                    const response = await fetch(`../actions/get-profile-list-books.php?list_id=${listId}`);
                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.error || 'Failed to load books');
                    }

                    if (data.books.length === 0) {
                        booksContainer.innerHTML = `
                            <div class="empty-message">
                                <span class="material-symbols-outlined">menu_book</span>
                                No books in this list yet
                            </div>
                        `;
                    } else {
                        booksContainer.innerHTML = `
                            <div class="list-books-grid">
                                ${data.books.map(book => `
                                    <a href="book.php?id=${book.book_id}" class="list-book-card">
                                        <img src="${book.cover || '../assets/images/default-book.jpg'}" 
                                             alt="${escapeHtml(book.title)}" 
                                             class="list-book-cover">
                                        <div class="list-book-info">
                                            <h4 class="list-book-title">${escapeHtml(book.title)}</h4>
                                            <p class="list-book-author">by ${escapeHtml(book.author)}</p>
                                        </div>
                                    </a>
                                `).join('')}
                            </div>
                        `;
                    }

                    booksLoaded = true;
                } catch (error) {
                    console.error('Error:', error);
                    booksContainer.innerHTML = `
                        <div class="error-message">
                            <span class="material-symbols-outlined">error</span>
                            Failed to load books. Please try again.
                        </div>
                    `;
                }
            } else {
                // Toggle visibility
                booksContainer.classList.toggle('hidden');
            }
        });
    });
});

// Helper function to escape HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}