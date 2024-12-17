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
    
    // Validate required fields
    if (!isset($data['book_id']) || !isset($data['title']) || !isset($data['content'])) {
        throw new Exception('Missing required fields');
    }

    $book_id = (int)$data['book_id'];
    $chapter_id = $data['chapter_id'] ?? null;
    $number = (int)$data['number'];
    $title = trim($data['title']);
    $content = trim($data['content']);
    $is_published = (int)$data['is_published'];
    $user_id = $_SESSION['user_id'];

    $conn = getDatabaseConnection();
    
    // Verify book ownership
    $verify_query = "SELECT 1 FROM books WHERE book_id = ? AND author = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $book_id, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Book not found or unauthorized');
    }

    if ($chapter_id) {
        // Update existing chapter
        $query = "UPDATE chapters SET title = ?, content = ?, is_published = ? WHERE chapter_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $title, $content, $is_published, $chapter_id);
    } else {
        // Create new chapter
        $query = "INSERT INTO chapters (book_id, number, title, content, is_published) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissi", $book_id, $number, $title, $content, $is_published);
    }
    
    // Execute the chapter save/update query FIRST
    if (!$stmt->execute()) {
        throw new Exception('Failed to save chapter');
    }

    // Get the chapter_id if it's a new chapter
    $chapter_id = $chapter_id ?? $conn->insert_id;
    
    // After successfully saving a published chapter
    if ($is_published) {
        // Get all users who have this book in their library
        $notify_query = "
            SELECT DISTINCT l.user_id 
            FROM library l
            JOIN notification_settings ns ON l.user_id = ns.user_id AND l.book_id = ns.book_id
            WHERE l.book_id = ? AND ns.notify_updates = 1
        ";
        $stmt = $conn->prepare($notify_query);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Create notification for each user
        $insert_notification = "
            INSERT INTO notifications (user_id, book_id, chapter_id, type, message)
            VALUES (?, ?, ?, 'new_chapter', ?)
        ";
        $stmt = $conn->prepare($insert_notification);
        
        foreach ($users as $user) {
            $message = "New chapter available: " . $title;
            $stmt->bind_param("iiis", $user['user_id'], $book_id, $chapter_id, $message);
            $stmt->execute();
        }
    }
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>