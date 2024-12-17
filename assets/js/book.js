document.addEventListener('DOMContentLoaded', function() {
    const addToLibraryBtn = document.getElementById('addToLibrary');
    const addToListBtn = document.getElementById('addToList');
    const likeBookBtn = document.getElementById('likeBook');
    const listModal = document.getElementById('listModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const listItems = document.querySelectorAll('.list-item');
    
    // Add/Remove from Library
    if (addToLibraryBtn) {
        addToLibraryBtn.addEventListener('click', async function() {
            try {
                const response = await fetch('../actions/add-to-library.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        book_id: new URLSearchParams(window.location.search).get('id')
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.in_library) {
                        addToLibraryBtn.innerHTML = `
                            <span class="material-symbols-outlined">check</span>
                            In Library
                        `;
                        addToLibraryBtn.classList.add('in-library');
                    } else {
                        addToLibraryBtn.innerHTML = `
                            <span class="material-symbols-outlined">add</span>
                            Add to Library
                        `;
                        addToLibraryBtn.classList.remove('in-library');
                    }
                } else {
                    throw new Error(data.error || 'Failed to update library');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        });
    }
    
    // Like/Unlike Book
    if (likeBookBtn) {
        likeBookBtn.addEventListener('click', async function() {
            try {
                const response = await fetch('../actions/toggle-like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        book_id: new URLSearchParams(window.location.search).get('id')
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const likesCountSpan = likeBookBtn.querySelector('.likes-count');
                    likesCountSpan.textContent = data.likes_count;
                    
                    if (data.liked) {
                        likeBookBtn.classList.add('liked');
                    } else {
                        likeBookBtn.classList.remove('liked');
                    }
                } else {
                    throw new Error(data.error || 'Failed to toggle like');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to update like status');
            }
        });
    }
    
    // Modal Controls
    if (addToListBtn) {
        addToListBtn.addEventListener('click', function() {
            listModal.style.display = 'flex';
        });
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            listModal.style.display = 'none';
        });
    }
    
    
    window.addEventListener('click', function(event) {
        if (event.target === listModal) {
            listModal.style.display = 'none';
        }
    });
    
    // Add/Remove from Reading Lists
    listItems.forEach(item => {
        item.addEventListener('click', async function() {
            const listId = this.dataset.listId;
            const bookId = new URLSearchParams(window.location.search).get('id');
            
            try {
                const response = await fetch('../actions/add-to-list.php', {
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
                    const icon = this.querySelector('.material-symbols-outlined');
                    if (data.in_list) {
                        this.classList.add('in-list');
                        icon.textContent = 'check';
                    } else {
                        this.classList.remove('in-list');
                        icon.textContent = 'add';
                    }
                } else {
                    throw new Error(data.error || 'Failed to update list');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        });
    });
});