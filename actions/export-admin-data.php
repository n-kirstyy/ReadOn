<?php
session_start();
require_once '../db/db-config.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 2) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    $conn = getDatabaseConnection();
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="admin-dashboard-export-' . date('Y-m-d') . '.csv"');
    
    // Create output handle
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($output, [
        'Username',
        'Join Date',
        'Total Books',
        'Published Books',
        'Total Comments',
        'Last Activity'
    ]);
    
    // Get user data
    $query = "
        SELECT 
            u.username,
            u.date_joined,
            COUNT(DISTINCT b.book_id) as total_books,
            SUM(b.is_published = 1) as published_books,
            COUNT(DISTINCT c.comment_id) as total_comments,
            MAX(GREATEST(COALESCE(b.created_at, '1970-01-01'), 
                        COALESCE(c.created_at, '1970-01-01'))) as last_activity
        FROM users u
        LEFT JOIN books b ON u.user_id = b.author
        LEFT JOIN chapter_comments c ON u.user_id = c.user_id
        GROUP BY u.user_id
        ORDER BY last_activity DESC
    ";
    
    $result = $conn->query($query);
    
    // Write data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['username'],
            $row['date_joined'],
            $row['total_books'],
            $row['published_books'],
            $row['total_comments'],
            $row['last_activity']
        ]);
    }
    
    fclose($output);

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Failed to export data'
    ]);
}

$conn->close();
