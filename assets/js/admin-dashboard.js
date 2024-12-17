document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let refreshInterval;
    const REFRESH_INTERVAL = 60000; // Refresh every minute
    
    // Function to format numbers with commas
    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Function to update stats cards
    async function updateStats() {
        try {
            const response = await fetch('../actions/get-admin-stats.php');
            const data = await response.json();
            
            if (data.success) {
                // Update each stat card
                document.querySelectorAll('.stat-card').forEach(card => {
                    const statType = card.dataset.stat;
                    if (data.stats[statType]) {
                        const valueEl = card.querySelector('.value');
                        if (valueEl) {
                            valueEl.textContent = formatNumber(data.stats[statType]);
                            
                            // Add a brief highlight effect
                            valueEl.classList.add('updated');
                            setTimeout(() => valueEl.classList.remove('updated'), 1000);
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Failed to update stats:', error);
        }
    }

    // Function to update user rankings
    async function updateUserRankings() {
        try {
            const response = await fetch('../actions/get-top-users.php');
            const data = await response.json();
            
            if (data.success) {
                const tbody = document.querySelector('.users-table tbody');
                if (!tbody || !data.users) return;

                // Find the highest activity score for percentage calculations
                const maxActivity = Math.max(...data.users.map(user => user.activity_score));

                // Update table rows
                data.users.forEach((user, index) => {
                    const row = tbody.children[index];
                    if (!row) return;

                    const percentage = (user.activity_score / maxActivity) * 100;
                    
                    // Update table cells
                    row.querySelector('td:nth-child(2) a').textContent = user.username;
                    row.querySelector('td:nth-child(3)').textContent = user.total_books;
                    row.querySelector('td:nth-child(4)').textContent = user.total_comments;
                    row.querySelector('td:nth-child(5)').textContent = user.activity_score;
                    row.querySelector('.progress-fill').style.width = `${percentage}%`;
                });
            }
        } catch (error) {
            console.error('Failed to update user rankings:', error);
        }
    }

    // Function to start auto-refresh
    function startAutoRefresh() {
        refreshInterval = setInterval(() => {
            updateStats();
            updateUserRankings();
        }, REFRESH_INTERVAL);
    }

    // Function to stop auto-refresh
    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    }

    // Handle visibility change to save resources
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            updateStats();
            updateUserRankings();
            startAutoRefresh();
        }
    });

    // Initialize search functionality
    const searchInput = document.getElementById('userSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.users-table tbody tr');
            
            rows.forEach(row => {
                const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                row.style.display = username.includes(searchTerm) ? '' : 'none';
            });
        }, 300));
    }

    // Debounce helper function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Handle sort functionality
    document.querySelectorAll('.users-table th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const tbody = document.querySelector('.users-table tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const column = th.dataset.sort;
            const ascending = th.classList.toggle('sort-asc');

            // Sort rows
            rows.sort((a, b) => {
                const aVal = a.querySelector(`td[data-${column}]`).dataset[column];
                const bVal = b.querySelector(`td[data-${column}]`).dataset[column];
                
                if (column === 'username') {
                    return ascending ? 
                        aVal.localeCompare(bVal) : 
                        bVal.localeCompare(aVal);
                } else {
                    return ascending ? 
                        Number(aVal) - Number(bVal) : 
                        Number(bVal) - Number(aVal);
                }
            });

            // Reorder table
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // Export functionality
    const exportBtn = document.getElementById('exportData');
    if (exportBtn) {
        exportBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('../actions/export-admin-data.php');
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `admin-dashboard-export-${new Date().toISOString().split('T')[0]}.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Failed to export data:', error);
                alert('Failed to export data. Please try again.');
            }
        });
    }

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', e => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.dataset.tooltip;
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 5}px`;
            tooltip.style.left = `${rect.left + (element.offsetWidth - tooltip.offsetWidth) / 2}px`;
        });

        element.addEventListener('mouseleave', () => {
            document.querySelectorAll('.tooltip').forEach(t => t.remove());
        });
    });

    // Start initial auto-refresh
    startAutoRefresh();
});
