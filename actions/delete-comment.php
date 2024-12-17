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
    
    if (!isset($data['comment_id'])) {
        throw new Exception('Comment ID is required');
    }

    $comment_id = (int)$data['comment_id'];
    $user_id = $_SESSION['user_id'];

    $conn = getDatabaseConnection();
    
    // Verify comment ownership or admin status
    $verify_query = "
        SELECT user_id 
        FROM chapter_comments 
        WHERE comment_id = ?
    ";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result || ($result['user_id'] !== $user_id && $_SESSION['role'] !== 2)) {
        throw new Exception('Unauthorized to delete this comment');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete replies first if any exist
        $delete_replies = "DELETE FROM chapter_comments WHERE parent_id = ?";
        $stmt = $conn->prepare($delete_replies);
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        
        // Delete the comment
        $delete_comment = "DELETE FROM chapter_comments WHERE comment_id = ?";
        $stmt = $conn->prepare($delete_comment);
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        
        $conn->commit();
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