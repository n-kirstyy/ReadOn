<?php
session_start();
require_once '../db/db-config.php';

$book_id = $_GET['id'] ?? null;

if (!$book_id) {
    header('Location: all-books.php');
    exit();
}

$conn = getDatabaseConnection();

// Fetch book details with author info, genre, likes, and comments count
$book_query = "
    SELECT b.*, u.username as author_name, g.name as genre_name,
           COUNT(DISTINCT bl.like_id) as likes_count,
           (SELECT COUNT(*) FROM chapter_comments cc
            JOIN chapters ch ON cc.chapter_id = ch.chapter_id
            WHERE ch.book_id = b.book_id) as comment_count
    FROM books b
    JOIN users u ON b.author = u.user_id
    JOIN genres g ON b.genre = g.genre_id
    LEFT JOIN book_likes bl ON b.book_id = bl.book_id
    WHERE b.book_id = ? AND b.is_published = 1
    GROUP BY b.book_id
";

$stmt = $conn->prepare($book_query);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    header('Location: all-books.php');
    exit();
}

// Check if book is in user's library
$in_library = false;
if (isset($_SESSION['user_id'])) {
    $library_check = $conn->prepare("SELECT 1 FROM library WHERE user_id = ? AND book_id = ?");
    $library_check->bind_param("ii", $_SESSION['user_id'], $book_id);
    $library_check->execute();
    $in_library = $library_check->get_result()->num_rows > 0;
}

// Check if user has liked the book
$is_liked = false;
if (isset($_SESSION['user_id'])) {
    $like_check = $conn->prepare("SELECT 1 FROM book_likes WHERE user_id = ? AND book_id = ?");
    $like_check->bind_param("ii", $_SESSION['user_id'], $book_id);
    $like_check->execute();
    $is_liked = $like_check->get_result()->num_rows > 0;
}

// Get first chapter for read button
$first_chapter_query = "SELECT chapter_id FROM chapters WHERE book_id = ? AND is_published = 1 ORDER BY number ASC LIMIT 1";
$stmt = $conn->prepare($first_chapter_query);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$first_chapter = $stmt->get_result()->fetch_assoc();
$first_chapter_id = $first_chapter['chapter_id'] ?? null;

// Get chapters count
$chapter_count = 0;
$chapters_query = "SELECT COUNT(*) as count FROM chapters WHERE book_id = ? AND is_published = 1";
$stmt = $conn->prepare($chapters_query);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $chapter_count = $row['count'];
}

// Fetch user's reading lists
$reading_lists = [];
if (isset($_SESSION['user_id'])) {
    $lists_query = "
        SELECT rl.*, 
               CASE WHEN lb.book_id IS NOT NULL THEN 1 ELSE 0 END as book_in_list
        FROM reading_lists rl
        LEFT JOIN list_books lb ON rl.list_id = lb.list_id AND lb.book_id = ?
        WHERE rl.user_id = ?
    ";
    $lists_stmt = $conn->prepare($lists_query);
    $lists_stmt->bind_param("ii", $book_id, $_SESSION['user_id']);
    $lists_stmt->execute();
    $reading_lists = $lists_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/book-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="book-container">
        <div class="book-header">
            <div class="book-cover">
                <img src="<?php echo htmlspecialchars($book['cover'] ?? '../assets/images/default-book.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($book['title']); ?>">
            </div>
            
            <div class="book-info">
                <h1><?php echo htmlspecialchars($book['title']); ?></h1>
                <p class="author">by <a href="profile.php?id=<?php echo $book['author']; ?>">
                    <?php echo htmlspecialchars($book['author_name']); ?></a></p>
                
                <div class="stats">
                    <span class="likes">
                        <span class="material-symbols-outlined">favorite</span>
                        <?php echo $book['likes_count']; ?>
                    </span>
                    <span class="comments">
                        <span class="material-symbols-outlined">comment</span>
                        <?php echo $book['comment_count']; ?>
                    </span>
                    <span class="chapters">
                        <span class="material-symbols-outlined">menu_book</span>
                        <?php echo $chapter_count; ?> Chapters
                    </span>
                    <span class="genre">#<?php echo strtolower($book['genre_name']); ?></span>
                </div>

                <?php if (!empty($book['description'])): ?>
                    <div class="book-description">
                        <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="actions">
                        <div class="primary-actions">
                            <?php if ($chapter_count > 0): ?>
                                <a href="read.php?id=<?php echo $first_chapter_id; ?>" class="action-btn read-btn">
                                    <span class="material-symbols-outlined">book</span>
                                    Read
                                </a>
                            <?php endif; ?>
                            
                            <button id="likeBook" class="action-btn <?php echo $is_liked ? 'liked' : ''; ?>">
                                <span class="material-symbols-outlined">favorite</span>
                                <span class="likes-count"><?php echo $book['likes_count']; ?></span>
                            </button>
                        </div>

                        <div class="secondary-actions">
                            <?php if (!$in_library): ?>
                                <button id="addToLibrary" class="action-btn">
                                    <span class="material-symbols-outlined">add</span>
                                    Add to Library
                                </button>
                            <?php else: ?>
                                <button id="addToLibrary" class="action-btn in-library">
                                    <span class="material-symbols-outlined">check</span>
                                    In Library
                                </button>
                            <?php endif; ?>
                            
                            <button id="addToList" class="action-btn">
                                <span class="material-symbols-outlined">playlist_add</span>
                                Add to List
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($chapter_count > 0): ?>
            <div class="chapters-section">
                <h2>Chapters</h2>
                <div class="chapters-list">
                    <?php
                    $chapters_query = "SELECT chapter_id, title, number 
                                     FROM chapters 
                                     WHERE book_id = ? AND is_published = 1 
                                     ORDER BY number ASC";
                    $chapters_stmt = $conn->prepare($chapters_query);
                    $chapters_stmt->bind_param("i", $book_id);
                    $chapters_stmt->execute();
                    $chapters = $chapters_stmt->get_result();
                    
                    while ($chapter = $chapters->fetch_assoc()):
                    ?>
                        <a href="read.php?id=<?php echo $chapter['chapter_id']; ?>" class="chapter-item">
                            <span class="chapter-number">Chapter <?php echo $chapter['number']; ?></span>
                            <span class="chapter-title"><?php echo htmlspecialchars($chapter['title']); ?></span>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Add to List Modal -->
    <?php if (isset($_SESSION['user_id']) && !empty($reading_lists)): ?>
    <div id="listModal" class="modal">
        <div class="modal-content">
            <h2>Add to Reading List</h2>
            <div class="lists">
                <?php foreach ($reading_lists as $list): ?>
                    <div class="list-item <?php echo $list['book_in_list'] ? 'in-list' : ''; ?>"
                         data-list-id="<?php echo $list['list_id']; ?>">
                        <span><?php echo htmlspecialchars($list['name']); ?></span>
                        <span class="material-symbols-outlined">
                            <?php echo $list['book_in_list'] ? 'check' : 'add'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="close-modal">Close</button>
        </div>
    </div>
    <?php endif; ?>

    <script src="../assets/js/book.js"></script>
</body>
</html>