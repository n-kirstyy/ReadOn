<?php
session_start();
include '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDatabaseConnection();

$query = "
    SELECT 
        rl.list_id,
        rl.name,
        rl.description,
        COUNT(DISTINCT lb.book_id) as book_count,
        MIN(b.cover) as first_cover,
        GROUP_CONCAT(DISTINCT g.name) as genres
    FROM reading_lists rl
    LEFT JOIN list_books lb ON rl.list_id = lb.list_id
    LEFT JOIN books b ON lb.book_id = b.book_id
    LEFT JOIN genres g ON b.genre = g.genre_id
    WHERE rl.user_id = ?
    GROUP BY rl.list_id
    ORDER BY rl.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reading_lists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/reading-lists-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="library-container">
        <h1>Library</h1>

        <div class="library-tabs">
            <a href="library.php">Private Library</a>
            <a href="reading-lists.php" class="active">Reading Lists</a>
        </div>

        <button class="new-list-btn">
            <span>New Reading List</span>
            <span class="material-symbols-outlined">add</span>
        </button>

        <div class="lists-container">
            <?php if (empty($reading_lists)): ?>
                <p class="no-lists">You haven't created any reading lists yet.</p>
            <?php else: ?>
                <?php foreach ($reading_lists as $list): ?>
                    <div class="list-card" data-list-id="<?php echo $list['list_id']; ?>">
                        <div class="list-content">
                            <div class="book-preview">
                                <div class="drag-handle">
                                    <span class="material-symbols-outlined">drag_indicator</span>
                                </div>
                                <img 
                                    src="<?php echo htmlspecialchars($list['first_cover'] ?? '../assets/images/default-book.jpg'); ?>" 
                                    alt="<?php echo htmlspecialchars($list['name']); ?>"
                                    class="list-cover"
                                >
                            </div>
                            <div class="list-details">
                                <h2 class="list-title"><?php echo htmlspecialchars($list['name']); ?></h2>
                                <p class="story-count"><?php echo $list['book_count']; ?> Stories</p>
                                <?php if (!empty($list['genres'])): ?>
                                    <div class="genres">
                                        <?php foreach (explode(',', $list['genres']) as $genre): ?>
                                            <span class="genre-tag">#<?php echo strtolower(htmlspecialchars(trim($genre))); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="options-btn" aria-label="List options">
                                <span class="material-symbols-outlined">more_horiz</span>
                            </button>
                            <div class="options-menu">
                                <a href="edit-list.php?id=<?php echo $list['list_id']; ?>">Edit</a>
                                <button class="delete-btn" data-list-id="<?php echo $list['list_id']; ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Create List Modal -->
    <div class="modal" id="createListModal">
        <div class="modal-content">
            <h2>Create New Reading List</h2>
            <form id="createListForm">
                <div class="form-group">
                    <label for="listName">List Name</label>
                    <input type="text" id="listName" name="name" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="listDescription">Description (Optional)</label>
                    <textarea id="listDescription" name="description" maxlength="200"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="submit" class="create-btn">Create</button>
                </div>
            </form>
        </div>
    </div>

    <script src = "../assets/js/reading-lists.js"></script>
</body>
</html>