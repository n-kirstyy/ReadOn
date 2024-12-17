<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['book_id'])) {
        throw new Exception('Book ID is required');
    }

    $book_id = (int)$data['book_id'];
    $user_id = $_SESSION['user_id'];

    $conn = getDatabaseConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First verify ownership and get cover path
        $verify_query = "SELECT cover FROM books WHERE book_id = ? AND author = ?";
        $stmt = $conn->prepare($verify_query);
        $stmt->bind_param("ii", $book_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Book not found or unauthorized');
        }

        $cover_path = $result->fetch_assoc()['cover'];

        // Delete in this order to maintain referential integrity
        // 1. Delete chapter comments
        $delete_comments = "DELETE cc FROM chapter_comments cc 
                          INNER JOIN chapters c ON cc.chapter_id = c.chapter_id 
                          WHERE c.book_id = ?";
        $stmt = $conn->prepare($delete_comments);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        
        // 2. Delete chapters
        $delete_chapters = "DELETE FROM chapters WHERE book_id = ?";
        $stmt = $conn->prepare($delete_chapters);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        
        // 3. Delete from reading lists
        $delete_from_lists = "DELETE FROM list_books WHERE book_id = ?";
        $stmt = $conn->prepare($delete_from_lists);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        
        // 4. Delete from library
        $delete_from_library = "DELETE FROM library WHERE book_id = ?";
        $stmt = $conn->prepare($delete_from_library);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        
        // 5. Delete likes
        $delete_likes = "DELETE FROM book_likes WHERE book_id = ?";
        $stmt = $conn->prepare($delete_likes);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        
        // 6. Finally delete the book
        $delete_book = "DELETE FROM books WHERE book_id = ? AND author = ?";
        $stmt = $conn->prepare($delete_book);
        $stmt->bind_param("ii", $book_id, $user_id);
        $stmt->execute();
        
        $conn->commit();

        // Delete cover image if it's not the default
        if ($cover_path !== '../assets/images/default-book.jpg' && file_exists($cover_path)) {
            unlink($cover_path);
        }
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>