<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';

// Cek apakah ada order_id di parameter
if (!isset($_GET['order_id'])) {
    header('Location: menu.php');
    exit();
}

// Inisialisasi koneksi MongoDB
$db = connectToMongoDB();

// Ambil data pesanan
$order_id = new MongoDB\BSON\ObjectId($_GET['order_id']);
$order = $db->orders->findOne(['_id' => $order_id]);

if (!$order) {
    header('Location: menu.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan - UD Rahayu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .success-icon {
            font-size: 4rem;
            color: #198754;
        }
        .order-number {
            font-size: 1.2rem;
            font-family: monospace;
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Pesan Sukses -->
                <div class="card mb-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle success-icon mb-3"></i>
                        <h2 class="mb-4">Pesanan Berhasil Dibuat!</h2>
                        <p class="mb-3">Nomor Pesanan Anda:</p>
                        <div class="order-number mb-4"><?php echo $order->order_number; ?></div>
                        <p class="text-muted mb-0">Mohon simpan nomor pesanan ini untuk keperluan pembayaran</p>
                    </div>
                </div>

                <!-- Detail Pesanan -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Detail Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <!-- Informasi Pelanggan -->
                        <div class="mb-4">
                            <h6 class="mb-3">Informasi Pelanggan:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="150">Nama</th>
                                        <td><?php echo htmlspecialchars($order->customer_info->name); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td><?php echo htmlspecialchars($order->customer_info->username); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?php echo htmlspecialchars($order->customer_info->email); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Telepon</th>
                                        <td><?php echo htmlspecialchars($order->customer_info->phone); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Item Pesanan -->
                        <h6 class="mb-3">Item Pesanan:</h6>
                        <div class="table-responsive mb-3">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order->items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item->product_name); ?></td>
                                        <td class="text-center"><?php echo $item->quantity; ?>x</td>
                                        <td class="text-end">Rp<?php echo number_format($item->price, 0, ',', '.'); ?></td>
                                        <td class="text-end">Rp<?php echo number_format($item->subtotal, 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end">Subtotal:</td>
                                        <td class="text-end">Rp<?php echo number_format($order->payment_info->subtotal, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end">PPN (11%):</td>
                                        <td class="text-end">Rp<?php echo number_format($order->payment_info->tax, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold">Rp<?php echo number_format($order->payment_info->total, 0, ',', '.'); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Informasi Pembayaran -->
                        <div class="alert <?php echo $order->payment_info->method === 'transfer' ? 'alert-info' : 'alert-warning'; ?> mb-0">
                            <h6 class="alert-heading">
                                <i class="fas <?php echo $order->payment_info->method === 'transfer' ? 'fa-university' : 'fa-money-bill-wave'; ?> me-2"></i>
                                Metode Pembayaran: <?php echo $order->payment_info->method === 'transfer' ? 'Transfer Bank' : 'Bayar di Kasir'; ?>
                            </h6>
                            <hr>
                            <?php if ($order->payment_info->method === 'transfer'): ?>
                                <p class="mb-2">Silakan transfer ke rekening berikut:</p>
                                <ul class="mb-2">
                                    <li>Bank BCA</li>
                                    <li>No. Rekening: 1234567890</li>
                                    <li>Atas Nama: UD Rahayu</li>
                                    <li>Nominal: Rp<?php echo number_format($order->payment_info->total, 0, ',', '.'); ?></li>
                                </ul>
                                <p class="mb-0">
                                    <small>* Kirim bukti transfer ke WhatsApp admin: 081234567890</small>
                                </p>
                            <?php else: ?>
                                <p class="mb-0">Silakan tunjukkan nomor pesanan ini kepada kasir untuk melakukan pembayaran.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tombol Navigasi -->
                <div class="text-center">
                    <a href="menu.php" class="btn btn-primary">
                        <i class="fas fa-utensils me-2"></i>Kembali ke Menu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 