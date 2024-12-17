<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    // Get JSON input
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
        // First verify that the user owns this book
        $verify_query = "SELECT is_published FROM books WHERE book_id = ? AND author = ?";
        $stmt = $conn->prepare($verify_query);
        $stmt->bind_param("ii", $book_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Book not found or unauthorized');
        }
        
        $current_status = $result->fetch_assoc()['is_published'];
        $new_status = $current_status ? 0 : 1;
        
        // Update the book publish status
        $update_query = "UPDATE books SET is_published = ?, updated_at = CURRENT_TIMESTAMP WHERE book_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $new_status, $book_id);
        $stmt->execute();

        // If unpublishing book, also unpublish all chapters
        if ($new_status === 0) {
            $update_chapters = "UPDATE chapters SET is_published = 0 WHERE book_id = ?";
            $stmt = $conn->prepare($update_chapters);
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'is_published' => $new_status
        ]);

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