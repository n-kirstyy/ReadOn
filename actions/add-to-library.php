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
        // Check if book exists and is published
        $verify_query = "SELECT 1 FROM books WHERE book_id = ? AND is_published = 1";
        $stmt = $conn->prepare($verify_query);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception('Book not found or not published');
        }

        // Check if already in library
        $check_query = "SELECT 1 FROM library WHERE user_id = ? AND book_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        
        $in_library = $stmt->get_result()->num_rows > 0;

        if ($in_library) {
            // Remove from library and notification settings
            $delete_library = "DELETE FROM library WHERE user_id = ? AND book_id = ?";
            $stmt = $conn->prepare($delete_library);
            $stmt->bind_param("ii", $user_id, $book_id);
            $stmt->execute();
            
            $delete_settings = "DELETE FROM notification_settings WHERE user_id = ? AND book_id = ?";
            $stmt = $conn->prepare($delete_settings);
            $stmt->bind_param("ii", $user_id, $book_id);
            $stmt->execute();
            
            $in_library = false;
        } else {
            // Add to library
            $insert_library = "INSERT INTO library (user_id, book_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_library);
            $stmt->bind_param("ii", $user_id, $book_id);
            $stmt->execute();
            
            // Initialize notification settings
            $insert_settings = "INSERT INTO notification_settings (user_id, book_id, notify_updates) VALUES (?, ?, 1)";
            $stmt = $conn->prepare($insert_settings);
            $stmt->bind_param("ii", $user_id, $book_id);
            $stmt->execute();
            
            $in_library = true;
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'in_library' => $in_library
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
