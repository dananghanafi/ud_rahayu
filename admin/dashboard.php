<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Initialize MongoDB connection
$db = connectToMongoDB();

// Get total customers (excluding admin users)
$totalCustomers = $db->users->countDocuments(['role' => 'customer']);

// Get total products
$totalProducts = $db->products->countDocuments();

// Get total orders
$totalOrders = $db->orders->countDocuments();

// Calculate total revenue from completed orders
$revenuePipeline = [
    [
        '$match' => [
            'status' => 'completed'
        ]
    ],
    [
        '$group' => [
            '_id' => null,
            'total' => ['$sum' => ['$toDouble' => '$total_amount']]
        ]
    ]
];
$revenueResult = $db->orders->aggregate($revenuePipeline)->toArray();
$totalRevenue = isset($revenueResult[0]['total']) ? $revenueResult[0]['total'] : 0;

// Get pending orders count
$pendingOrders = $db->orders->countDocuments(['status' => 'pending']);

// Get recent orders (limit to 5)
$recentOrders = $db->orders->find(
    [],
    [
        'sort' => ['order_date' => -1],
        'limit' => 5
    ]
)->toArray();

// Get popular products with actual sales data
$popularProductsPipeline = [
    [
        '$match' => [
            'status' => 'completed'
        ]
    ],
    ['$unwind' => '$items'],
    [
        '$group' => [
            '_id' => '$items.product_id',
            'name' => ['$first' => '$items.name'],
            'total_sold' => ['$sum' => '$items.quantity']
        ]
    ],
    [
        '$lookup' => [
            'from' => 'products',
            'localField' => '_id',
            'foreignField' => '_id',
            'as' => 'product_info'
        ]
    ],
    ['$unwind' => '$product_info'],
    [
        '$project' => [
            'name' => 1,
            'total_sold' => 1,
            'stock' => '$product_info.stock',
            'price' => '$product_info.price',
            'category' => '$product_info.category'
        ]
    ],
    ['$sort' => ['total_sold' => -1]],
    ['$limit' => 5]
];

$popularProducts = $db->orders->aggregate($popularProductsPipeline)->toArray();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UD Rahayu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: #4a2c2a;
            padding: 15px 0;
            position: relative;
        }
        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .navbar-brand {
            color: white;
            font-size: 18px;
            margin: 0;
            padding-left: 20px;
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
            padding-right: 20px;
        }
        .navbar-text {
            color: white;
            font-size: 14px;
        }
        .logout-btn {
            background: transparent;
            border: 1px solid white;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 14px;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
        }
        .content-wrapper {
            padding: 20px;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            color: #4a2c2a;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .quick-action {
            text-decoration: none;
            color: #4a2c2a;
            transition: all 0.3s;
            padding: 15px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .quick-action:hover {
            transform: translateY(-3px);
            color: #6d4c4a;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">UD RAHAYU - Admin Dashboard</div>
            <div class="navbar-right">
                <span class="navbar-text">Welcome, admin</span>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <h3>Selamat Datang di Panel Admin</h3>
                    <p class="text-muted">Kelola toko Anda dengan mudah menggunakan panel admin ini.</p>
                </div>
            </div>

            <!-- Navigation Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <a href="dashboard.php" class="card stat-card text-center p-3 text-decoration-none">
                        <i class="fas fa-home fa-2x mb-2 text-primary"></i>
                        <div>Dashboard</div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="products.php" class="card stat-card text-center p-3 text-decoration-none">
                        <i class="fas fa-coffee fa-2x mb-2 text-primary"></i>
                        <div>Produk</div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="orders.php" class="card stat-card text-center p-3 text-decoration-none">
                        <i class="fas fa-shopping-cart fa-2x mb-2 text-primary"></i>
                        <div>Pesanan</div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="users.php" class="card stat-card text-center p-3 text-decoration-none">
                        <i class="fas fa-users fa-2x mb-2 text-primary"></i>
                        <div>Pengguna</div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="reports.php" class="card stat-card text-center p-3 text-decoration-none">
                        <i class="fas fa-chart-bar fa-2x mb-2 text-primary"></i>
                        <div>Laporan</div>
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total Pelanggan</h6>
                                    <h3 class="card-title mb-0"><?php echo $totalCustomers; ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total Produk</h6>
                                    <h3 class="card-title mb-0"><?php echo $totalProducts; ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-coffee"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total Pesanan</h6>
                                    <h3 class="card-title mb-0"><?php echo $totalOrders; ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total Pendapatan</h6>
                                    <h3 class="card-title mb-0">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Orders -->
                <div class="col-md-8">
                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Pesanan Terbaru</h5>
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Pelanggan</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><?php echo substr((string)$order['_id'], -6); ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            $statusText = '';
                                            switch ($order['status']) {
                                                case 'pending':
                                                    $statusClass = 'bg-warning';
                                                    $statusText = 'Menunggu';
                                                    break;
                                                case 'processing':
                                                    $statusClass = 'bg-info';
                                                    $statusText = 'Diproses';
                                                    break;
                                                case 'completed':
                                                    $statusClass = 'bg-success';
                                                    $statusText = 'Selesai';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-secondary';
                                                    $statusText = $order['status'];
                                            }
                                            ?>
                                            <span class="status-badge text-white <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Popular Products -->
                <div class="col-md-4">
                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Produk Terpopuler</h5>
                            <a href="products.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Terjual</th>
                                        <th>Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($popularProducts as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                                        <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                        <td><?php echo $product['total_sold']; ?></td>
                                        <td><?php echo $product['stock']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 