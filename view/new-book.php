<?php
session_start();
require_once '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get genres for dropdown
$conn = getDatabaseConnection();
$genres_query = "SELECT genre_id, name FROM genres ORDER BY name";
$genres = $conn->query($genres_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Book | ReadOn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/new-book-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="new-book-container">
        <h1>Create New Book</h1>

        <form id="newBookForm" class="new-book-form" enctype="multipart/form-data">
            <div class="cover-upload">
                <img id="coverPreview" src="../assets/images/default-book.jpg" alt="Book Cover">
                <label for="coverInput" class="upload-btn">
                    <span class="material-symbols-outlined">photo_camera</span>
                    <span>Change Cover</span>
                </label>
                <input type="file" id="coverInput" name="cover" accept="image/*" hidden>
            </div>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required maxlength="20">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" maxlength="400"></textarea>
            </div>

            <div class="form-group">
                <label for="genre">Genre</label>
                <select id="genre" name="genre" required>
                    <option value="">Select a genre</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?php echo $genre['genre_id']; ?>">
                            <?php echo htmlspecialchars($genre['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="window.location.href='write.php'">Cancel</button>
                <div class="save-options">
                    <button type="button" class="draft-btn" data-action="draft">Save as Draft</button>
                    <button type="button" class="publish-btn" data-action="publish">Save & Publish</button>
                </div>
            </div>
        </form>
    </main>

    <script src="../assets/js/new-book.js"></script>
</body>
</html>