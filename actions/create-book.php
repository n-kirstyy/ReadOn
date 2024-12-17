<?php
session_start();
require_once '../db/db-config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    // Validate required fields
    if (empty($_POST['title']) || empty($_POST['genre'])) {
        throw new Exception('Title and genre are required');
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $genre = (int)$_POST['genre'];
    $is_published = (int)$_POST['is_published'];
    $author = $_SESSION['user_id'];

    // Handle cover upload
    $cover_path = '../assets/images/default-book.jpg';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file = $_FILES['cover'];
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed');
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File is too large. Maximum size is 5MB');
        }
        
        // Create upload directory if it doesn't exist
        $upload_dir = '../assets/images/covers/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cover_') . '.' . $extension;
        $cover_path = $upload_dir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $cover_path)) {
            throw new Exception('Failed to upload cover image');
        }
    }

    $conn = getDatabaseConnection();
    
    // Insert book
    $query = "INSERT INTO books (title, description, author, cover, genre, is_published) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisii", $title, $description, $author, $cover_path, $genre, $is_published);
    
    if (!$stmt->execute()) {
        // Delete uploaded cover if insertion fails
        if ($cover_path !== '../assets/images/default-book.jpg' && file_exists($cover_path)) {
            unlink($cover_path);
        }
        throw new Exception('Failed to create book');
    }
    
    echo json_encode([
        'success' => true,
        'book_id' => $stmt->insert_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>