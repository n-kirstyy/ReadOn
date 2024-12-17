<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    // Log the raw input
    $input = file_get_contents('php://input');
    error_log("Received input: " . $input);

    $data = json_decode($input, true);
    
    // Validate and log the decoded data
    error_log("Decoded data: " . print_r($data, true));
    
    if (!isset($data['chapter_id']) || !isset($data['comment'])) {
        throw new Exception('Missing required fields: chapter_id or comment');
    }

    $chapter_id = (int)$data['chapter_id'];
    $comment = trim($data['comment']);
    $parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : null;
    $user_id = $_SESSION['user_id'];

    // Log the processed values
    error_log("Processing comment - Chapter ID: $chapter_id, User ID: $user_id, Parent ID: " . 
             ($parent_id ?? 'null'));

    $conn = getDatabaseConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Verify chapter exists and is published
    $verify_query = "SELECT 1 FROM chapters WHERE chapter_id = ? AND is_published = 1";
    $stmt = $conn->prepare($verify_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare verify query: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $chapter_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute verify query: ' . $stmt->error);
    }
    
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Chapter not found or not published');
    }

    // If this is a reply, verify parent comment exists
    if ($parent_id !== null) {
        $verify_parent = "SELECT 1 FROM chapter_comments WHERE comment_id = ? AND chapter_id = ?";
        $stmt = $conn->prepare($verify_parent);
        if (!$stmt) {
            throw new Exception('Failed to prepare parent verify query: ' . $conn->error);
        }
        
        $stmt->bind_param("ii", $parent_id, $chapter_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute parent verify query: ' . $stmt->error);
        }
        
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception('Parent comment not found');
        }
    }

    // Insert comment
    $insert_query = "INSERT INTO chapter_comments (chapter_id, user_id, parent_id, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare insert query: ' . $conn->error);
    }
    
    $stmt->bind_param("iiis", $chapter_id, $user_id, $parent_id, $comment);
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert comment: ' . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'comment_id' => $stmt->insert_id
    ]);

} catch (Exception $e) {
    error_log("Error in post-comment.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>