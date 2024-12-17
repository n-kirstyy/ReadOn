<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['name'])) {
        throw new Exception('Genre name is required');
    }

    $name = trim($data['name']);
    
    if (empty($name)) {
        throw new Exception('Genre name cannot be empty');
    }

    $conn = getDatabaseConnection();
    
    // Check if genre already exists
    $check_query = "SELECT 1 FROM genres WHERE name = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Genre already exists');
    }
    
    // Add new genre
    $insert_query = "INSERT INTO genres (name) VALUES (?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("s", $name);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to add genre');
    }
    
    echo json_encode([
        'success' => true,
        'genre_id' => $stmt->insert_id,
        'name' => $name
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();