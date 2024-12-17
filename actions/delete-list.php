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
    
    if (!isset($data['list_id'])) {
        throw new Exception('List ID is required');
    }

    $list_id = (int)$data['list_id'];
    $user_id = $_SESSION['user_id'];

    $conn = getDatabaseConnection();
    
    // Verify ownership before deletion
    $verify_query = "SELECT 1 FROM reading_lists WHERE list_id = ? AND user_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $list_id, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('List not found or unauthorized');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete list books first
        $delete_books = "DELETE FROM list_books WHERE list_id = ?";
        $stmt = $conn->prepare($delete_books);
        $stmt->bind_param("i", $list_id);
        $stmt->execute();
        
        // Delete the list
        $delete_list = "DELETE FROM reading_lists WHERE list_id = ? AND user_id = ?";
        $stmt = $conn->prepare($delete_list);
        $stmt->bind_param("ii", $list_id, $user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('Failed to delete list');
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'List deleted successfully'
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