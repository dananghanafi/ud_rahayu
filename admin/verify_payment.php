<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../403.php');
    exit();
}

$db = connectToMongoDB();

// Proses verifikasi pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $order_id = new MongoDB\BSON\ObjectId($_POST['order_id']);
        $status = $_POST['status']; // 'verified' atau 'rejected'
        $note = $_POST['note'];

        // Update status pembayaran
        $result = $db->orders->updateOne(
            ['_id' => $order_id],
            [
                '$set' => [
                    'payment_status' => $status,
                    'order_status' => $status === 'verified' ? 'processing' : 'cancelled',
                    'payment_details.verification_status' => $status,
                    'payment_details.verified_by' => $_SESSION['user_id'],
                    'payment_details.verified_at' => new MongoDB\BSON\UTCDateTime(),
                    'payment_details.verification_note' => $note,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );

        if ($result->getModifiedCount() > 0) {
            // Jika verifikasi sukses dan pembayaran diverifikasi, update stok
            if ($status === 'verified') {
                $order = $db->orders->findOne(['_id' => $order_id]);
                foreach ($order->items as $item) {
                    $db->products->updateOne(
                        ['_id' => $item->product_id],
                        ['$inc' => ['stock' => -$item->quantity]]
                    );
                }
            }
            $success = 'Status pembayaran berhasil diperbarui';
        }
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Ambil daftar pesanan yang perlu diverifikasi
$pending_orders = $db->orders->aggregate([
    [
        '$match' => [
            'payment_status' => 'pending'
        ]
    ],
    [
        '$lookup' => [
            'from' => 'users',
            'localField' => 'user_id',
            'foreignField' => '_id',
            'as' => 'user'
        ]
    ],
    [
        '$unwind' => '$user'
    ],
    [
        '$sort' => ['created_at' => -1]
    ]
])->toArray();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            padding-left: 25px;
        }
        .order-card {
            transition: all 0.3s;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-white text-center mb-4">Admin Panel</h4>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="verify_payment.php" class="active"><i class="fas fa-check-circle me-2"></i> Verifikasi Pembayaran</a>
                <a href="products.php"><i class="fas fa-coffee me-2"></i> Produk</a>
                <a href="orders.php"><i class="fas fa-shopping-cart me-2"></i> Pesanan</a>
                <a href="users.php"><i class="fas fa-users me-2"></i> Pengguna</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 bg-light p-4">
                <h2 class="mb-4">Verifikasi Pembayaran</h2>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($pending_orders)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Tidak ada pembayaran yang perlu diverifikasi saat ini.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($pending_orders as $order): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card order-card">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Order #<?php echo substr($order->_id, -8); ?></h5>
                                        <span class="badge bg-warning">Menunggu Verifikasi</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Pelanggan:</strong><br>
                                            <?php echo htmlspecialchars($order->user->name); ?><br>
                                            <?php echo htmlspecialchars($order->user->phone); ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Detail Pesanan:</strong>
                                            <ul class="list-unstyled">
                                                <?php foreach ($order->items as $item): ?>
                                                    <li><?php echo $item->quantity; ?>x <?php echo $item->product_name; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Total Pembayaran:</strong><br>
                                            Rp<?php echo number_format($order->total, 0, ',', '.'); ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Metode Pembayaran:</strong><br>
                                            <?php 
                                            $method = $order->payment_method;
                                            if ($method === 'transfer') echo 'Transfer Bank';
                                            elseif ($method === 'qris') echo 'QRIS';
                                            elseif ($method === 'ewallet') {
                                                echo 'E-Wallet (' . strtoupper($order->payment_details->ewallet_type) . ')';
                                            }
                                            elseif ($method === 'cash') echo 'Bayar di Kasir';
                                            ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Waktu Pesanan:</strong><br>
                                            <?php echo $order->created_at->toDateTime()->format('d M Y H:i'); ?>
                                        </div>

                                        <!-- Form Verifikasi -->
                                        <form method="POST" action="" class="mt-4">
                                            <input type="hidden" name="order_id" value="<?php echo $order->_id; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Catatan Verifikasi:</label>
                                                <textarea name="note" class="form-control" rows="2" required></textarea>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" name="status" value="verified" class="btn btn-success flex-grow-1">
                                                    <i class="fas fa-check me-2"></i>Verifikasi
                                                </button>
                                                <button type="submit" name="status" value="rejected" class="btn btn-danger flex-grow-1">
                                                    <i class="fas fa-times me-2"></i>Tolak
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 