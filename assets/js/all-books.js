document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentSearchType = 'books';
    let currentGenre = '';
    let currentView = 'grid';
    let isSearching = false;
    
    const searchInput = document.getElementById('searchInput');
    const searchTypeButtons = document.querySelectorAll('.search-type-buttons button');
    const genreButtons = document.querySelectorAll('.genre-btn');
    const resultsContainer = document.getElementById('resultsContainer');
    const viewToggle = document.getElementById('viewToggle');
    const prevButton = document.getElementById('prevPage');
    const nextButton = document.getElementById('nextPage');
    const loadingIndicator = document.getElementById('loadingIndicator');


    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    searchInput.addEventListener('keyup', debounce(handleSearch, 500));
    searchTypeButtons.forEach(button => {
        button.addEventListener('click', () => handleSearchTypeChange(button));
    });
    genreButtons.forEach(button => {
        button.addEventListener('click', () => handleGenreChange(button));
    });
    viewToggle.addEventListener('click', toggleView);
    prevButton.addEventListener('click', () => changePage(-1));
    nextButton.addEventListener('click', () => changePage(1));


    function handleSearch() {
        const query = searchInput.value.trim();
        currentPage = 1;
        
        if (query.length === 0) {
            isSearching = false;
            fetchBooks();
        } else {
            isSearching = true;
            fetchResults(query);
        }
    }


    async function fetchBooks() {
        showLoading();
        try {
            const params = new URLSearchParams({
                page: currentPage,
                genre: currentGenre
            });

            const response = await fetch(`../actions/get-books.php?${params}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to fetch books');
            }
            
            if (data.results) {
                updateResults(data.results);
                updatePagination(data.hasMore);
            } else {
                throw new Error('No results in response');
            }
        } catch (error) {
            console.error('Failed to fetch books:', error);
            showError('Failed to fetch books. Please try again later.');
        } finally {
            hideLoading();
        }
    }


    function handleGenreChange(button) {
        genreButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        currentGenre = button.dataset.genre;
        currentPage = 1;
        
        if (isSearching && searchInput.value.trim()) {
            handleSearch();
        } else {
            fetchBooks();
        }
    }

    
    function handleSearchTypeChange(button) {
        searchTypeButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        currentSearchType = button.dataset.type;
        
        const genreFilters = document.querySelector('.genre-filters');
        genreFilters.style.display = currentSearchType === 'books' ? 'flex' : 'none';
        viewToggle.style.display = currentSearchType === 'books' ? 'block' : 'none';
        
        if (searchInput.value.trim()) {
            handleSearch();
        }
    }

    function toggleView() {
        currentView = currentView === 'grid' ? 'list' : 'grid';
        resultsContainer.className = currentView + '-view';
        viewToggle.innerHTML = `<span class="material-symbols-outlined">
            ${currentView === 'grid' ? 'list' : 'grid_view'}
        </span>`;
    }

    function changePage(delta) {
        currentPage += delta;
        prevButton.disabled = currentPage === 1;
        
        if (isSearching) {
            fetchResults(searchInput.value.trim());
        } else {
            fetchBooks();
        }
    }

    async function fetchResults(query) {
        showLoading();
        try {
            const params = new URLSearchParams({
                query: query,
                type: currentSearchType,
                genre: currentGenre,
                page: currentPage
            });

            const response = await fetch(`../actions/search.php?${params}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to fetch results');
            }
            
            updateResults(data.results);
            updatePagination(data.hasMore);
        } catch (error) {
            console.error('Search failed:', error);
            showError('Failed to fetch results. Please try again later.');
        } finally {
            hideLoading();
        }
    }

    
    function showError(message) {
        resultsContainer.innerHTML = `
            <div class="error-message" style="text-align: center; padding: 20px; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px 0;">
                ${message}
            </div>`;
    }

    // Update Results
    function updateResults(results) {
        resultsContainer.innerHTML = '';
        
        if (!results || results.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No results found</div>';
            return;
        }

        if (currentSearchType === 'books') {
            resultsContainer.className = currentView + '-view';
            results.forEach(item => {
                resultsContainer.appendChild(createBookCard(item));
            });
        } else {
            // For profiles, always use list view
            resultsContainer.className = 'profile-list';
            results.forEach(item => {
                resultsContainer.appendChild(createProfileCard(item));
            });
        }
    }

    // Create Profile Card
    function createProfileCard(profile) {
        const card = document.createElement('div');
        card.className = 'profile-card';
        card.onclick = () => window.location.href = `profile.php?id=${profile.user_id}`;
        
        card.innerHTML = `
            <img src="${profile.avatar || '../assets/images/default-pfp.jpg'}" 
                alt="${escapeHtml(profile.username)}" 
                class="profile-avatar">
            <div class="profile-info">
                <h3>${escapeHtml(profile.username)}</h3>
                <p class="books-count">${profile.books_count} ${profile.books_count === 1 ? 'book' : 'books'}</p>
            </div>
        `;
        
        return card;
    }

    function createBookCard(book) {
        const card = document.createElement('div');
        card.className = 'book-card';
        card.onclick = () => window.location.href = `book.php?id=${book.book_id}`;
        
        card.innerHTML = `
            <img src="${book.cover || '../assets/images/default-book.jpg'}" 
                 alt="${escapeHtml(book.title)}" 
                 class="book-cover">
            <div class="book-info">
                <h3>${escapeHtml(book.title)}</h3>
                <p class="author">by ${escapeHtml(book.author)}</p>
                <p class="genre">${escapeHtml(book.genre)}</p>
                ${book.description ? `<p class="description">${escapeHtml(book.description)}</p>` : ''}
            </div>
        `;
        
        return card;
    }

    function updatePagination(hasMore) {
        prevButton.disabled = currentPage === 1;
        nextButton.disabled = !hasMore;
    }

    function showLoading() {
        loadingIndicator.classList.remove('hidden');
    }

    function hideLoading() {
        loadingIndicator.classList.add('hidden');
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


    fetchBooks();
});