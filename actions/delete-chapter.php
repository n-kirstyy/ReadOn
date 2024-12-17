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
    
    if (!isset($data['chapter_id'])) {
        throw new Exception('Chapter ID is required');
    }

    $chapter_id = (int)$data['chapter_id'];
    $user_id = $_SESSION['user_id'];

    $conn = getDatabaseConnection();
    
    // Verify chapter ownership through book
    $verify_query = "
        SELECT 1 FROM chapters c
        JOIN books b ON c.book_id = b.book_id
        WHERE c.chapter_id = ? AND b.author = ?
    ";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $chapter_id, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Chapter not found or unauthorized');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete chapter comments
        $delete_comments = "DELETE FROM chapter_comments WHERE chapter_id = ?";
        $stmt = $conn->prepare($delete_comments);
        $stmt->bind_param("i", $chapter_id);
        $stmt->execute();
        
        // Delete chapter
        $delete_chapter = "DELETE FROM chapters WHERE chapter_id = ?";
        $stmt = $conn->prepare($delete_chapter);
        $stmt->bind_param("i", $chapter_id);
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