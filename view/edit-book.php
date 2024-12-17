<?php
session_start();
require_once '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$book_id = $_GET['id'] ?? null;
if (!$book_id) {
    header("Location: write.php");
    exit();
}

$conn = getDatabaseConnection();

// Verify book ownership and get book details
$book_query = "
    SELECT b.*, COUNT(c.chapter_id) as chapter_count 
    FROM books b 
    LEFT JOIN chapters c ON b.book_id = c.book_id 
    WHERE b.book_id = ? AND b.author = ? 
    GROUP BY b.book_id
";
$stmt = $conn->prepare($book_query);
$stmt->bind_param("ii", $book_id, $_SESSION['user_id']);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    header("Location: write.php");
    exit();
}

// Get all chapters for this book
$chapters_query = "
    SELECT chapter_id, number, title, is_published 
    FROM chapters 
    WHERE book_id = ? 
    ORDER BY number ASC
";
$stmt = $conn->prepare($chapters_query);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$chapters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get genres for dropdown
$genres_query = "SELECT genre_id, name FROM genres ORDER BY name";
$genres = $conn->query($genres_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($book['title']); ?> | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/edit-book-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="edit-book-container">
        <header class="edit-header">
            <h1>Edit Book</h1>
            <div class="header-actions">
                <button type="button" class="save-btn" id="saveChanges">Save Changes</button>
            </div>
        </header>

        <div class="edit-content">
            <!-- Book Details Section -->
            <section class="book-details">
                <div class="cover-section">
                    <img id="coverPreview" 
                         src="<?php echo htmlspecialchars($book['cover'] ?? '../assets/images/default-book.jpg'); ?>" 
                         alt="Book Cover">
                    <label for="coverInput" class="change-cover-btn">
                        <span class="material-symbols-outlined">photo_camera</span>
                        Change Cover
                    </label>
                    <input type="file" id="coverInput" accept="image/*" hidden>
                </div>

                <div class="details-form">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="<?php echo htmlspecialchars($book['title']); ?>" 
                               required 
                               maxlength="20">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4" 
                                  maxlength="400"><?php echo htmlspecialchars($book['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre" required>
                            <?php foreach ($genres as $genre): ?>
                                <option value="<?php echo $genre['genre_id']; ?>" 
                                        <?php echo $genre['genre_id'] == $book['genre'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($genre['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Chapters Section -->
            <section class="chapters-section">
                <div class="section-header">
                    <h2>Chapters</h2>
                    <a href="write-chapter.php?book=<?php echo $book_id; ?>" class="new-chapter-btn">
                        <span class="material-symbols-outlined">add</span>
                        New Chapter
                    </a>
                </div>
                
                <div class="chapters-list">
                    <?php if (empty($chapters)): ?>
                        <p class="no-chapters">No chapters yet. Create your first chapter!</p>
                    <?php else: ?>
                        <?php foreach ($chapters as $chapter): ?>
                            <div class="chapter-item" data-chapter-id="<?php echo $chapter['chapter_id']; ?>">
                                <div class="chapter-info">
                                    <span class="chapter-number">Chapter <?php echo $chapter['number']; ?></span>
                                    <h3 class="chapter-title"><?php echo htmlspecialchars($chapter['title']); ?></h3>
                                    <span class="chapter-status <?php echo $chapter['is_published'] ? 'published' : 'draft'; ?>">
                                        <?php echo $chapter['is_published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </div>
                                <div class="chapter-actions">
                                    <a href="edit-chapter.php?id=<?php echo $chapter['chapter_id']; ?>" 
                                    class="action-btn edit">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                    <button class="action-btn toggle-publish" data-chapter-id="<?php echo $chapter['chapter_id']; ?>">
                                        <span class="material-symbols-outlined">
                                            <?php echo $chapter['is_published'] ? 'visibility_off' : 'visibility'; ?>
                                        </span>
                                    </button>
                                    <button class="action-btn delete" data-chapter-id="<?php echo $chapter['chapter_id']; ?>">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </section>
        </div>
    </main>

    <script>
        // Pass book data to JavaScript
        const bookData = <?php echo json_encode([
            'id' => $book_id,
            'title' => $book['title'],
            'description' => $book['description'],
            'genre' => $book['genre'],
            'cover' => $book['cover']
        ]); ?>;
    </script>
    <script src="../assets/js/edit-book.js"></script>
</body>
</html>