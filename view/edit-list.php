<?php
session_start();
include '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$list_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$conn = getDatabaseConnection();

// Fetch list details
$list_query = "
    SELECT name, description
    FROM reading_lists
    WHERE list_id = ? AND user_id = ?
";
$stmt = $conn->prepare($list_query);
$stmt->bind_param("ii", $list_id, $user_id);
$stmt->execute();
$list = $stmt->get_result()->fetch_assoc();

if (!$list) {
    header("Location: reading-lists.php");
    exit();
}

// Fetch books in the list
$books_query = "
    SELECT 
        b.book_id,
        b.title,
        b.cover,
        u.username as author,
        COUNT(DISTINCT bl.like_id) as likes
    FROM list_books lb
    JOIN books b ON lb.book_id = b.book_id
    JOIN users u ON b.author = u.user_id
    LEFT JOIN book_likes bl ON b.book_id = bl.book_id
    WHERE lb.list_id = ?
    GROUP BY b.book_id
";
$stmt = $conn->prepare($books_query);
$stmt->bind_param("i", $list_id);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reading List | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/edit-list-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="edit-list-container">
        <div class="list-header">
            <div class="list-info">
                <input 
                    type="text" 
                    id="listTitle" 
                    value="<?php echo htmlspecialchars($list['name']); ?>"
                    class="list-title-input"
                    placeholder="List Title"
                >
                <textarea 
                    id="listDescription" 
                    class="list-description-input"
                    placeholder="Add a description..."
                ><?php echo htmlspecialchars($list['description'] ?? ''); ?></textarea>
            </div>
            <div class="action-buttons">
                <button id="doneBtn" class="done-btn">Done</button>
                <button id="deleteListBtn" class="delete-btn">Delete List</button>
            </div>
        </div>

        <div class="books-grid">
            <?php foreach ($books as $book): ?>
                <div class="book-item" data-book-id="<?php echo $book['book_id']; ?>">
                    <div class="book-info">
                        <img 
                            src="<?php echo htmlspecialchars($book['cover'] ?? '../assets/images/default-book.jpg'); ?>" 
                            alt="<?php echo htmlspecialchars($book['title']); ?>"
                            class="book-cover"
                        >
                        <div class="book-details">
                            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                            <p class="stats">
                                <span class="views">👁️ <?php echo number_format($book['likes']); ?></span>
                            </p>
                        </div>
                    </div>
                    <button class="remove-book" data-book-id="<?php echo $book['book_id']; ?>">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="../assets/js/edit-list.js"></script>
</body>
</html>