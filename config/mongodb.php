<?php
require_once __DIR__ . '/../vendor/autoload.php';

function connectToMongoDB() {
    try {
        // MongoDB connection string
        $uri = "mongodb://localhost:27017";
        
        // Create a new client and connect to the server
        $client = new MongoDB\Client($uri);
        
        // Select database
        $database = $client->ud_rahayu;
        
        // Ping the deployment to confirm connection
        $client->selectDatabase('admin')->command(['ping' => 1]);
        
        return $database;
    } catch (Exception $e) {
        error_log("MongoDB Connection Error: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}

// Fungsi untuk produk
function getProducts($limit = 6) {
    $db = connectToMongoDB();
    try {
        $cursor = $db->products->find([], [
            'limit' => $limit,
            'sort' => ['name' => 1]
        ]);
        
        return iterator_to_array($cursor);
    } catch (Exception $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk kategori
function getCategories() {
    $db = connectToMongoDB();
    try {
        return $db->products->distinct('category');
    } catch (Exception $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk pesanan
function getOrders($limit = 5) {
    $db = connectToMongoDB();
    try {
        $cursor = $db->orders->find([], [
            'limit' => $limit,
            'sort' => ['order_date' => -1]
        ]);
        
        return iterator_to_array($cursor);
    } catch (Exception $e) {
        error_log("Error fetching orders: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk statistik dasar
function getDashboardStats() {
    $db = connectToMongoDB();
    try {
        $stats = [
            'products' => $db->products->countDocuments(),
            'categories' => count($db->products->distinct('category')),
            'orders' => $db->orders->countDocuments(),
            'revenue' => calculateTotalRevenue()
        ];
        return $stats;
    } catch (Exception $e) {
        error_log("Error getting dashboard stats: " . $e->getMessage());
        return [
            'products' => 0,
            'categories' => 0,
            'orders' => 0,
            'revenue' => 0
        ];
    }
}

// Fungsi untuk menghitung total pendapatan
function calculateTotalRevenue() {
    $db = connectToMongoDB();
    try {
        $result = $db->orders->aggregate([
            [
                '$match' => [
                    'status' => 'completed'
                ]
            ],
            [
                '$group' => [
                    '_id' => null,
                    'total' => ['$sum' => '$total']
                ]
            ]
        ])->toArray();
        
        return isset($result[0]) ? $result[0]['total'] : 0;
    } catch (Exception $e) {
        error_log("Error calculating revenue: " . $e->getMessage());
        return 0;
    }
}

// Fungsi untuk mendapatkan notifikasi admin
function getAdminNotifications($limit = 5) {
    $db = connectToMongoDB();
    try {
        $cursor = $db->notifications->find(
            ['read' => false],
            [
                'limit' => $limit,
                'sort' => ['created_at' => -1]
            ]
        );
        
        return iterator_to_array($cursor);
    } catch (Exception $e) {
        error_log("Error fetching notifications: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk laporan penjualan
function getSalesReport($period = 'daily') {
    $db = connectToMongoDB();
    try {
        $dateFormat = [
            'daily' => '%Y-%m-%d',
            'monthly' => '%Y-%m',
            'yearly' => '%Y'
        ];

        $result = $db->orders->aggregate([
            [
                '$match' => [
                    'status' => 'completed'
                ]
            ],
            [
                '$group' => [
                    '_id' => [
                        'date' => ['$dateToString' => ['format' => $dateFormat[$period], 'date' => '$order_date']]
                    ],
                    'total_sales' => ['$sum' => '$total'],
                    'order_count' => ['$sum' => 1]
                ]
            ],
            [
                '$sort' => ['_id.date' => -1]
            ],
            [
                '$limit' => 7
            ]
        ])->toArray();

        return $result;
    } catch (Exception $e) {
        error_log("Error generating sales report: " . $e->getMessage());
        return [];
    }
}
?>