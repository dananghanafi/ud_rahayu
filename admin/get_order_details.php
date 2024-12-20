<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login admin
checkAdmin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID pesanan tidak diberikan']);
    exit();
}

try {
    $db = connectDB();
    
    // Ambil detail pesanan
    $orderId = $_GET['id'];
    $order = $db->orders->findOne(['_id' => new MongoDB\BSON\ObjectId($orderId)]);
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Pesanan tidak ditemukan']);
        exit();
    }

    // Ambil informasi produk untuk setiap item
    $items = [];
    foreach ($order['items'] as $item) {
        try {
            $product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($item['product_id'])]);
            $items[] = [
                'product_name' => $product ? $product['name'] : 'Produk tidak ditemukan',
                'price' => (float)$item['price'],
                'quantity' => (int)$item['quantity']
            ];
        } catch (Exception $e) {
            $items[] = [
                'product_name' => 'Produk tidak ditemukan',
                'price' => (float)$item['price'],
                'quantity' => (int)$item['quantity']
            ];
        }
    }

    // Siapkan data response
    $orderData = [
        'order_id' => (string)$order['_id'],
        'order_date' => date('d/m/Y H:i', $order['order_date']->toDateTime()->getTimestamp()),
        'status' => $order['status'],
        'total_amount' => (float)$order['total_amount'],
        'customer_name' => $order['customer_name'] ?? 'Pelanggan',
        'items' => $items
    ];
    
    echo json_encode($orderData);
} catch (Exception $e) {
    error_log("Error in get_order_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Gagal memuat detail pesanan']);
} 