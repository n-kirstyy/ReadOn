<?php
session_start();
include '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDatabaseConnection();

// Fetch published books
$published_query = "
    SELECT 
        b.book_id,
        b.title,
        b.cover,
        COUNT(c.chapter_id) as chapter_count
    FROM books b
    LEFT JOIN chapters c ON b.book_id = c.book_id
    WHERE b.author = ? AND b.is_published = 1
    GROUP BY b.book_id
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($published_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$published_books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch unpublished books
$unpublished_query = "
    SELECT 
        b.book_id,
        b.title,
        b.cover,
        COUNT(c.chapter_id) as chapter_count
    FROM books b
    LEFT JOIN chapters c ON b.book_id = c.book_id
    WHERE b.author = ? AND b.is_published = 0
    GROUP BY b.book_id
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($unpublished_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unpublished_books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/write-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="write-container">
        <div class="write-header">
            <h1>My Works</h1>
            <a href="new-book.php" class="new-book-btn">
                <span>New Book</span>
                <span class="material-symbols-outlined">add</span>
            </a>
        </div>

        <div class="write-tabs">
            <button class="tab active" data-tab="published">Published</button>
            <button class="tab" data-tab="unpublished">Unpublished</button>
        </div>

        <div class="tab-content" id="published">
            <?php if (empty($published_books)): ?>
                <p class="no-books">You haven't published any books yet.</p>
            <?php else: ?>
                <?php foreach ($published_books as $book): ?>
                    <div class="book-card">
                        <div class="book-info">
                            <img src="<?php echo htmlspecialchars($book['cover'] ?? '../assets/images/default-book.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>"
                                 class="book-cover">
                            <div class="book-details">
                                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p class="chapters"><?php echo $book['chapter_count']; ?> Chapters</p>
                            </div>
                        </div>
                        <div class="book-actions">
                            <a href="edit-book.php?id=<?php echo $book['book_id']; ?>" class="action-btn edit">
                                <span class="material-symbols-outlined">edit</span>
                                <span class="tooltip">Edit</span>
                            </a>
                            <button class="action-btn unpublish" data-book-id="<?php echo $book['book_id']; ?>">
                                <span class="material-symbols-outlined">visibility_off</span>
                                <span class="tooltip">Unpublish</span>
                            </button>
                            <button class="action-btn delete" data-book-id="<?php echo $book['book_id']; ?>">
                                <span class="material-symbols-outlined">delete</span>
                                <span class="tooltip">Delete</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="tab-content hidden" id="unpublished">
            <?php if (empty($unpublished_books)): ?>
                <p class="no-books">You don't have any unpublished books.</p>
            <?php else: ?>
                <?php foreach ($unpublished_books as $book): ?>
                    <div class="book-card">
                        <div class="book-info">
                            <img src="<?php echo htmlspecialchars($book['cover'] ?? '../assets/images/default-book.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>"
                                 class="book-cover">
                            <div class="book-details">
                                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p class="chapters"><?php echo $book['chapter_count']; ?> Chapters</p>
                            </div>
                        </div>
                        <div class="book-actions">
                            <a href="edit-book.php?id=<?php echo $book['book_id']; ?>" class="action-btn edit">
                                <span class="material-symbols-outlined">edit</span>
                                <span class="tooltip">Edit</span>
                            </a>
                            <button class="action-btn publish" data-book-id="<?php echo $book['book_id']; ?>">
                                <span class="material-symbols-outlined">visibility</span>
                                <span class="tooltip">Publish</span>
                            </button>
                            <button class="action-btn delete" data-book-id="<?php echo $book['book_id']; ?>">
                                <span class="material-symbols-outlined">delete</span>
                                <span class="tooltip">Delete</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/write.js"></script>
</body>
</html>