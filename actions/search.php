<?php
require_once '../db/db-config.php';

header('Content-Type: application/json');

// Get search parameters
$query = $_GET['query'] ?? '';
$type = $_GET['type'] ?? 'books';
$genre = $_GET['genre'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$conn = getDatabaseConnection();
$results = [];

try {
    if ($type === 'books') {
        // Base query for books
        $sql = "SELECT b.book_id, b.title, b.description, b.cover, 
                       u.username as author, g.name as genre
                FROM books b
                JOIN users u ON b.author = u.user_id
                JOIN genres g ON b.genre = g.genre_id
                WHERE b.is_published = 1";
        
        // Add search conditions
        if (!empty($query)) {
            $sql .= " AND (b.title LIKE ? OR u.username LIKE ?)";
        }
        
        // Add genre filter
        if (!empty($genre)) {
            $sql .= " AND b.genre = ?";
        }
        
        // Add pagination
        $sql .= " LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        if (!empty($query) && !empty($genre)) {
            $search = "%$query%";
            $stmt->bind_param("sssii", $search, $search, $genre, $per_page, $offset);
        } elseif (!empty($query)) {
            $search = "%$query%";
            $stmt->bind_param("ssii", $search, $search, $per_page, $offset);
        } elseif (!empty($genre)) {
            $stmt->bind_param("sii", $genre, $per_page, $offset);
        } else {
            $stmt->bind_param("ii", $per_page, $offset);
        }
        
    } else {
        // Search for profiles
        $sql = "SELECT u.user_id, u.username, u.profile as avatar,
                       COUNT(DISTINCT b.book_id) as books_count
                FROM users u
                LEFT JOIN books b ON u.user_id = b.author
                WHERE u.username LIKE ?
                GROUP BY u.user_id
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $search = "%$query%";
        $stmt->bind_param("sii", $search, $per_page, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'results' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while searching'
    ]);
}

$conn->close();
?>