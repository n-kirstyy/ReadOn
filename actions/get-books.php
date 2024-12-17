<?php
require_once '../db/db-config.php';

header('Content-Type: application/json');

// Get parameters
$page = max(1, intval($_GET['page'] ?? 1));
$genre = $_GET['genre'] ?? '';
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    $conn = getDatabaseConnection();
    
    // Base query
    $sql = "SELECT b.book_id, b.title, b.description, b.cover, 
                   u.username as author, g.name as genre
            FROM books b
            JOIN users u ON b.author = u.user_id
            JOIN genres g ON b.genre = g.genre_id
            WHERE b.is_published = 1";
    
    // Add genre filter if specified
    $params = [];
    $types = '';
    
    if (!empty($genre)) {
        $sql .= " AND b.genre = ?";
        $params[] = $genre;
        $types .= 'i';
    }
    
    // Add pagination
    $sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM books b WHERE b.is_published = 1";
    if (!empty($genre)) {
        $count_sql .= " AND b.genre = ?";
    }
    
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($genre)) {
        $count_stmt->bind_param('i', $genre);
    }
    
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'results' => $books,
        'hasMore' => ($offset + $per_page) < $total,
        'total' => $total
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch books'
    ]);
}

$conn->close();
?>