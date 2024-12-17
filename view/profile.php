<?php 
session_start();
require_once '../db/db-config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get profile user info
$profile_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];
$isOwnProfile = $profile_id == $_SESSION['user_id'];

$conn = getDatabaseConnection();

// Fetch user data
$stmt = $conn->prepare("SELECT username, bio, profile, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: index.php");
    exit();
}

// Get book count
$stmt = $conn->prepare("SELECT COUNT(*) as book_count FROM books WHERE author = ? AND is_published = 1");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$book_count = $stmt->get_result()->fetch_assoc()['book_count'];

// Get list count
$stmt = $conn->prepare("SELECT COUNT(*) as list_count FROM reading_lists WHERE user_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$list_count = $stmt->get_result()->fetch_assoc()['list_count'];

// Get published books
$stmt = $conn->prepare("
    SELECT b.*, g.name as genre_name 
    FROM books b 
    JOIN genres g ON b.genre = g.genre_id 
    WHERE b.author = ? AND b.is_published = 1
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get reading lists
$lists_query = "
    SELECT 
        rl.*,
        COUNT(DISTINCT lb.book_id) as book_count,
        GROUP_CONCAT(DISTINCT g.name) as genres
    FROM reading_lists rl
    LEFT JOIN list_books lb ON rl.list_id = lb.list_id
    LEFT JOIN books b ON lb.book_id = b.book_id
    LEFT JOIN genres g ON b.genre = g.genre_id
    WHERE rl.user_id = ?
    GROUP BY rl.list_id
    ORDER BY rl.created_at DESC
";
$stmt = $conn->prepare($lists_query);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$reading_lists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profile-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="profile-container">
        <div class="profile-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-pic-container">
                    <img src="<?php echo htmlspecialchars($user['profile'] ?? '../assets/images/default-pfp.jpg'); ?>" 
                         alt="Profile Picture" 
                         class="profile-pic">
                    <?php if ($isOwnProfile): ?>
                        <label for="profile-upload" class="camera-icon">
                            <span class="material-symbols-outlined">photo_camera</span>
                        </label>
                        <input type="file" id="profile-upload" hidden accept="image/*">
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                    <div class="stats">
                        <span><?php echo $book_count; ?> Works</span>
                        <span><?php echo $list_count; ?> Reading Lists</span>
                    </div>
                </div>
            </div>

            <!-- Profile Navigation -->
            <div class="profile-tabs">
                <button class="tab active" data-tab="about">About</button>
                <button class="tab" data-tab="works">Works</button>
                <button class="tab" data-tab="lists">Reading Lists</button>
            </div>

            <!-- Tab Contents -->
            <div id="about" class="tab-content">
                <div class="bio-container">
                    <?php if ($isOwnProfile): ?>
                        <button id="edit-bio" class="edit-btn">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                    <?php endif; ?>
                    <div id="bio-text">
                        <?php echo nl2br(htmlspecialchars($user['bio'] ?? '')); ?>
                    </div>
                    <?php if ($isOwnProfile): ?>
                        <textarea id="bio-editor" class="hidden"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        <button id="save-bio" class="hidden">Save</button>
                    <?php endif; ?>
                </div>
            </div>

            <div id="works" class="tab-content hidden">
                <?php if (empty($books)): ?>
                    <div class="empty-message">No published works yet.</div>
                <?php else: ?>
                    <div class="books-grid">
                        <?php foreach ($books as $book): ?>
                            <div class="book-card" onclick="window.location.href='book.php?id=<?php echo $book['book_id']; ?>'">
                                <img src="<?php echo htmlspecialchars($book['cover'] ?? '../assets/images/default-book.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                                     class="book-cover">
                                <div class="book-info">
                                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                    <span class="genre"><?php echo htmlspecialchars($book['genre_name']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div id="lists" class="tab-content hidden">
                <?php if (empty($reading_lists)): ?>
                    <div class="empty-message">No reading lists yet.</div>
                <?php else: ?>
                    <div class="lists-container">
                        <?php foreach ($reading_lists as $list): ?>
                            <div class="list-card" data-list-id="<?php echo $list['list_id']; ?>">
                                <div class="list-header">
                                    <h3><?php echo htmlspecialchars($list['name']); ?></h3>
                                    <?php if (!empty($list['description'])): ?>
                                        <p class="list-description"><?php echo htmlspecialchars($list['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="list-stats">
                                        <span class="book-count"><?php echo $list['book_count']; ?> books</span>
                                        <?php if (!empty($list['genres'])): ?>
                                            <div class="genre-tags">
                                                <?php foreach (array_unique(explode(',', $list['genres'])) as $genre): ?>
                                                    <span class="genre-tag">#<?php echo strtolower(htmlspecialchars(trim($genre))); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="list-books hidden">
                                    <!-- Books will be loaded dynamically -->
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/profile.js"></script>
</body>
</html>