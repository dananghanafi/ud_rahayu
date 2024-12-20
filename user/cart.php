<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login
checkLogin();

try {
    $db = connectDB();
    
    // Ambil data keranjang dari session jika ada
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
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - UD Rahayu</title>
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

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logout-btn {
            background: #6d4c4a;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #5a3f3d;
        }

        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .back-btn {
            background: #4a2c2a;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            background: #6d4c4a;
        }

        .cart-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .product-info h3 {
            color: #4a2c2a;
            margin-bottom: 0.5rem;
        }

        .product-price {
            color: #666;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            background: #4a2c2a;
            color: white;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover {
            background: #6d4c4a;
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.25rem;
        }

        .remove-btn {
            color: #cc0033;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        .remove-btn:hover {
            color: #990033;
        }

        .cart-summary {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem;
            margin-top: 1rem;
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
        }

        .checkout-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: #4a2c2a;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1rem;
            margin-top: 1rem;
            text-align: center;
            text-decoration: none;
        }

        .checkout-btn:hover {
            background: #6d4c4a;
        }

        .empty-cart {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>UD RAHAYU</h1>
        <div class="user-info">
            <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="index.php" class="logout-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>Keranjang Belanja</h2>
        </div>

        <?php if (empty($cart_items)): ?>
        <div class="cart-container">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Keranjang Belanja Kosong</h3>
                <p>Anda belum menambahkan produk ke keranjang.</p>
                <a href="products.php" class="back-btn" style="display: inline-block; margin-top: 1rem;">
                    <i class="fas fa-store"></i>
                    Lihat Katalog Produk
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="cart-container">
            <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <img src="<?php echo !empty($item['product']->image_url) ? '..' . htmlspecialchars($item['product']->image_url) : '../assets/images/product-placeholder.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($item['product']->name); ?>" 
                     class="product-image">
                
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($item['product']->name); ?></h3>
                    <p class="product-price">Rp <?php echo number_format($item['product']->price, 0, ',', '.'); ?></p>
                </div>

                <div class="quantity-control">
                    <form action="update_cart.php" method="POST" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="hidden" name="product_id" value="<?php echo $item['product']->_id; ?>">
                        <button type="submit" name="action" value="decrease" class="quantity-btn">-</button>
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['product']->stock; ?>" class="quantity-input" readonly>
                        <button type="submit" name="action" value="increase" class="quantity-btn">+</button>
                    </form>
                </div>

                <div style="text-align: right;">
                    <p style="font-weight: bold; color: #4a2c2a;">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></p>
                    <form action="remove_from_cart.php" method="POST" style="margin-top: 0.5rem;">
                        <input type="hidden" name="product_id" value="<?php echo $item['product']->_id; ?>">
                        <button type="submit" class="remove-btn">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
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
            <a href="checkout.php" class="checkout-btn">
                <i class="fas fa-shopping-bag"></i>
                Lanjut ke Pembayaran
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 