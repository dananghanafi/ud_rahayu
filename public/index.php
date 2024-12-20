<?php
require_once '../config/database.php';
require_once '../includes/header.php';

// Mengambil produk unggulan
$db = getMongoDBConnection();
$products = $db->products->find([], ['limit' => 6]);
?>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 mb-4">Selamat Datang di UD RAHAYU</h1>
        <p class="lead mb-4">Nikmati Pengalaman Kopi Premium Terbaik</p>
        <a href="products.php" class="btn btn-primary btn-lg">Lihat Menu Kami</a>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Produk Unggulan Kami</h2>
        <div class="row">
            <?php foreach ($products as $product): ?>
            <div class="col-md-4 product-card">
                <div class="card h-100 fade-in">
                    <img src="<?php echo $product->image_url ?? '/assets/images/default-product.jpg'; ?>" 
                         class="card-img-top" alt="<?php echo $product->name; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $product->name; ?></h5>
                        <p class="card-text"><?php echo $product->description; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="product-price">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></span>
                            <a href="order.php?id=<?php echo $product->_id; ?>" class="btn btn-primary">Pesan</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <i class="fas fa-coffee fa-3x mb-3 text-primary"></i>
                <h4>Kopi Premium</h4>
                <p>Biji kopi pilihan dengan kualitas terbaik</p>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fas fa-truck fa-3x mb-3 text-primary"></i>
                <h4>Pengiriman Cepat</h4>
                <p>Layanan pengiriman yang cepat dan aman</p>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fas fa-heart fa-3x mb-3 text-primary"></i>
                <h4>Pelayanan Terbaik</h4>
                <p>Kepuasan pelanggan adalah prioritas kami</p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?> 