<?php
require_once '../config/database.php';
require_once '../includes/header.php';

$db = getMongoDBConnection();

// Mengambil ID order dari parameter URL
$order_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$order_id) {
    header('Location: products.php');
    exit;
}

// Mengambil detail order
$order = $db->orders->findOne(['_id' => new MongoDB\BSON\ObjectId($order_id)]);

if (!$order) {
    header('Location: products.php');
    exit;
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle text-success fa-5x mb-4"></i>
                    <h2 class="card-title mb-4">Pesanan Berhasil!</h2>
                    <p class="card-text">Terima kasih telah melakukan pemesanan di UD RAHAYU.</p>
                    
                    <div class="order-details mt-4">
                        <h5>Detail Pesanan:</h5>
                        <table class="table">
                            <tr>
                                <td>Nomor Pesanan:</td>
                                <td>#<?php echo substr($order_id, -6); ?></td>
                            </tr>
                            <tr>
                                <td>Produk:</td>
                                <td><?php echo $order->product_name; ?></td>
                            </tr>
                            <tr>
                                <td>Jumlah:</td>
                                <td><?php echo $order->quantity; ?></td>
                            </tr>
                            <tr>
                                <td>Total Pembayaran:</td>
                                <td>Rp <?php echo number_format($order->total_price, 0, ',', '.'); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="mt-4">
                        <p>Kami akan segera memproses pesanan Anda.</p>
                        <p>Silakan cek email Anda untuk informasi lebih lanjut.</p>
                    </div>

                    <div class="mt-4">
                        <a href="products.php" class="btn btn-primary me-2">Kembali ke Menu</a>
                        <a href="/" class="btn btn-outline-primary">Halaman Utama</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 