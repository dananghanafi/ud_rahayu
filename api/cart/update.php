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

if (!isset($data['cart_item_id']) || !isset($data['quantity_change'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $db = connectToMongoDB();
    
    // Get cart item
    $cart_item = $db->cart->findOne([
        '_id' => new MongoDB\BSON\ObjectId($data['cart_item_id']),
        'user_id' => $_SESSION['user_id']
    ]);

    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit();
    }

    // Get product to check stock
    $product = $db->products->findOne(['_id' => $cart_item->product_id]);
    
    // Calculate new quantity
    $new_quantity = $cart_item->quantity + $data['quantity_change'];

    // Validate new quantity
    if ($new_quantity <= 0) {
        // Remove item if quantity would be 0 or less
        $db->cart->deleteOne(['_id' => new MongoDB\BSON\ObjectId($data['cart_item_id'])]);
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        exit();
    }

    if ($new_quantity > $product->stock) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit();
    }

    // Update quantity
    $db->cart->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($data['cart_item_id'])],
        ['$set' => ['quantity' => $new_quantity]]
    );

    echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 