<?php
session_start();
require_once '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$chapter_id = $_GET['id'] ?? null;
if (!$chapter_id) {
    header("Location: write.php");
    exit();
}

$conn = getDatabaseConnection();

// Fetch chapter details with book info
$chapter_query = "
    SELECT c.*, b.title as book_title, b.author, b.book_id
    FROM chapters c
    JOIN books b ON c.book_id = b.book_id
    WHERE c.chapter_id = ? AND b.author = ?
";
$stmt = $conn->prepare($chapter_query);
$stmt->bind_param("ii", $chapter_id, $_SESSION['user_id']);
$stmt->execute();
$chapter = $stmt->get_result()->fetch_assoc();

if (!$chapter) {
    header("Location: write.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Chapter | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/edit-chapter-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="edit-chapter-container">
        <header class="edit-header">
            <div class="header-info">
                <a href="edit-book.php?id=<?php echo $chapter['book_id']; ?>" class="back-btn">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h4 class="book-title"><?php echo htmlspecialchars($chapter['book_title']); ?></h4>
                    <h1>Edit Chapter</h1>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" class="save-btn" id="saveChanges">Save Changes</button>
            </div>
        </header>

        <div class="editor-container">
            <div class="chapter-info">
                <div class="chapter-number">Chapter <?php echo $chapter['number']; ?></div>
                <input type="text" 
                       id="chapterTitle" 
                       placeholder="Chapter Title" 
                       value="<?php echo htmlspecialchars($chapter['title']); ?>"
                       maxlength="20"
                       required>
            </div>

            <div class="editor-wrapper">
                <textarea id="chapterContent" 
                          placeholder="Write your chapter content here..."
                          required><?php echo htmlspecialchars($chapter['content']); ?></textarea>
            </div>
        </div>
    </main>

    <script>
        // Pass chapter data to JavaScript
        const chapterData = {
            chapterId: <?php echo $chapter_id; ?>,
            bookId: <?php echo $chapter['book_id']; ?>,
            isPublished: <?php echo $chapter['is_published']; ?>,
            number: <?php echo $chapter['number']; ?>
        };
    </script>
    <script src="../assets/js/edit-chapter.js"></script>
</body>
</html>