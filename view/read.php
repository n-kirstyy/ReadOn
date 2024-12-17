<?php
session_start();
require_once '../db/db-config.php';

$chapter_id = $_GET['id'] ?? null;

if (!$chapter_id) {
    header('Location: all-books.php');
    exit();
}

$conn = getDatabaseConnection();

// Fetch chapter details with book info and navigation
$chapter_query = "
    SELECT 
        c.*, 
        b.title as book_title,
        b.book_id,
        b.author as author_id,
        u.username as author_name,
        (SELECT chapter_id FROM chapters 
         WHERE book_id = c.book_id AND number < c.number 
         ORDER BY number DESC LIMIT 1) as prev_chapter,
        (SELECT chapter_id FROM chapters 
         WHERE book_id = c.book_id AND number > c.number 
         ORDER BY number ASC LIMIT 1) as next_chapter
    FROM chapters c
    JOIN books b ON c.book_id = b.book_id
    JOIN users u ON b.author = u.user_id
    WHERE c.chapter_id = ? AND c.is_published = 1
";

$stmt = $conn->prepare($chapter_query);
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$chapter = $stmt->get_result()->fetch_assoc();

if (!$chapter) {
    header('Location: all-books.php');
    exit();
}

if (isset($_SESSION['user_id'])) {
    $update_progress = "
        INSERT INTO reading_progress (user_id, book_id, chapter_id)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE chapter_id = ?, last_read = CURRENT_TIMESTAMP
    ";
    $stmt = $conn->prepare($update_progress);
    $stmt->bind_param("iiii", $_SESSION['user_id'], $chapter['book_id'], $chapter_id, $chapter_id);
    $stmt->execute();
}


if (!$chapter) {
    header('Location: all-books.php');
    exit();
}

// Fetch all chapters for the book
$chapters_query = "
    SELECT chapter_id, number, title 
    FROM chapters 
    WHERE book_id = ? AND is_published = 1 
    ORDER BY number ASC
";
$stmt = $conn->prepare($chapters_query);
$stmt->bind_param("i", $chapter['book_id']);
$stmt->execute();
$chapters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch comments
$comments_query = "
    SELECT 
        cc.*,
        u.username,
        u.profile as user_profile
    FROM chapter_comments cc
    JOIN users u ON cc.user_id = u.user_id
    WHERE cc.chapter_id = ? AND cc.parent_id IS NULL
    ORDER BY cc.created_at DESC
";
$stmt = $conn->prepare($comments_query);
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch replies for each comment
foreach ($comments as &$comment) {
    $replies_query = "
        SELECT 
            cc.*,
            u.username,
            u.profile as user_profile
        FROM chapter_comments cc
        JOIN users u ON cc.user_id = u.user_id
        WHERE cc.parent_id = ?
        ORDER BY cc.created_at ASC
    ";
    $stmt = $conn->prepare($replies_query);
    $stmt->bind_param("i", $comment['comment_id']);
    $stmt->execute();
    $comment['replies'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($chapter['title']); ?> - <?php echo htmlspecialchars($chapter['book_title']); ?> | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/read-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="read-container">
        <header class="chapter-header">
            <div class="header-info">
                <h4 class="book-title">
                    <a href="book.php?id=<?php echo $chapter['book_id']; ?>">
                        <?php echo htmlspecialchars($chapter['book_title']); ?>
                    </a>
                </h4>
                <h1 class="chapter-title">Chapter <?php echo $chapter['number']; ?>: <?php echo htmlspecialchars($chapter['title']); ?></h1>
                <p class="author">by <a href="profile.php?id=<?php echo $chapter['author_id']; ?>"><?php echo htmlspecialchars($chapter['author_name']); ?></a></p>
            </div>

            <button id="chaptersToggle" class="chapters-toggle">
                <span class="material-symbols-outlined">menu</span>
                Chapters
            </button>
        </header>

        <div class="chapter-content">
            <?php echo nl2br(htmlspecialchars($chapter['content'])); ?>
        </div>

        <div class="chapter-navigation">
            <?php if ($chapter['prev_chapter']): ?>
                <a href="?id=<?php echo $chapter['prev_chapter']; ?>" class="nav-btn prev">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Previous Chapter
                </a>
            <?php endif; ?>

            <?php if ($chapter['next_chapter']): ?>
                <a href="?id=<?php echo $chapter['next_chapter']; ?>" class="nav-btn next">
                    Next Chapter
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Comments Section -->
        <section class="comments-section">
            <h2>Comments</h2>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="commentForm" class="comment-form">
                    <textarea placeholder="Write a comment..." required></textarea>
                    <button type="submit">Post Comment</button>
                </form>
            <?php else: ?>
                <p class="login-prompt">Please <a href="login.php">log in</a> to comment</p>
            <?php endif; ?>

            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment" data-comment-id="<?php echo $comment['comment_id']; ?>">
                        <div class="comment-header">
                            <img src="<?php echo htmlspecialchars($comment['user_profile'] ?? '../assets/images/default-pfp.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($comment['username']); ?>" 
                                 class="user-avatar">
                            <div class="comment-info">
                                <span class="username"><?php echo htmlspecialchars($comment['username']); ?></span>
                                <span class="timestamp"><?php echo date('M j, Y', strtotime($comment['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                        </div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="reply-btn">Reply</button>
                        <?php endif; ?>

                        <!-- Replies -->
                        <?php if (!empty($comment['replies'])): ?>
                            <div class="replies">
                                <?php foreach ($comment['replies'] as $reply): ?>
                                    <div class="reply">
                                        <div class="comment-header">
                                            <img src="<?php echo htmlspecialchars($reply['user_profile'] ?? '../assets/images/default-pfp.jpg'); ?>" 
                                                 alt="<?php echo htmlspecialchars($reply['username']); ?>" 
                                                 class="user-avatar">
                                            <div class="comment-info">
                                                <span class="username"><?php echo htmlspecialchars($reply['username']); ?></span>
                                                <span class="timestamp"><?php echo date('M j, Y', strtotime($reply['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            <?php echo nl2br(htmlspecialchars($reply['comment'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Chapters Sidebar -->
    <aside id="chaptersSidebar" class="chapters-sidebar">
        <div class="sidebar-header">
            <h3>Chapters</h3>
            <button class="close-sidebar">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="chapters-list">
            <?php foreach ($chapters as $chap): ?>
                <a href="?id=<?php echo $chap['chapter_id']; ?>" 
                   class="chapter-item <?php echo $chap['chapter_id'] == $chapter_id ? 'active' : ''; ?>">
                    <span class="chapter-number">Chapter <?php echo $chap['number']; ?></span>
                    <span class="chapter-title"><?php echo htmlspecialchars($chap['title']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>

    <script>
        // Pass chapter data to JavaScript
        const chapterData = {
            chapterId: <?php echo $chapter_id; ?>,
            bookId: <?php echo $chapter['book_id']; ?>
        };
    </script>
    <script src="../assets/js/read.js"></script>
</body>
</html>