document.addEventListener('DOMContentLoaded', function() {
    const accountBtn = document.getElementById('accountBtn');
    const writeBtn = document.getElementById('writeBtn');
    const accountDropdown = document.getElementById('accountDropdown');
    const writeDropdown = document.getElementById('writeDropdown');
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const mainNav = document.getElementById('mainNav');

    // Toggle dropdown when clicking account button
    if (accountBtn) {
        accountBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            accountDropdown.classList.toggle('show');
            // Close other dropdown if open
            writeDropdown?.classList.remove('show');
        });
    }

    // Toggle dropdown when clicking write button
    if (writeBtn) {
        writeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            writeDropdown.classList.toggle('show');
            // Close other dropdown if open
            accountDropdown?.classList.remove('show');
        });
    }

    // Toggle mobile menu when clicking view more button
    if (viewMoreBtn) {
        viewMoreBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            mainNav.classList.toggle('show');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!accountDropdown?.contains(e.target) && !accountBtn?.contains(e.target)) {
            accountDropdown?.classList.remove('show');
        }
        
        if (!writeDropdown?.contains(e.target) && !writeBtn?.contains(e.target)) {
            writeDropdown?.classList.remove('show');
        }
        
        if (!mainNav?.contains(e.target) && !viewMoreBtn?.contains(e.target)) {
            mainNav?.classList.remove('show');
        }
    });

    // Close dropdowns when pressing escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            accountDropdown?.classList.remove('show');
            writeDropdown?.classList.remove('show');
            mainNav?.classList.remove('show');
        }
    });
});