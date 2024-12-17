<?php
session_start();
require_once '../db/db-config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['bio'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bio content is required']);
    exit();
}

$conn = getDatabaseConnection();

// Sanitize and validate bio
$bio = strip_tags($data['bio']);
if (strlen($bio) > 1000) { // Adjust max length as needed
    http_response_code(400);
    echo json_encode(['error' => 'Bio is too long']);
    exit();
}

// Update bio in database
$stmt = $conn->prepare("UPDATE users SET bio = ? WHERE user_id = ?");
$stmt->bind_param("si", $bio, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'bio' => $bio]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update bio']);
}

$stmt->close();
$conn->close();