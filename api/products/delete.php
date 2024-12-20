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

// Get product ID from request
$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? null;

if (!$productId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit();
}

try {
    // Get product to delete its image
    $product = getProductById($productId);
    if ($product && !empty($product['image'])) {
        $imagePath = '../../' . ltrim($product['image'], '/');
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete product from database
    $result = deleteProduct($productId);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ]);
    } else {
        throw new Exception('Gagal menghapus produk');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 