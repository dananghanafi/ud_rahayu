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

try {
    $cart = getCart($_SESSION['user']['id']);
    $count = 0;

    if ($cart && isset($cart['items'])) {
        foreach ($cart['items'] as $item) {
            $count += $item['quantity'];
        }
    }

    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 