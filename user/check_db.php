<?php
session_start();
require_once '../config/mongodb.php';
require_once '../config/config.php';
require_once '../includes/auth_check.php';

echo "<pre>";
echo "Database Check Results:\n\n";

try {
    // Test MongoDB connection
    $db = connectDB();
    echo "✅ MongoDB connection successful\n";
    
    // Check session data
    echo "\nSession Data:\n";
    print_r($_SESSION);
    
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
        echo "\nUser ID: " . $user_id . "\n";
        
        // List all collections
        echo "\nCollections in Database:\n";
        $collections = $db->listCollections();
        foreach ($collections as $collection) {
            echo "- " . $collection->getName() . "\n";
        }
        
        // Check orders collection
        echo "\nOrders Collection Check:\n";
        $totalOrders = $db->orders->countDocuments();
        echo "Total orders in database: " . $totalOrders . "\n";
        
        // Check user's orders
        $userOrders = $db->orders->countDocuments(['user_id' => $user_id]);
        echo "Orders for current user: " . $userOrders . "\n";
        
        // Show last 5 orders for debugging
        echo "\nLast 5 Orders:\n";
        $recentOrders = $db->orders->find(
            ['user_id' => $user_id],
            [
                'sort' => ['order_date' => -1],
                'limit' => 5
            ]
        )->toArray();
        
        foreach ($recentOrders as $order) {
            echo "\nOrder ID: " . $order['_id'];
            echo "\nDate: " . $order['order_date']->toDateTime()->format('Y-m-d H:i:s');
            echo "\nTotal: " . $order['total_amount'];
            echo "\nStatus: " . $order['status'];
            echo "\n-------------------\n";
        }
    } else {
        echo "\n❌ User not logged in\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
}
?> 