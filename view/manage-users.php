<?php
session_start();
require_once '../db/db-config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 2) {
    header('Location: index.php');
    exit();
}

$conn = getDatabaseConnection();

// Get all users with their stats
$query = "
    SELECT 
        u.user_id,
        u.username,
        u.email,
        u.date_joined,
        u.profile,
        COUNT(DISTINCT b.book_id) as total_books,
        COUNT(DISTINCT cc.comment_id) as total_comments
    FROM users u
    LEFT JOIN books b ON u.user_id = b.author
    LEFT JOIN chapter_comments cc ON u.user_id = cc.user_id
    GROUP BY u.user_id
    ORDER BY u.date_joined DESC
";

$users = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-manage.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="admin-container">
        <header class="admin-header">
            <h1>Manage Users</h1>
            <div class="search-bar">
                <input type="text" id="userSearch" placeholder="Search users...">
                <span class="material-symbols-outlined">search</span>
            </div>
        </header>

        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Join Date</th>
                        <th>Books</th>
                        <th>Comments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr data-user-id="<?php echo $user['user_id']; ?>">
                            <td>
                                <div class="user-info">
                                    <img src="<?php echo htmlspecialchars($user['profile'] ?? '../assets/images/default-pfp.jpg'); ?>" 
                                         alt="Profile picture" 
                                         class="user-avatar">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($user['date_joined'])); ?></td>
                            <td><?php echo $user['total_books']; ?></td>
                            <td><?php echo $user['total_comments']; ?></td>
                            <td>
                                <div class="actions">
                                    <a href="profile.php?id=<?php echo $user['user_id']; ?>" 
                                       class="action-btn view" 
                                       title="View Profile">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </a>
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <button class="action-btn delete" 
                                                title="Delete User"
                                                onclick="deleteUser(<?php echo $user['user_id']; ?>)">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="../assets/js/manage-users.js"></script>
</body>
</html>