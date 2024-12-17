<?php
// actions/delete-user.php
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
    
    if (!isset($data['user_id'])) {
        throw new Exception('User ID is required');
    }

    $user_id = (int)$data['user_id'];
    
    // Don't allow admin to delete themselves
    if ($user_id === $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
    }

    $conn = getDatabaseConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete user's profile picture if it exists
        $get_profile = "SELECT profile FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($get_profile);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc()['profile'];
        
        if ($profile && $profile !== '../assets/images/default-pfp.jpg' && file_exists($profile)) {
            unlink($profile);
        }

        // Delete user's book covers
        $get_covers = "SELECT cover FROM books WHERE author = ?";
        $stmt = $conn->prepare($get_covers);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $covers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        foreach ($covers as $cover) {
            if ($cover['cover'] && $cover['cover'] !== '../assets/images/default-book.jpg' && file_exists($cover['cover'])) {
                unlink($cover['cover']);
            }
        }

        // Delete user (cascading will handle related records)
        $delete_user = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_user);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();

