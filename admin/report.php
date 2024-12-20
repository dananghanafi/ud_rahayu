<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug information
echo "<!-- Debug: Script started -->\n";

session_start();
echo "<!-- Debug: Session started -->\n";

require_once '../config/mongodb.php';
echo "<!-- Debug: MongoDB config loaded -->\n";

require_once '../includes/auth_check.php';
echo "<!-- Debug: Auth check loaded -->\n";

// Debug logging
error_log("Report.php accessed at " . date('Y-m-d H:i:s'));
error_log("Session user_role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'not set'));

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    error_log("Access denied: User is not admin");
    header('Location: ../login.php');
    exit();
}

try {
    echo "<!-- Debug: Starting database operations -->\n";
    // Initialize MongoDB connection
    $db = connectToMongoDB();
    echo "<!-- Debug: MongoDB connected successfully -->\n";

    // Get filter parameters
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';

    error_log("Filter parameters - Start: $startDate, End: $endDate, Status: $status");

    // Build the match condition for MongoDB aggregation
    $matchCondition = [
        'order_date' => [
            '$gte' => new MongoDB\BSON\UTCDateTime(strtotime($startDate) * 1000),
            '$lte' => new MongoDB\BSON\UTCDateTime(strtotime($endDate . ' 23:59:59') * 1000)
        ]
    ];

    if ($status !== 'all') {
        $matchCondition['status'] = $status;
    }

    // Aggregate sales data
    $salesPipeline = [
        [
            '$match' => $matchCondition
        ],
        [
            '$group' => [
                '_id' => null,
                'total_sales' => ['$sum' => ['$toDouble' => '$total_amount']],
                'total_orders' => ['$sum' => 1],
                'orders' => ['$push' => '$$ROOT']
            ]
        ]
    ];

    $salesResult = $db->orders->aggregate($salesPipeline)->toArray();
    $salesData = !empty($salesResult) ? $salesResult[0] : null;

    // Get product-wise sales
    $productPipeline = [
        [
            '$match' => $matchCondition
        ],
        ['$unwind' => '$items'],
        [
            '$group' => [
                '_id' => '$items.product_id',
                'product_name' => ['$first' => '$items.name'],
                'total_quantity' => ['$sum' => '$items.quantity'],
                'total_amount' => [
                    '$sum' => [
                        '$multiply' => ['$items.price', '$items.quantity']
                    ]
                ]
            ]
        ],
        ['$sort' => ['total_amount' => -1]]
    ];

    $productSales = $db->orders->aggregate($productPipeline)->toArray();
} catch (Exception $e) {
    error_log("Error in reports.php: " . $e->getMessage());
    echo "An error occurred. Please check the error log for details.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - UD Rahayu</title>
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
        .report-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .summary-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-form {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
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
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Laporan Penjualan</h2>
                <p class="text-muted">Lihat dan analisis data penjualan toko Anda</p>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-form">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Pesanan</label>
                    <select class="form-select" name="status">
                        <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="processing" <?php echo $status == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Filter</button>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="summary-card">
                    <h6 class="text-muted mb-2">Total Penjualan</h6>
                    <h3 class="mb-0">Rp <?php echo number_format($salesData ? $salesData['total_sales'] : 0, 0, ',', '.'); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <h6 class="text-muted mb-2">Jumlah Pesanan</h6>
                    <h3 class="mb-0"><?php echo $salesData ? $salesData['total_orders'] : 0; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <h6 class="text-muted mb-2">Rata-rata Pesanan</h6>
                    <h3 class="mb-0">Rp <?php 
                        $avgOrder = $salesData && $salesData['total_orders'] > 0 
                            ? $salesData['total_sales'] / $salesData['total_orders'] 
                            : 0;
                        echo number_format($avgOrder, 0, ',', '.');
                    ?></h3>
                </div>
            </div>
        </div>

        <!-- Product Sales Table -->
        <div class="table-container">
            <h5 class="mb-3">Penjualan per Produk</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Jumlah Terjual</th>
                            <th>Total Penjualan</th>
                            <th>Kontribusi (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productSales as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo $product['total_quantity']; ?></td>
                            <td>Rp <?php echo number_format($product['total_amount'], 0, ',', '.'); ?></td>
                            <td><?php 
                                $contribution = $salesData && $salesData['total_sales'] > 0 
                                    ? ($product['total_amount'] / $salesData['total_sales']) * 100 
                                    : 0;
                                echo number_format($contribution, 1);
                            ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detailed Orders -->
        <div class="table-container">
            <h5 class="mb-3">Detail Pesanan</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($salesData && !empty($salesData['orders'])): ?>
                            <?php foreach ($salesData['orders'] as $order): ?>
                            <tr>
                                <td><?php echo substr((string)$order['_id'], -6); ?></td>
                                <td><?php echo date('d/m/Y H:i', $order['order_date']->toDateTime()->getTimestamp()); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
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
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data pesanan untuk periode ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 