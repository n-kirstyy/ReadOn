<?php
session_start();
include '../db/db-config.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['name'])) {
        throw new Exception('List name is required');
    }

    $name = trim($data['name']);
    $description = isset($data['description']) ? trim($data['description']) : '';
    $user_id = $_SESSION['user_id'];

    if (empty($name)) {
        throw new Exception('List name cannot be empty');
    }

    $conn = getDatabaseConnection();
    
    // First check if user already has a list with this name
    $check_query = "SELECT 1 FROM reading_lists WHERE user_id = ? AND name = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("is", $user_id, $name);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('You already have a list with this name');
    }

    // Create new list
    $insert_query = "INSERT INTO reading_lists (user_id, name, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iss", $user_id, $name, $description);

    if (!$stmt->execute()) {
        throw new Exception('Failed to create list: ' . $conn->error);
    }

    echo json_encode([
        'success' => true,
        'list_id' => $stmt->insert_id,
        'message' => 'List created successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
