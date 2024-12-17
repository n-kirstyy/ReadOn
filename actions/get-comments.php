<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['chapter_id'])) {
        throw new Exception('Chapter ID is required');
    }

    $chapter_id = (int)$_GET['chapter_id'];
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    $conn = getDatabaseConnection();
    
    // Fetch top-level comments
    $comments_query = "
        SELECT 
            cc.*,
            u.username,
            u.profile as user_profile,
            (SELECT COUNT(*) FROM chapter_comments WHERE parent_id = cc.comment_id) as reply_count
        FROM chapter_comments cc
        JOIN users u ON cc.user_id = u.user_id
        WHERE cc.chapter_id = ? AND cc.parent_id IS NULL
        ORDER BY cc.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $conn->prepare($comments_query);
    $stmt->bind_param("iii", $chapter_id, $per_page, $offset);
    $stmt->execute();
    $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get total count for pagination
    $count_query = "
        SELECT COUNT(*) as total 
        FROM chapter_comments 
        WHERE chapter_id = ? AND parent_id IS NULL
    ";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $chapter_id);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];

    // For each top-level comment, fetch its replies
    foreach ($comments as &$comment) {
        if ($comment['reply_count'] > 0) {
            $replies_query = "
                SELECT 
                    cc.*,
                    u.username,
                    u.profile as user_profile
                FROM chapter_comments cc
                JOIN users u ON cc.user_id = u.user_id
                WHERE cc.parent_id = ?
                ORDER BY cc.created_at ASC
            ";
            
            $stmt = $conn->prepare($replies_query);
            $stmt->bind_param("i", $comment['comment_id']);
            $stmt->execute();
            $comment['replies'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $comment['replies'] = [];
        }
    }

    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'total' => $total,
        'has_more' => ($offset + $per_page) < $total
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