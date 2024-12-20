<?php
session_start();
require_once '../../config/mongodb.php';
require_once '../../config/database_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['shipping_info']) || !isset($input['payment_method']) || !isset($input['total_amount'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    // Get cart items
    $cart = getCart($_SESSION['user']['id']);
    if (!$cart || empty($cart['items'])) {
        throw new Exception('Keranjang kosong');
    }

    // Validate stock for all items
    $orderItems = [];
    foreach ($cart['items'] as $item) {
        $product = getProductById($item['product_id']);
        if (!$product) {
            throw new Exception('Produk tidak ditemukan');
        }

        if ($product['stock'] < $item['quantity']) {
            throw new Exception('Stok ' . $product['name'] . ' tidak mencukupi');
        }

        $orderItems[] = [
            'product_id' => $item['product_id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $item['quantity'],
            'subtotal' => $product['price'] * $item['quantity']
        ];

        // Update stock
        updateProduct($item['product_id'], [
            'stock' => $product['stock'] - $item['quantity']
        ]);
    }

    // Create order
    $orderId = createOrder(
        $_SESSION['user']['id'],
        $orderItems,
        $input['total_amount'],
        $input['shipping_info'],
        $input['payment_method']
    );

    if ($orderId) {
        // Add notification
        addNotification(
            $_SESSION['user']['id'],
            'Pesanan Berhasil',
            'Pesanan #' . $orderId . ' telah berhasil dibuat. Silakan lakukan pembayaran.',
            'order'
        );

        echo json_encode([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat',
            'order_id' => $orderId
        ]);
    } else {
        throw new Exception('Gagal membuat pesanan');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 