<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['list_id'])) {
        throw new Exception('List ID is required');
    }

    $list_id = (int)$_GET['list_id'];
    
    $conn = getDatabaseConnection();
    
    // Get list details first
    $list_query = "SELECT name, description FROM reading_lists WHERE list_id = ?";
    $stmt = $conn->prepare($list_query);
    $stmt->bind_param("i", $list_id);
    $stmt->execute();
    $list = $stmt->get_result()->fetch_assoc();
    
    if (!$list) {
        throw new Exception('List not found');
    }
    
    // Get books in the list
    $books_query = "
        SELECT 
            b.book_id,
            b.title,
            b.description,
            b.cover,
            u.username as author,
            u.user_id as author_id,
            COUNT(DISTINCT bl.like_id) as likes_count,
            COUNT(DISTINCT c.chapter_id) as chapter_count
        FROM list_books lb
        JOIN books b ON lb.book_id = b.book_id
        JOIN users u ON b.author = u.user_id
        LEFT JOIN book_likes bl ON b.book_id = bl.book_id
        LEFT JOIN chapters c ON b.book_id = c.book_id AND c.is_published = 1
        WHERE lb.list_id = ? AND b.is_published = 1
        GROUP BY b.book_id
        ORDER BY lb.added_at DESC
    ";
    
    $stmt = $conn->prepare($books_query);
    $stmt->bind_param("i", $list_id);
    $stmt->execute();
    $books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'list' => $list,
        'books' => $books
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>