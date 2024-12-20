<?php
require_once '../config/database.php';
require_once '../includes/header.php';

$db = getMongoDBConnection();

// Filter dan pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Membuat query filter
$filter = [];
if (!empty($search)) {
    $filter['name'] = ['$regex' => $search, '$options' => 'i'];
}
if (!empty($category)) {
    $filter['category'] = $category;
}

// Mengambil kategori untuk filter
$categories = $db->products->distinct('category');

// Mengambil produk dengan filter
$products = $db->products->find($filter);
?>

<!-- Products Header -->
<div class="container py-5">
    <h2 class="text-center mb-4">Menu Kami</h2>
    
    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form action="" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" 
                       placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>
        <div class="col-md-6">
            <form action="" method="GET" class="d-flex">
                <select name="category" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                        <?php echo ucfirst($cat); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-md-4 mb-4 product-card">
            <div class="card h-100 fade-in">
                <img src="<?php echo $product->image_url ?? '/assets/images/default-product.jpg'; ?>" 
                     class="card-img-top" alt="<?php echo $product->name; ?>">
                <div class="card-body">
                    <div class="badge bg-secondary mb-2"><?php echo ucfirst($product->category); ?></div>
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

<?php require_once '../includes/footer.php'; ?> 