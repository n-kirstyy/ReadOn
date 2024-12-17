<?php
session_start();
require_once '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDatabaseConnection();
$chapter_id = $_GET['id'] ?? null; // For editing existing chapter
$book_id = $_GET['book'] ?? null; // For new chapter

// For editing existing chapter
$chapter = null;
if ($chapter_id) {
    $chapter_query = "
        SELECT c.*, b.title as book_title, b.author
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
    
    $book_id = $chapter['book_id'];
}

// Verify book ownership
if ($book_id) {
    $book_query = "SELECT title FROM books WHERE book_id = ? AND author = ?";
    $stmt = $conn->prepare($book_query);
    $stmt->bind_param("ii", $book_id, $_SESSION['user_id']);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if (!$book) {
        header("Location: write.php");
        exit();
    }
}

// Get next chapter number for new chapters
if (!$chapter_id) {
    $number_query = "SELECT MAX(number) as last_number FROM chapters WHERE book_id = ?";
    $stmt = $conn->prepare($number_query);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $next_chapter_number = ($result['last_number'] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $chapter ? 'Edit' : 'New'; ?> Chapter | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/write-chapter-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="write-chapter-container">
        <header class="write-header">
            <div class="header-info">
                <a href="edit-book.php?id=<?php echo $book_id; ?>" class="back-btn">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h4 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h4>
                    <h1 class="page-title">
                        <?php echo $chapter ? 'Edit Chapter' : 'New Chapter'; ?>
                    </h1>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" class="draft-btn" data-action="draft">Save as Draft</button>
                <button type="button" class="publish-btn" data-action="publish">Save & Publish</button>
            </div>
        </header>

        <div class="editor-container">
            <!-- Chapter Title -->
            <div class="chapter-info">
                <div class="chapter-number">
                    Chapter <?php echo $chapter ? $chapter['number'] : $next_chapter_number; ?>
                </div>
                <input type="text" 
                       id="chapterTitle" 
                       placeholder="Chapter Title" 
                       value="<?php echo htmlspecialchars($chapter['title'] ?? ''); ?>"
                       maxlength="20"
                       required>
            </div>

            <!-- Chapter Content -->
            <div class="editor-wrapper">
                <textarea id="chapterContent" 
                          placeholder="Write your chapter content here..."
                          required><?php echo htmlspecialchars($chapter['content'] ?? ''); ?></textarea>
            </div>
        </div>
    </main>

    <!-- Pass data to JavaScript -->
    <script>
        const chapterData = {
            bookId: <?php echo $book_id; ?>,
            chapterId: <?php echo $chapter_id ?? 'null'; ?>,
            number: <?php echo $chapter ? $chapter['number'] : $next_chapter_number; ?>
        };
    </script>
    <script src="../assets/js/write-chapter.js"></script>
</body>
</html>