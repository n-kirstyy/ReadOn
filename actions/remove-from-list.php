<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['list_id']) || !isset($data['book_id'])) {
        throw new Exception('List ID and Book ID are required');
    }

    $list_id = (int)$data['list_id'];
    $book_id = (int)$data['book_id'];
    $user_id = $_SESSION['user_id'];

    $conn = getDatabaseConnection();
    
    // Verify list ownership
    $verify_query = "SELECT 1 FROM reading_lists WHERE list_id = ? AND user_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $list_id, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('List not found or unauthorized');
    }
    
    // Remove book from list
    $delete_query = "DELETE FROM list_books WHERE list_id = ? AND book_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $list_id, $book_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to remove book from list');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Book removed successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}