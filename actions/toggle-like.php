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
    
    // Check if already liked
    $check_query = "SELECT like_id FROM book_likes WHERE book_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $book_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Unlike
        $delete_query = "DELETE FROM book_likes WHERE book_id = ? AND user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $book_id, $user_id);
        $stmt->execute();
        $is_liked = false;
    } else {
        // Like
        $insert_query = "INSERT INTO book_likes (book_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $book_id, $user_id);
        $stmt->execute();
        $is_liked = true;
    }

    // Get updated like count
    $count_query = "SELECT COUNT(*) as like_count FROM book_likes WHERE book_id = ?";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $likes_count = $stmt->get_result()->fetch_assoc()['like_count'];

    echo json_encode([
        'success' => true,
        'liked' => $is_liked,
        'likes_count' => $likes_count
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>