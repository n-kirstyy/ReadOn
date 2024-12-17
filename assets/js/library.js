document.addEventListener('DOMContentLoaded', () => {
    const bookCards = document.getElementById('book-cards');
    const bookTemplate = document.getElementById('book-template');
    const modal = document.getElementById('book-details-modal');
    const closeButton = document.getElementsByClassName('close-button')[0];
    const libraryOptions = document.querySelectorAll('.library-options a');

    // Function to handle tab switching
    libraryOptions.forEach(option => {
        option.addEventListener('click', (e) => {
            e.preventDefault();
            // Remove active class from all options
            libraryOptions.forEach(opt => opt.classList.remove('active'));
            // Add active class to clicked option
            option.classList.add('active');
            
            // Show appropriate content based on selected tab
            if (option.textContent === 'Private Library') {
                populateBookCards(userLibrary);
                document.querySelector('.book-grid').style.display = 'grid';
                document.querySelector('.reading-lists').style.display = 'none';
            } else if (option.textContent === 'Reading Lists') {
                document.querySelector('.book-grid').style.display = 'none';
                document.querySelector('.reading-lists').style.display = 'block';
            }
        });
    });

    // Function to populate book cards
    function populateBookCards(books) {
        bookCards.innerHTML = '';

        books.forEach(book => {
            const bookCard = bookTemplate.content.cloneNode(true);
            
            // Set book cover image
            const coverImg = bookCard.querySelector('img');
            coverImg.src = book.cover || '../assets/images/default-book.jpg';
            coverImg.alt = book.title;

            // Set book details
            bookCard.querySelector('h3').textContent = book.title;
            bookCard.querySelector('p').textContent = `by ${book.author_name}`;
            bookCard.querySelector('.likes-count').textContent = book.likes_count || '0';
            bookCard.querySelector('.comments-count').textContent = book.comments_count || '0';

            // Add event listener to details button
            const detailsBtn = bookCard.querySelector('.details-btn');
            detailsBtn.addEventListener('click', () => showBookDetails(book.book_id));

            bookCards.appendChild(bookCard);
        });
    }


    // Function to show book details modal
    function showBookDetails(bookId) {
        fetch(`../functions/get-book-details.php?book_id=${bookId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('book-title').textContent = data.title;
                document.getElementById('book-description').textContent = data.description || 'No description available';

                // Populate chapters if available
                const chapterList = document.getElementById('book-chapters');
                chapterList.innerHTML = '';
                if (data.chapters && data.chapters.length > 0) {
                    data.chapters.forEach(chapter => {
                        const li = document.createElement('li');
                        li.textContent = chapter.title;
                        li.addEventListener('click', () => window.location.href = `read.php?chapter=${chapter.chapter_id}`);
                        chapterList.appendChild(li);
                    });
                } else {
                    chapterList.innerHTML = '<li>No chapters available</li>';
                }

                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching book details:', error);
                alert('Error loading book details. Please try again.');
            });
    }

    // Event listeners for modal
    closeButton.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Handle escape key for modal
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    });

    // Initialize the page with private library view
    if (userLibrary) {
        populateBookCards(userLibrary);
    }
});