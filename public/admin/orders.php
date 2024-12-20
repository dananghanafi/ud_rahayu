<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$db = getMongoDBConnection();

// Update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $id = $_POST['id'];
        $status = $_POST['status'];
        
        $db->orders->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            ['$set' => ['status' => $status]]
        );
    }
}

// Mengambil semua pesanan
$orders = $db->orders->find([], ['sort' => ['created_at' => -1]]);
?>

<div class="container py-5">
    <h2 class="mb-4">Kelola Pesanan</h2>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo substr($order->_id, -6); ?></td>
                            <td><?php echo $order->created_at->toDateTime()->format('d/m/Y H:i'); ?></td>
                            <td>
                                <?php echo $order->customer_name; ?><br>
                                <small class="text-muted"><?php echo $order->customer_phone; ?></small>
                            </td>
                            <td><?php echo $order->product_name; ?></td>
                            <td><?php echo $order->quantity; ?></td>
                            <td>Rp <?php echo number_format($order->total_price, 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $order->status === 'completed' ? 'success' : 
                                        ($order->status === 'processing' ? 'warning' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst($order->status); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?php echo $order->_id; ?>">
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $order->status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order->status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo $order->status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order->status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </form>
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