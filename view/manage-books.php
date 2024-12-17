<?php
// manage-books.php
session_start();
require_once '../db/db-config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 2) {
    header('Location: index.php');
    exit();
}

$conn = getDatabaseConnection();

// Get all books with their stats
$query = "
    SELECT 
        b.book_id,
        b.title,
        b.cover,
        b.is_published,
        b.created_at,
        u.username as author,
        g.name as genre,
        COUNT(DISTINCT bl.like_id) as likes_count,
        COUNT(DISTINCT c.chapter_id) as chapters_count
    FROM books b
    JOIN users u ON b.author = u.user_id
    JOIN genres g ON b.genre = g.genre_id
    LEFT JOIN book_likes bl ON b.book_id = bl.book_id
    LEFT JOIN chapters c ON b.book_id = c.book_id
    GROUP BY b.book_id
    ORDER BY b.created_at DESC
";

$books = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Get all genres
$genres = $conn->query("SELECT * FROM genres ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-manage.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="admin-container">
        <header class="admin-header">
            <div class="header-main">
                <h1>Manage Books</h1>
                <div class="search-bar">
                    <input type="text" id="bookSearch" placeholder="Search books...">
                    <span class="material-symbols-outlined">search</span>
                </div>
            </div>
            <div class="genre-management">
                <button id="addGenreBtn" class="add-genre-btn">
                    <span class="material-symbols-outlined">add</span>
                    Add New Genre
                </button>
            </div>
        </header>

        <div class="books-table">
            <table>
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>Published</th>
                        <th>Chapters</th>
                        <th>Likes</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr data-book-id="<?php echo $book['book_id']; ?>">
                            <td>
                                <div class="book-info">
                                    <img src="<?php echo htmlspecialchars($book['cover'] ?? '../assets/images/default-book.jpg'); ?>" 
                                         alt="Book cover" 
                                         class="book-cover">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['genre']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $book['is_published'] ? 'published' : 'draft'; ?>">
                                    <?php echo $book['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                            </td>
                            <td><?php echo $book['chapters_count']; ?></td>
                            <td><?php echo $book['likes_count']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($book['created_at'])); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="book.php?id=<?php echo $book['book_id']; ?>" 
                                       class="action-btn view" 
                                       title="View Book">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </a>
                                    <button class="action-btn delete" 
                                            title="Delete Book"
                                            onclick="deleteBook(<?php echo $book['book_id']; ?>)">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Genre Modal -->
    <div id="genreModal" class="modal">
        <div class="modal-content">
            <h2>Add New Genre</h2>
            <form id="addGenreForm">
                <div class="form-group">
                    <label for="genreName">Genre Name</label>
                    <input type="text" id="genreName" name="name" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="submit" class="submit-btn">Add Genre</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/manage-books.js"></script>
</body>
</html>