<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';

// Initialize MongoDB connection
$db = connectToMongoDB();
$user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

// Get cart count
$cart_count = $db->cart->countDocuments(['user_id' => $user_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UD Rahayu - Beranda</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/user-dashboard.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: #4a2c2a;
            padding: 0 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 65px;
            margin-bottom: 2rem;
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

        .welcome-box {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
        }

        .welcome-box h2 {
            font-size: 1.5rem;
            color: #4a2c2a;
            margin-bottom: 0.5rem;
        }

        .welcome-box p {
            font-size: 0.95rem;
            color: #666;
        }

        .menu-item h3 {
            font-size: 1.1rem;
            margin: 0.5rem 0;
        }

        .menu-item p {
            font-size: 0.95rem;
            color: #666;
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

            .welcome-box h2 {
                font-size: 1.35rem;
            }

            .welcome-box p {
                font-size: 0.9rem;
            }

            .menu-item h3 {
                font-size: 1rem;
            }

            .menu-item p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>UD RAHAYU</h1>
        <div class="user-info">
            <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-box">
            <h2>Selamat Datang di UD Rahayu</h2>
            <p>Temukan berbagai produk berkualitas dengan harga terbaik.</p>
        </div>

        <div class="menu-grid">
            <a href="products.php" class="menu-item">
                <i class="fas fa-box"></i>
                <h3>Katalog Produk</h3>
                <p>Lihat semua produk</p>
            </a>

            <a href="cart.php" class="menu-item">
                <i class="fas fa-shopping-cart"></i>
                <h3>Keranjang</h3>
                <p>Lihat keranjang belanja</p>
            </a>

            <a href="check_orders.php" class="menu-item">
                <i class="fas fa-clipboard-list"></i>
                <h3>Pesanan Saya</h3>
                <p>Lacak pesanan Anda</p>
            </a>

            <a href="profile.php" class="menu-item">
                <i class="fas fa-users"></i>
                <h3>Profil</h3>
                <p>Pengaturan akun</p>
            </a>
        </div>
    </div>
</body>
</html> 