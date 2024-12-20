<?php
session_start();
require_once '../../config/mongodb.php';
require_once '../../config/database_functions.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$category = $_POST['category'] ?? '';

// Validate input
if (empty($name) || empty($description) || $price <= 0 || $stock < 0 || empty($category)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi dengan benar']);
    exit();
}

// Handle image upload
$imagePath = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image'];
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = '../../assets/images/products/' . $fileName;
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tipe file tidak valid. Hanya JPG, PNG dan GIF yang diperbolehkan']);
        exit();
    }
    
    // Validate file size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB']);
        exit();
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $imagePath = '/assets/images/products/' . $fileName;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload gambar']);
        exit();
    }
}

try {
    // Create product data
    $productData = [
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'stock' => $stock,
        'category' => $category,
        'image' => $imagePath,
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];

    // Add product to database
    $database = connectToMongoDB();
    $result = $database->products->insertOne($productData);

    if ($result->getInsertedCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'product_id' => (string)$result->getInsertedId()
        ]);
    } else {
        throw new Exception('Gagal menambahkan produk');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 