<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login
checkLogin();

try {
    $db = connectDB();
    
    // Ambil data keranjang dari session
    $cart = $_SESSION['cart'] ?? [];
    $cart_items = [];
    $total = 0;
    
    // Jika ada item di keranjang, ambil detail produknya
    if (!empty($cart)) {
        foreach ($cart as $product_id => $quantity) {
            $product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
            if ($product) {
                $subtotal = $product->price * $quantity;
                $cart_items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
                $total += $subtotal;
            }
        }
    } else {
        // Redirect ke keranjang jika kosong
        header("Location: " . BASE_URL . "/user/cart.php");
        exit();
    }

    // Proses checkout jika form disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi input
        $required_fields = ['street', 'city', 'state', 'postal_code', 'phone'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Field " . ucfirst($field) . " harus diisi";
            }
        }

        if (empty($errors)) {
            try {
                // Debug: Log cart items
                error_log("Cart items: " . print_r($cart_items, true));

                // Siapkan data order
                $order_items = [];
                foreach ($cart_items as $item) {
                    $order_items[] = [
                        'product_id' => new MongoDB\BSON\ObjectId((string)$item['product']->_id),
                        'quantity' => (int)$item['quantity'],
                        'price' => (float)$item['product']->price,
                        'subtotal' => (float)$item['subtotal']
                    ];

                    // Debug: Log stock update
                    error_log("Updating stock for product: " . $item['product']->_id . ", quantity: -" . $item['quantity']);

                    // Update stok produk
                    $result = $db->products->updateOne(
                        ['_id' => $item['product']->_id],
                        ['$inc' => ['stock' => -$item['quantity']]]
                    );
                    
                    // Debug: Log stock update result
                    error_log("Stock update result: " . print_r($result->getModifiedCount(), true));
                }

                // Debug: Log order data
                $order_data = [
                    'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
                    'order_date' => new MongoDB\BSON\UTCDateTime(),
                    'status' => 'pending',
                    'total_amount' => (float)$total,
                    'items' => $order_items,
                    'shipping_address' => [
                        'street' => (string)$_POST['street'],
                        'city' => (string)$_POST['city'],
                        'state' => (string)$_POST['state'],
                        'postal_code' => (string)$_POST['postal_code']
                    ],
                    'phone' => (string)$_POST['phone'],
                    'payment_status' => 'unpaid',
                    'created_at' => new MongoDB\BSON\UTCDateTime(),
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ];
                error_log("Order data: " . print_r($order_data, true));

                // Simpan order ke database
                $result = $db->orders->insertOne($order_data);

                // Debug: Log insert result
                error_log("Order insert result: " . print_r($result->getInsertedId(), true));

                if ($result->getInsertedId()) {
                    // Debug: Log success
                    error_log("Order created successfully with ID: " . $result->getInsertedId());

                    // Kosongkan keranjang
                    unset($_SESSION['cart']);
                    
                    // Redirect ke halaman sukses
                    header("Location: " . BASE_URL . "/user/order_success.php?order_id=" . $result->getInsertedId());
                    exit();
                } else {
                    // Debug: Log failure
                    error_log("Failed to create order");
                    $errors[] = "Gagal membuat pesanan";
                }
            } catch (Exception $e) {
                // Debug: Log any exceptions
                error_log("Exception during order creation: " . $e->getMessage());
                error_log("Exception trace: " . $e->getTraceAsString());
                $errors[] = "Error: " . $e->getMessage();
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - UD Rahayu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
        }

        .navbar {
            background: #4a2c2a;
            padding: 1rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-size: 1.5rem;
        }

        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .checkout-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .order-summary {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a2c2a;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: #4a2c2a;
        }

        .error-message {
            color: #cc0033;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: #4a2c2a;
            margin-bottom: 0.25rem;
        }

        .item-price {
            color: #666;
            font-size: 0.9rem;
        }

        .item-quantity {
            color: #666;
            font-size: 0.9rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            color: #4a2c2a;
            font-size: 1.2rem;
            margin-top: 1rem;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: #4a2c2a;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background: #6d4c4a;
        }

        .back-link {
            color: #4a2c2a;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>UD RAHAYU</h1>
    </nav>

    <div class="container">
        <div class="checkout-form">
            <a href="cart.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Keranjang
            </a>

            <h2 style="margin-bottom: 1.5rem;">Informasi Pengiriman</h2>

            <?php if (!empty($errors)): ?>
                <div style="background: #fff0f0; border-left: 4px solid #cc0033; padding: 1rem; margin-bottom: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <p style="color: #cc0033; margin: 0.25rem 0;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="street">Alamat</label>
                    <input type="text" id="street" name="street" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="city">Kota</label>
                    <input type="text" id="city" name="city" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="state">Provinsi</label>
                    <input type="text" id="state" name="state" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="postal_code">Kode Pos</label>
                    <input type="text" id="postal_code" name="postal_code" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="phone">Nomor Telepon</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i>
                    Buat Pesanan
                </button>
            </form>
        </div>

        <div class="order-summary">
            <h3 style="margin-bottom: 1.5rem;">Ringkasan Pesanan</h3>

            <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <img src="<?php echo BASE_URL . (!empty($item['product']->image_url) ? $item['product']->image_url : '/assets/images/product-placeholder.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($item['product']->name); ?>" 
                     class="item-image">
                <div class="item-info">
                    <div class="item-name"><?php echo htmlspecialchars($item['product']->name); ?></div>
                    <div class="item-price">Rp <?php echo number_format($item['product']->price, 0, ',', '.'); ?></div>
                    <div class="item-quantity">Jumlah: <?php echo $item['quantity']; ?></div>
                </div>
                <div style="text-align: right;">
                    <strong>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></strong>
                </div>
            </div>
            <?php endforeach; ?>

            <div style="margin-top: 1rem;">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row">
                    <span>Ongkos Kirim</span>
                    <span>Rp 0</span>
                </div>
                <div class="summary-row">
                    <span>Total</span>
                    <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 