document.addEventListener('DOMContentLoaded', function() {
    const chaptersToggle = document.getElementById('chaptersToggle');
    const chaptersSidebar = document.getElementById('chaptersSidebar');
    const closeSidebar = document.querySelector('.close-sidebar');
    const commentForm = document.getElementById('commentForm');

    // Chapters sidebar toggle
    chaptersToggle?.addEventListener('click', () => {
        chaptersSidebar.classList.add('show');
    });

    closeSidebar?.addEventListener('click', () => {
        chaptersSidebar.classList.remove('show');
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', (e) => {
        if (chaptersSidebar?.classList.contains('show') && 
            !chaptersSidebar.contains(e.target) && 
            !chaptersToggle.contains(e.target)) {
            chaptersSidebar.classList.remove('show');
        }
    });

    // Handle top-level comment submission
    if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const textarea = commentForm.querySelector('textarea');
            const comment = textarea.value.trim();
            
            if (!comment) return;

            try {
                const response = await fetch('../actions/post-comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        chapter_id: chapterData.chapterId,
                        comment: comment
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    throw new Error(data.error || 'Failed to post comment');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to post comment. Please try again.');
            }

            textarea.value = '';
        });
    }

    // Handle reply buttons
    document.querySelectorAll('.reply-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Remove any existing reply forms first
            const existingForms = document.querySelectorAll('.reply-form');
            existingForms.forEach(form => form.remove());

            const comment = this.closest('.comment');
            const commentId = comment.dataset.commentId;
            
            // Create reply form
            const replyForm = document.createElement('form');
            replyForm.className = 'reply-form';
            
            // Add form HTML
            replyForm.innerHTML = `
                <textarea placeholder="Write your reply..." required></textarea>
                <div class="form-actions">
                    <button type="button" class="cancel-reply">Cancel</button>
                    <button type="submit" class="submit-reply">Reply</button>
                </div>
            `;
            
            // Insert form after the reply button
            this.insertAdjacentElement('afterend', replyForm);
            
            // Focus the textarea
            const textarea = replyForm.querySelector('textarea');
            textarea.focus();

            // Handle reply submission
            replyForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const replyText = textarea.value.trim();
                if (!replyText) return;

                try {
                    const response = await fetch('../actions/post-comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            chapter_id: chapterData.chapterId,
                            parent_id: commentId,
                            comment: replyText
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        location.reload();
                    } else {
                        throw new Error(data.error || 'Failed to post reply');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to post reply. Please try again.');
                }
            });

            // Handle cancel button
            const cancelButton = replyForm.querySelector('.cancel-reply');
            cancelButton.addEventListener('click', () => {
                replyForm.remove();
            });
        });
    });

    //Close by esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const replyForms = document.querySelectorAll('.reply-form');
            replyForms.forEach(form => form.remove());
        }
    });
});