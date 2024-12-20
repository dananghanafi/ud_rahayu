<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login admin
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $db = connectDB();
        $product_id = $_GET['id'];
        
        $product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
        
        if ($product) {
            // Konversi ObjectId ke string untuk JSON
            $product->_id = (string)$product->_id;
            
            // Kirim response sebagai JSON
            header('Content-Type: application/json');
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Produk tidak ditemukan']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID produk tidak diberikan']);
} 