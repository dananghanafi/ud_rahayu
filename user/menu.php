<?php
session_start();
require_once '../config/mongodb.php';

// Initialize MongoDB connection
$db = connectToMongoDB();

// Get all active products
$products = $db->products->find(['status' => 'active'])->toArray();

// Group products by category
$categorized_products = [];
foreach ($products as $product) {
    $category = $product['category'] ?? 'Lainnya';
    if (!isset($categorized_products[$category])) {
        $categorized_products[$category] = [];
    }
    $categorized_products[$category][] = $product;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - UD Rahayu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a2c2a;
            --secondary-color: #6d4c4a;
            --accent-color: #8b6b69;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand, .nav-link {
            color: white !important;
        }

        .category-title {
            color: var(--primary-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .product-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
            background: white;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .product-image {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .product-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .product-price {
            color: #28a745;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .product-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .btn-add-cart {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            transition: background-color 0.3s;
        }

        .btn-add-cart:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .product-card {
                margin-bottom: 20px;
            }
            .product-image {
                height: 150px;
            }
            .product-title {
                font-size: 1rem;
            }
            .product-price {
                font-size: 1.1rem;
            }
            .btn-add-cart {
                padding: 6px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">UD Rahayu</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="menu.php">
                            <i class="fas fa-coffee me-2"></i>Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart me-2"></i>Keranjang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i>Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <?php foreach ($categorized_products as $category => $category_products): ?>
        <div class="mb-5">
            <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
            <div class="row g-4">
                <?php foreach ($category_products as $product): ?>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="product-card">
                        <?php 
                        $image_url = $product['image_url'] ?? '/ud_rahayu/assets/images/default-product.jpg';
                        $image_path = $_SERVER['DOCUMENT_ROOT'] . $image_url;
                        
                        if (file_exists($image_path)) {
                            echo '<img src="' . htmlspecialchars($image_url) . '" class="product-image w-100" alt="' . htmlspecialchars($product['name']) . '">';
                        } else {
                            echo '<img src="/ud_rahayu/assets/images/default-product.jpg" class="product-image w-100" alt="Default Image">';
                        }
                        ?>
                        <span class="stock-badge <?php echo $product['stock'] < 10 ? 'text-danger' : 'text-success'; ?>">
                            Stok: <?php echo $product['stock']; ?>
                        </span>
                        <div class="card-body">
                            <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="product-description">
                                <?php echo htmlspecialchars($product['description'] ?? 'Tidak ada deskripsi'); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                <button class="btn btn-add-cart" onclick="addToCart('<?php echo $product['_id']; ?>')" 
                                        <?php echo $product['stock'] < 1 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-cart-plus me-2"></i>Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCart(productId) {
            // Show loading spinner
            document.querySelector('.loading-spinner').style.display = 'block';

            // Prepare the data
            const data = {
                product_id: productId,
                quantity: 1
            };

            // Send request to add to cart
            fetch('../api/cart/add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading spinner
                document.querySelector('.loading-spinner').style.display = 'none';

                // Show alert based on response
                if (data.success) {
                    alert('Produk berhasil ditambahkan ke keranjang');
                } else {
                    alert(data.message || 'Gagal menambahkan produk ke keranjang');
                }
            })
            .catch(error => {
                // Hide loading spinner
                document.querySelector('.loading-spinner').style.display = 'none';
                alert('Terjadi kesalahan saat menambahkan produk ke keranjang');
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>