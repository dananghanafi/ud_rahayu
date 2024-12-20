<?php
require_once '../config/database.php';
require_once '../includes/header.php';

$db = getMongoDBConnection();

// Mengambil ID produk dari parameter URL
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Mengambil detail produk
$product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Proses pemesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)$_POST['quantity'];
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $customer_phone = $_POST['customer_phone'];
    $notes = $_POST['notes'];

    // Membuat order baru
    $order = [
        'product_id' => new MongoDB\BSON\ObjectId($product_id),
        'product_name' => $product->name,
        'quantity' => $quantity,
        'price' => $product->price,
        'total_price' => $product->price * $quantity,
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'notes' => $notes,
        'status' => 'pending',
        'created_at' => new MongoDB\BSON\UTCDateTime(),
    ];

    $result = $db->orders->insertOne($order);

    if ($result->getInsertedCount()) {
        // Membuat notifikasi
        $notification = [
            'type' => 'new_order',
            'message' => "Pesanan baru dari $customer_name",
            'order_id' => $result->getInsertedId(),
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'is_read' => false
        ];
        $db->notifications->insertOne($notification);

        header('Location: order-success.php?id=' . $result->getInsertedId());
        exit;
    }
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Product Details -->
        <div class="col-md-6">
            <div class="card">
                <img src="<?php echo $product->image_url ?? '/assets/images/default-product.jpg'; ?>" 
                     class="card-img-top" alt="<?php echo $product->name; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $product->name; ?></h5>
                    <p class="card-text"><?php echo $product->description; ?></p>
                    <p class="product-price">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>

        <!-- Order Form -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Form Pemesanan</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Jumlah</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                   min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="customer_name" 
                                   name="customer_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="customer_email" 
                                   name="customer_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="customer_phone" 
                                   name="customer_phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Pesan Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Price Calculator Script -->
<script>
document.getElementById('quantity').addEventListener('change', function() {
    const quantity = this.value;
    const price = <?php echo $product->price; ?>;
    const totalPrice = quantity * price;
    document.querySelector('.product-price').textContent = 
        'Rp ' + totalPrice.toLocaleString('id-ID');
});
</script>

<?php require_once '../includes/footer.php'; ?> 