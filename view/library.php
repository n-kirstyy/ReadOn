<?php
session_start();
include '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDatabaseConnection();

$private_library_query = "
    SELECT 
        b.book_id,
        b.title,
        b.cover,
        u.username as author_name,
        g.name as genre_name,
        COUNT(DISTINCT bl.like_id) as likes_count,
        COUNT(DISTINCT cc.comment_id) as comments_count,
        rp.chapter_id as last_read_chapter,
        (SELECT MIN(chapter_id) 
         FROM chapters 
         WHERE book_id = b.book_id AND is_published = 1) as first_chapter
    FROM library l
    JOIN books b ON l.book_id = b.book_id
    JOIN users u ON b.author = u.user_id
    JOIN genres g ON b.genre = g.genre_id
    LEFT JOIN book_likes bl ON b.book_id = bl.book_id
    LEFT JOIN chapters c ON b.book_id = c.book_id
    LEFT JOIN chapter_comments cc ON c.chapter_id = cc.chapter_id
    LEFT JOIN reading_progress rp ON b.book_id = rp.book_id AND rp.user_id = ?
    WHERE l.user_id = ?
    GROUP BY b.book_id, b.title, b.cover, u.username, g.name, rp.chapter_id
";

$stmt = $conn->prepare($private_library_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$library_books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/library-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="library-container">
        <h1>Library</h1>

        <div class="library-tabs">
            <a href="library.php" class="active">Private Library</a>
            <a href="reading-lists.php">Reading Lists</a>
        </div>

        <div class="book-grid">
            <?php if (empty($library_books)): ?>
                <p class="no-books">You haven't added any books yet</p>
            <?php else: ?>
                <?php foreach ($library_books as $book): ?>
                    <div class="book-item">
                        <img 
                            src="<?php echo !empty($book['cover']) ? $book['cover'] : '../assets/images/default-book.jpg'; ?>" 
                            alt="<?php echo htmlspecialchars($book['title']); ?>" 
                            class="book-image"
                        >
                        <div class="book-info">
                            <h2 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h2>
                            <p class="book-author">by <?php echo htmlspecialchars($book['author_name']); ?></p>
                            <div class="stats">
                                <span>❤️ <span class="likes-count"><?php echo $book['likes_count'] ?? '0'; ?></span></span>
                                <span>💬 <span class="comments-count"><?php echo $book['comments_count'] ?? '0'; ?></span></span>
                            </div>
                            <div class="book-actions">
                                <a href="book.php?id=<?php echo $book['book_id']; ?>" class="action-btn details-btn">
                                    <span class="material-symbols-outlined">info</span>
                                    Details
                                </a>
                                <?php if (!empty($book['last_read_chapter'])): ?>
                                    <a href="read.php?id=<?php echo $book['last_read_chapter']; ?>" class="action-btn continue-btn">
                                        <span class="material-symbols-outlined">book</span>
                                        Continue Reading
                                    </a>
                                <?php elseif (!empty($book['first_chapter'])): ?>
                                    <a href="read.php?id=<?php echo $book['first_chapter']; ?>" class="action-btn continue-btn">
                                        <span class="material-symbols-outlined">book</span>
                                        Start Reading
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/library.js"></script>
</body>
</html>