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
    
    // Ambil detail pesanan dengan informasi user
    $order = $db->orders->aggregate([
        [
            '$match' => [
                '_id' => new MongoDB\BSON\ObjectId($_GET['id'])
            ]
        ],
        [
            '$lookup' => [
                'from' => 'users',
                'localField' => 'user_id',
                'foreignField' => '_id',
                'as' => 'user'
            ]
        ],
        [
            '$unwind' => '$user'
        ]
    ])->toArray();

    if (empty($order)) {
        http_response_code(404);
        echo json_encode(['error' => 'Pesanan tidak ditemukan']);
        exit();
    }

    // Convert MongoDB BSON objects to PHP arrays/objects
    $order = json_decode(json_encode($order[0]), true);
    
    echo json_encode($order);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 