<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';

// Initialize MongoDB connection
$db = connectToMongoDB();
$user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

// Sample orders data
$sample_orders = [
    [
        'user_id' => $user_id,
        'order_date' => new MongoDB\BSON\UTCDateTime(),
        'total_amount' => 48000,
        'status' => 'pending',
        'items' => [
            [
                'product_id' => new MongoDB\BSON\ObjectId(),
                'name' => 'Holiday fellas',
                'price' => 25000,
                'quantity' => 1
            ],
            [
                'product_id' => new MongoDB\BSON\ObjectId(),
                'name' => 'Kopsu berry',
                'price' => 23000,
                'quantity' => 1
            ]
        ],
        'shipping_address' => 'Jl. Contoh No. 123',
        'payment_method' => 'transfer_bank'
    ]
];

try {
    // Clear existing orders for testing
    $db->orders->deleteMany(['user_id' => $user_id]);
    
    // Insert sample orders
    foreach ($sample_orders as $order) {
        $result = $db->orders->insertOne($order);
        if ($result->getInsertedId()) {
            echo "Order added successfully with ID: " . $result->getInsertedId() . "<br>";
        }
    }
    
    echo "<br>Sample orders added successfully!<br>";
    echo "<a href='check_orders.php'>Back to Orders</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 