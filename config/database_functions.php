<?php
require_once 'mongodb.php';

// Authentication Functions
function authenticateUser($username, $password) {
    try {
        $database = connectToMongoDB();
        $user = $database->users->findOne(['username' => $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            return [
                'id' => (string)$user['_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return null;
    }
}

function createUser($userData) {
    try {
        $database = connectToMongoDB();
        
        // Check if username already exists
        $existingUser = $database->users->findOne(['username' => $userData['username']]);
        if ($existingUser) {
            return ['success' => false, 'message' => 'Username sudah digunakan'];
        }
        
        // Hash password
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Add timestamps
        $userData['created_at'] = new MongoDB\BSON\UTCDateTime();
        $userData['updated_at'] = new MongoDB\BSON\UTCDateTime();
        
        $result = $database->users->insertOne($userData);
        return ['success' => true, 'user_id' => (string)$result->getInsertedId()];
    } catch (Exception $e) {
        error_log("User creation error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal membuat user'];
    }
}

// Product Functions
function getAllProducts() {
    try {
        $database = connectToMongoDB();
        $cursor = $database->products->find();
        return $cursor->toArray();
    } catch (Exception $e) {
        error_log("Error getting products: " . $e->getMessage());
        return [];
    }
}

function getProductById($productId) {
    try {
        $database = connectToMongoDB();
        return $database->products->findOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);
    } catch (Exception $e) {
        error_log("Error getting product by ID: " . $e->getMessage());
        return null;
    }
}

function addProduct($productData) {
    try {
        $database = connectToMongoDB();
        $result = $database->products->insertOne($productData);
        return $result->getInsertedId();
    } catch (Exception $e) {
        error_log("Error adding product: " . $e->getMessage());
        return null;
    }
}

function updateProduct($productId, $productData) {
    try {
        $database = connectToMongoDB();
        $result = $database->products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($productId)],
            ['$set' => $productData]
        );
        return $result->getModifiedCount() > 0;
    } catch (Exception $e) {
        error_log("Error updating product: " . $e->getMessage());
        return false;
    }
}

function deleteProduct($productId) {
    try {
        $database = connectToMongoDB();
        $result = $database->products->deleteOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);
        return $result->getDeletedCount() > 0;
    } catch (Exception $e) {
        error_log("Error deleting product: " . $e->getMessage());
        return false;
    }
}

// User Functions
function getUsers($limit = null) {
    $database = connectToMongoDB();
    $options = [];
    if ($limit) {
        $options['limit'] = $limit;
    }
    return $database->users->find([], $options)->toArray();
}

function getUserById($userId) {
    try {
        $database = connectToMongoDB();
        return $database->users->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);
    } catch (Exception $e) {
        error_log("Error getting user by ID: " . $e->getMessage());
        return null;
    }
}

function updateUser($userId, $data) {
    $database = connectToMongoDB();
    return $database->users->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($userId)],
        ['$set' => array_merge($data, ['updated_at' => new MongoDB\BSON\UTCDateTime()])]
    );
}

// Cart Functions
function getCart($userId) {
    try {
        $database = connectToMongoDB();
        return $database->carts->findOne(['user_id' => $userId]);
    } catch (Exception $e) {
        error_log("Error getting cart: " . $e->getMessage());
        return null;
    }
}

