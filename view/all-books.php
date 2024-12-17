<?php
session_start();
require_once '../db/db-config.php';

// Get all genres for filters
$conn = getDatabaseConnection();
$genres_query = "SELECT genre_id, name FROM genres";
$genres_result = $conn->query($genres_query);
$genres = [];
while ($row = $genres_result->fetch_assoc()) {
    $genres[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="eng">
<head>
    <title>All Books - ReadOn</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/all-books-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <!-- Search and Filter Section -->
        <div class="search-section">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search books or profiles...">
                <div class="search-type-buttons">
                    <button class="active" data-type="books">Books</button>
                    <button data-type="profiles">Profiles</button>
                </div>
                <button id="viewToggle" class="view-toggle">
                    <span class="material-symbols-outlined">grid_view</span>
                </button>
            </div>

            <div class="genre-filters">
                <button class="genre-btn active" data-genre="">All Genres</button>
                <?php foreach ($genres as $genre): ?>
                    <button class="genre-btn" data-genre="<?php echo $genre['genre_id']; ?>">
                        <?php echo htmlspecialchars($genre['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Results Section -->
        <div id="resultsContainer" class="grid-view">
            <!-- Books will be loaded dynamically -->
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button id="prevPage" disabled>Previous</button>
            <button id="nextPage">Next</button>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="loading-indicator hidden">
            <span class="material-symbols-outlined spinning">sync</span>
        </div>
    </div>

    <script src="../assets/js/all-books.js"></script>
</body>
</html>