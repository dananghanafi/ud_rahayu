<?php
session_start();
require_once '../../config/mongodb.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['cart_item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing cart item ID']);
    exit();
}

try {
    $db = connectToMongoDB();
    
    // Remove cart item
    $result = $db->cart->deleteOne([
        '_id' => new MongoDB\BSON\ObjectId($data['cart_item_id']),
        'user_id' => $_SESSION['user_id']
    ]);

    if ($result->getDeletedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found or already removed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 