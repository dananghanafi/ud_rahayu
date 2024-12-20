<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';

// Initialize MongoDB connection
$db = connectToMongoDB();

// Get current user's ID
$user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

echo "<pre>";
echo "Debug Information:\n";
echo "User ID: " . $user_id . "\n";
echo "Session Data:\n";
print_r($_SESSION);

// Get all orders (without filter first)
echo "\nAll Orders in Database:\n";
$all_orders = $db->orders->find()->toArray();
print_r($all_orders);

// Get user's orders
echo "\nUser's Orders:\n";
$user_orders = $db->orders->find(['user_id' => $user_id])->toArray();
print_r($user_orders);

// Check orders collection
echo "\nCollections in Database:\n";
$collections = $db->listCollections();
foreach ($collections as $collection) {
    echo $collection->getName() . "\n";
}
?> 