function addToCart($userId, $productId, $quantity) {
    try {
        $database = connectToMongoDB();
        $cart = getCart($userId);
        
        if (!$cart) {
            // Create new cart
            $result = $database->carts->insertOne([
                'user_id' => $userId,
                'items' => [
                    [
                        'product_id' => $productId,
                        'quantity' => $quantity
                    ]
                ],
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]);
            return $result->getInsertedCount() > 0;
        }
        
        // Update existing cart
        $existingItem = false;
        foreach ($cart['items'] as &$item) {
            if ($item['product_id'] == $productId) {
                $item['quantity'] += $quantity;
                $existingItem = true;
                break;
            }
        }
        
        if (!$existingItem) {
            $cart['items'][] = [
                'product_id' => $productId,
                'quantity' => $quantity
            ];
        }
        
        $result = $database->carts->updateOne(
            ['user_id' => $userId],
            [
                '$set' => [
                    'items' => $cart['items'],
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );
        
        return $result->getModifiedCount() > 0;
    } catch (Exception $e) {
        error_log("Error adding to cart: " . $e->getMessage());
        return false;
    }
}

function updateCartItem($userId, $productId, $quantity) {
    try {
        $database = connectToMongoDB();
        $cart = getCart($userId);
        
        if (!$cart) {
            return false;
        }
        
        foreach ($cart['items'] as &$item) {
            if ($item['product_id'] == $productId) {
                $item['quantity'] = $quantity;
                break;
            }
        }
        
        $result = $database->carts->updateOne(
            ['user_id' => $userId],
            [
                '$set' => [
                    'items' => $cart['items'],
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );
        
        return $result->getModifiedCount() > 0;
    } catch (Exception $e) {
        error_log("Error updating cart item: " . $e->getMessage());
        return false;
    }
}

function removeFromCart($userId, $productId) {
    try {
        $database = connectToMongoDB();
        $cart = getCart($userId);
        
        if (!$cart) {
            return false;
        }
        
        $items = array_filter($cart['items'], function($item) use ($productId) {
            return $item['product_id'] != $productId;
        });
        
        $result = $database->carts->updateOne(
            ['user_id' => $userId],
            [
                '$set' => [
                    'items' => array_values($items),
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );
        
        return $result->getModifiedCount() > 0;
    } catch (Exception $e) {
        error_log("Error removing from cart: " . $e->getMessage());
        return false;
    }
}

// Order Functions
function createOrder($userId, $items, $totalAmount, $shippingInfo, $paymentMethod) {
    try {
        $database = connectToMongoDB();
        
        // Generate order ID
        $orderId = uniqid('ORD');
        
        $result = $database->orders->insertOne([
            'order_id' => $orderId,
            'user_id' => $userId,
            'items' => $items,
            'total_amount' => $totalAmount,
            'shipping_info' => $shippingInfo,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        
        if ($result->getInsertedCount() > 0) {
            // Clear user's cart
            $database->carts->deleteOne(['user_id' => $userId]);
            return $orderId;
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Error creating order: " . $e->getMessage());
        return null;
    }
}

function getUserOrders($userId, $limit = null) {
    $database = connectToMongoDB();
    $options = [];
    if ($limit) {
        $options['limit'] = $limit;
    }
    return $database->orders->find(
        ['user_id' => $userId],
        array_merge($options, ['sort' => ['created_at' => -1]])
    )->toArray();
}

function getAllOrders($limit = null) {
    $database = connectToMongoDB();
    $options = [];
    if ($limit) {
        $options['limit'] = $limit;
    }
    return $database->orders->find(
        [],
        array_merge($options, ['sort' => ['created_at' => -1]])
    )->toArray();
}

function updateOrderStatus($orderId, $status) {
    $database = connectToMongoDB();
    return $database->orders->updateOne(
        ['order_id' => $orderId],
        [
            '$set' => [
                'status' => $status,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );
}

// Dashboard Stats Functions
function getDashboardStats() {
    $database = connectToMongoDB();
    
    $totalUsers = $database->users->countDocuments(['role' => 'customer']);
    $totalProducts = $database->products->countDocuments();
    $totalOrders = $database->orders->countDocuments();
    
    // Calculate today's sales
    $today = new MongoDB\BSON\UTCDateTime(strtotime('today') * 1000);
    $todaySales = $database->orders->aggregate([
        [
            '$match' => [
                'created_at' => ['$gte' => $today],
                'status' => ['$in' => ['completed', 'processing']]
            ]
        ],
        [
            '$group' => [
                '_id' => null,
                'total' => ['$sum' => '$total']
            ]
        ]
    ])->toArray();

    return [
        'total_users' => $totalUsers,
        'total_products' => $totalProducts,
        'total_orders' => $totalOrders,
        'today_sales' => $todaySales[0]['total'] ?? 0
    ];
}

// Report Functions
function getSalesReport($period = 'daily') {
    $database = connectToMongoDB();
    
    $groupBy = [];
    switch ($period) {
        case 'daily':
            $groupBy = [
                'year' => ['$year' => '$created_at'],
                'month' => ['$month' => '$created_at'],
                'day' => ['$dayOfMonth' => '$created_at']
            ];
            break;
        case 'monthly':
            $groupBy = [
                'year' => ['$year' => '$created_at'],
                'month' => ['$month' => '$created_at']
            ];
            break;
        case 'yearly':
            $groupBy = [
                'year' => ['$year' => '$created_at']
            ];
            break;
    }

    return $database->orders->aggregate([
        [
            '$match' => [
                'status' => ['$in' => ['completed', 'processing']]
            ]
        ],
        [
            '$group' => [
                '_id' => $groupBy,
                'total_sales' => ['$sum' => '$total'],
                'order_count' => ['$sum' => 1]
            ]
        ],
        [
            '$sort' => ['_id' => -1]
        ],
        [
            '$limit' => 30
        ]
    ])->toArray();
}

// Notification Functions
function getNotifications($userId, $limit = 5) {
    $database = connectToMongoDB();
    return $database->notifications->find(
        ['user_id' => $userId],
        [
            'sort' => ['created_at' => -1],
            'limit' => $limit
        ]
    )->toArray();
}

function addNotification($userId, $title, $message, $type = 'general') {
    try {
        $database = connectToMongoDB();
        $result = $database->notifications->insertOne([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'read' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        return $result->getInsertedCount() > 0;
    } catch (Exception $e) {
        error_log("Error adding notification: " . $e->getMessage());
        return false;
    }
}

function markNotificationAsRead($notificationId) {
    $database = connectToMongoDB();
    return $database->notifications->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($notificationId)],
        ['$set' => ['read' => true]]
    );
}

// Helper Functions
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate($date) {
    if ($date instanceof MongoDB\BSON\UTCDateTime) {
        return $date->toDateTime()->format('d M Y H:i');
    }
    return date('d M Y H:i', strtotime($date));
} 