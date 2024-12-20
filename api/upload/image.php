<?php
session_start();
require_once '../../config/mongodb.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
    exit();
}

try {
    $file = $_FILES['image'];
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = '../../assets/images/products/' . $fileName;
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
    }
    
    // Validate file size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new Exception('File is too large. Maximum size is 2MB.');
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'path' => '/assets/images/products/' . $fileName
        ]);
    } else {
        throw new Exception('Failed to move uploaded file.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 