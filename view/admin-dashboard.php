<?php
session_start();
require_once '../db/db-config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 2) {
    header('Location: index.php');
    exit();
}

$conn = getDatabaseConnection();

// Get overall statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total books and published books
$result = $conn->query("
    SELECT 
        COUNT(*) as total_books,
        SUM(is_published = 1) as published_books 
    FROM books
");
$books = $result->fetch_assoc();
$stats['total_books'] = $books['total_books'];
$stats['published_books'] = $books['published_books'];

// Total comments
$result = $conn->query("SELECT COUNT(*) as count FROM chapter_comments");
$stats['total_comments'] = $result->fetch_assoc()['count'];

// Get top 5 active users
$query = "
    SELECT 
        u.user_id,
        u.username,
        u.profile,
        COUNT(DISTINCT b.book_id) as total_books,
        COUNT(DISTINCT c.comment_id) as total_comments,
        (COUNT(DISTINCT b.book_id) + COUNT(DISTINCT c.comment_id)) as activity_score
    FROM users u
    LEFT JOIN books b ON u.user_id = b.author
    LEFT JOIN chapter_comments c ON u.user_id = c.user_id
    GROUP BY u.user_id
    ORDER BY activity_score DESC
    LIMIT 5
";

$result = $conn->query($query);
$top_users = $result->fetch_all(MYSQLI_ASSOC);

// Calculate the highest activity score for percentage calculations
$max_activity = 0;
foreach ($top_users as $user) {
    $max_activity = max($max_activity, $user['activity_score']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - ReadOn</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard-style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="value"><?php echo number_format($stats['total_users']); ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Total Books</h3>
                <div class="value"><?php echo number_format($stats['total_books']); ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Published Books</h3>
                <div class="value"><?php echo number_format($stats['published_books']); ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Total Comments</h3>
                <div class="value"><?php echo number_format($stats['total_comments']); ?></div>
            </div>
        </div>

        <!-- Top Users Table -->
        <div class="users-table">
            <h3>Top Active Users</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>User</th>
                            <th>Books</th>
                            <th>Comments</th>
                            <th>Activity Score</th>
                            <th>Activity Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_users as $index => $user): 
                            $percentage = ($user['activity_score'] / $max_activity) * 100;
                        ?>
                            <tr>
                                <td>
                                    <div class="rank">
                                        <span class="rank-number"><?php echo $index + 1; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <a href="profile.php?id=<?php echo $user['user_id']; ?>">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </a>
                                </td>
                                <td><?php echo $user['total_books']; ?></td>
                                <td><?php echo $user['total_comments']; ?></td>
                                <td><?php echo $user['activity_score']; ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-dashboard.js"></script>
</body>
</html>
