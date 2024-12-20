<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$db = getMongoDBConnection();

// Mengambil statistik
$totalProducts = $db->products->countDocuments();
$totalOrders = $db->orders->countDocuments();
$totalPendingOrders = $db->orders->countDocuments(['status' => 'pending']);
$totalRevenue = 0;

$orders = $db->orders->find(['status' => 'completed']);
foreach ($orders as $order) {
    $totalRevenue += $order->total_price;
}

// Mengambil pesanan terbaru
$recentOrders = $db->orders->find(
    [], 
    [
        'limit' => 5,
        'sort' => ['created_at' => -1]
    ]
);
?>

<div class="container py-5">
    <h2 class="mb-4">Dashboard Admin</h2>

    <!-- Statistik Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Produk</h5>
                    <h2><?php echo $totalProducts; ?></h2>
                    <a href="products.php" class="text-white">Kelola Produk →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pesanan</h5>
                    <h2><?php echo $totalOrders; ?></h2>
                    <a href="orders.php" class="text-white">Lihat Pesanan →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Pesanan Pending</h5>
                    <h2><?php echo $totalPendingOrders; ?></h2>
                    <a href="orders.php" class="text-dark">Proses Sekarang →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pendapatan</h5>
                    <h2>Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></h2>
                    <span class="text-white">Dari pesanan selesai</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pesanan Terbaru -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Pesanan Terbaru</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo substr($order->_id, -6); ?></td>
                            <td><?php echo $order->created_at->toDateTime()->format('d/m/Y H:i'); ?></td>
                            <td><?php echo $order->customer_name; ?></td>
                            <td>Rp <?php echo number_format($order->total_price, 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $order->status === 'completed' ? 'success' : 
                                        ($order->status === 'processing' ? 'warning' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst($order->status); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 