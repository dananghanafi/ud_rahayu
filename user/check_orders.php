<?php
session_start();
require_once '../config/mongodb.php';
require_once '../config/config.php';
require_once '../includes/auth_check.php';

// Debug connection
try {
    // Initialize MongoDB connection
    $db = connectDB();
    error_log("MongoDB connection successful");
    
    // Debug session
    error_log("User ID from session: " . $_SESSION['user_id']);
    
    $user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    error_log("User ID as ObjectId: " . $user_id);
    
    // Debug: Check if orders collection exists
    $collections = $db->listCollections();
    $hasOrders = false;
    foreach ($collections as $collection) {
        if ($collection->getName() === 'orders') {
            $hasOrders = true;
            break;
        }
    }
    error_log("Orders collection exists: " . ($hasOrders ? "yes" : "no"));
    
    // Get user's orders with debug
    $query = ['user_id' => $user_id];
    error_log("Query: " . json_encode($query));
    
    // Count total orders for this user
    $totalOrders = $db->orders->countDocuments($query);
    error_log("Total orders found for user: " . $totalOrders);
    
    // Get user's orders
    $orders = $db->orders->find(
        $query,
        [
            'sort' => ['order_date' => -1],
            'limit' => 50
        ]
    )->toArray();

    // Debug: Print orders
    error_log("Found orders: " . print_r($orders, true));
    
    if (empty($orders)) {
        error_log("No orders found for user ID: " . $user_id);
    } else {
        error_log("Found " . count($orders) . " orders for user");
    }

} catch (Exception $e) {
    error_log("MongoDB Error in check_orders.php: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
    $error_message = "Terjadi kesalahan saat mengambil data pesanan";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - UD Rahayu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
        }

        .navbar {
            background: #4a2c2a;
            padding: 1rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .navbar h1 {
            font-size: 1.5rem;
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header h2 {
            color: #4a2c2a;
            font-size: 1.75rem;
        }

        .orders-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            margin-bottom: 2rem;
        }

        .orders-table th,
        .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .orders-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #4a2c2a;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .empty-state {
            background: white;
            border-radius: 8px;
            padding: 3rem 1rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #4a2c2a;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 1rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            display: inline-block;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        @media screen and (max-width: 768px) {
            .navbar {
                padding: 0.75rem;
            }
            
            .navbar h1 {
                font-size: 1.25rem;
            }
            
            .header h2 {
                font-size: 1.5rem;
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>UD RAHAYU</h1>
        <div class="user-info">
            <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="index.php" class="logout-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>Pesanan Saya</h2>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-box"></i>
                <h3>Belum ada pesanan</h3>
                <p>Anda belum memiliki pesanan saat ini</p>
                <a href="products.php" class="logout-btn">
                    <i class="fas fa-shopping-cart"></i> Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <div class="orders-table">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo substr((string)$order['_id'], -6); ?></td>
                            <td><?php 
                                if ($order['order_date'] instanceof MongoDB\BSON\UTCDateTime) {
                                    echo $order['order_date']->toDateTime()->format('d/m/Y H:i');
                                } else {
                                    echo 'Invalid date';
                                }
                            ?></td>
                            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = 'Unknown';
                                switch ($order['status']) {
                                    case 'pending':
                                        $statusClass = 'status-pending';
                                        $statusText = 'Menunggu';
                                        break;
                                    case 'processing':
                                        $statusClass = 'status-processing';
                                        $statusText = 'Diproses';
                                        break;
                                    case 'completed':
                                        $statusClass = 'status-completed';
                                        $statusText = 'Selesai';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'status-cancelled';
                                        $statusText = 'Dibatalkan';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 