<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login
checkLogin();

try {
    $db = connectDB();
    $products = $db->products->find([], ['sort' => ['name' => 1]]);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - UD Rahayu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f4f6f9;
        }

        .navbar {
            background: #4a2c2a;
            padding: 0 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            height: 65px;
        }

        .navbar h1 {
            font-size: 1.25rem;
            margin: 0;
            padding: 0;
            font-weight: 600;
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 0.95rem;
        }

        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1.25rem;
            border-radius: 4px;
            font-size: 0.95rem;
            transition: background 0.2s;
            height: 36px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
        }

        @media screen and (max-width: 768px) {
            .navbar {
                padding: 0 1.5rem;
                height: 60px;
            }
            
            .navbar h1 {
                font-size: 1.25rem;
            }

            .navbar .user-info {
                gap: 1rem;
                font-size: 0.9rem;
            }

            .logout-btn {
                padding: 0.5rem 1rem;
                height: 34px;
                font-size: 0.9rem;
            }
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

        .cart-btn {
            background: #4a2c2a;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .cart-btn:hover {
            background: #6d4c4a;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 1rem;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #4a2c2a;
        }

        .product-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1rem;
            height: 40px;
            overflow: hidden;
        }

        .product-price {
            font-weight: 600;
            color: #4a2c2a;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .add-to-cart {
            width: 100%;
            padding: 0.5rem;
            background: #4a2c2a;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background 0.2s;
            font-size: 0.95rem;
        }

        .add-to-cart:hover {
            background: #6d4c4a;
        }

        .stock-info {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .stock-low {
            color: #cc0033;
        }

        .stock-out {
            color: #cc0033;
            font-weight: 600;
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
            <h2>Katalog Produk</h2>
            <a href="cart.php" class="cart-btn">
                <i class="fas fa-shopping-cart"></i>
                Lihat Keranjang
            </a>
        </div>

        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo BASE_URL . (!empty($product->image_url) ? $product->image_url : '/assets/images/product-placeholder.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($product->name); ?>" 
                     class="product-image">
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($product->name); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars($product->description); ?></p>
                    <p class="product-price">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></p>
                    
                    <?php if ($product->stock <= 0): ?>
                        <p class="stock-info stock-out">Stok Habis</p>
                        <button class="add-to-cart" disabled>
                            <i class="fas fa-shopping-cart"></i>
                            Stok Habis
                        </button>
                    <?php else: ?>
                        <p class="stock-info <?php echo $product->stock < 5 ? 'stock-low' : ''; ?>">
                            Stok: <?php echo $product->stock; ?>
                        </p>
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product->_id; ?>">
                            <button type="submit" class="add-to-cart">
                                <i class="fas fa-shopping-cart"></i>
                                Tambah ke Keranjang
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 