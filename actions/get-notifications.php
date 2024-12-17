<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("
        SELECT 
            n.*,
            b.title as book_title,
            u.username as author_name
        FROM notifications n
        JOIN books b ON n.book_id = b.book_id
        JOIN users u ON b.author = u.user_id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT 50 OFFSET ?
    ");
    
    $stmt->bind_param("ii", $_SESSION['user_id'], $offset);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}