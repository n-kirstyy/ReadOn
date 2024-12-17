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
    $stats = [];
    
    // Get total users
    $query = "SELECT COUNT(*) as count FROM users";
    $result = $conn->query($query);
    $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // Get books statistics
    $query = "SELECT 
        COUNT(*) as total_books,
        SUM(is_published = 1) as published_books
        FROM books";
    $result = $conn->query($query);
    $books = $result->fetch_assoc();
    $stats['total_books'] = $books['total_books'];
    $stats['published_books'] = $books['published_books'];
    
    // Get total comments
    $query = "SELECT COUNT(*) as count FROM chapter_comments";
    $result = $conn->query($query);
    $stats['total_comments'] = $result->fetch_assoc()['count'];
    
    // Get new users in last 24 hours
    $query = "SELECT COUNT(*) as count FROM users 
              WHERE date_joined >= NOW() - INTERVAL 24 HOUR";
    $result = $conn->query($query);
    $stats['new_users_24h'] = $result->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch statistics'
    ]);
}

$conn->close();
