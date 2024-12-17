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
        SELECT c.is_published 
        FROM chapters c
        JOIN books b ON c.book_id = b.book_id
        WHERE c.chapter_id = ? AND b.author = ?
    ";
    
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $chapter_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Chapter not found or unauthorized');
    }

    $current_status = $result->fetch_assoc()['is_published'];
    $new_status = $current_status ? 0 : 1;
    
    // Toggle publish status
    $update_query = "UPDATE chapters SET is_published = ? WHERE chapter_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $new_status, $chapter_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update chapter status');
    }
    
    echo json_encode([
        'success' => true,
        'is_published' => $new_status
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>