<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    $conn = getDatabaseConnection();
    
    $query = "
        SELECT 
            u.user_id,
            u.username,
            u.profile,
            COUNT(DISTINCT b.book_id) as total_books,
            COUNT(DISTINCT c.comment_id) as total_comments,
            (COUNT(DISTINCT b.book_id) + COUNT(DISTINCT c.comment_id)) as activity_score,
            MAX(GREATEST(b.created_at, c.created_at)) as last_activity
        FROM users u
        LEFT JOIN books b ON u.user_id = b.author
        LEFT JOIN chapter_comments c ON u.user_id = c.user_id
        GROUP BY u.user_id
        ORDER BY activity_score DESC, last_activity DESC
        LIMIT 5
    ";
    
    $result = $conn->query($query);
    $users = $result->fetch_all(MYSQLI_ASSOC);
    
    // Calculate activity percentages
    if (!empty($users)) {
        $maxActivity = max(array_column($users, 'activity_score'));
        foreach ($users as &$user) {
            $user['activity_percentage'] = ($user['activity_score'] / $maxActivity) * 100;
        }
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch user rankings'
    ]);
}

$conn->close();
