document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const newListBtn = document.querySelector('.new-list-btn');
    const modal = document.getElementById('createListModal');
    const newListForm = document.getElementById('createListForm');
    const nameInput = document.getElementById('listName');
    const descriptionInput = document.getElementById('listDescription');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    // Show modal when clicking new list button
    if (newListBtn) {
        newListBtn.addEventListener('click', () => {
            modal.classList.add('show');
        });
    }

    // Handle form submission
    if (newListForm) {
        newListForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const response = await fetch('../actions/create-list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: nameInput.value.trim(),
                        description: descriptionInput.value.trim()
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    throw new Error(data.error || 'Failed to create list');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Failed to create list');
            }
        });
    }

    // Handle delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const listId = this.dataset.listId;
            if (!listId || !confirm('Are you sure you want to delete this list?')) {
                return;
            }

            try {
                const response = await fetch('../actions/delete-list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ list_id: listId })
                });

                const data = await response.json();
                
                if (data.success) {
                    const listCard = this.closest('.list-card');
                    if (listCard) {
                        listCard.remove();
                    } else {
                        window.location = '../reading-lists.php';
                    }
                } else {
                    throw new Error(data.error || 'Failed to delete list');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Failed to delete list');
            }
        });
    });

    // Close modal when clicking cancel
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            modal.classList.remove('show');
            newListForm.reset();
        });
    }

    // Handle options menu toggle
    document.querySelectorAll('.options-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            menu.classList.toggle('show');
        });
    });

    // Close menus when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.options-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
});