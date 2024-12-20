<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/mongodb.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Initialize MongoDB connection
$db = connectToMongoDB();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UD Rahayu - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
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

        .container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 1rem;
            margin-top: 3.5rem;
        }

        .welcome-box {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
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

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 0;
        }

        .menu-item {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #dee2e6;
        }

        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
        }

        .menu-item i {
            font-size: 2rem;
            color: #4a2c2a;
            margin-bottom: 1rem;
        }

        .menu-item h3 {
            font-size: 1.25rem;
            margin: 0.5rem 0;
            color: #4a2c2a;
        }

        .menu-item p {
            font-size: 0.95rem;
            color: #666;
            margin: 0;
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
        <h1>UD RAHAYU - Admin Dashboard</h1>
        <div class="user-info">
            <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-box">
            <h2>Selamat Datang di Panel Admin</h2>
            <p>Kelola toko Anda dengan mudah menggunakan panel admin ini.</p>
        </div>

        <div class="menu-grid">
            <a href="products.php" class="menu-item">
                <i class="fas fa-box"></i>
                <h3>Produk</h3>
                <p>Kelola produk dan stok</p>
            </a>

            <a href="orders.php" class="menu-item">
                <i class="fas fa-shopping-cart"></i>
                <h3>Pesanan</h3>
                <p>Lihat dan kelola pesanan</p>
            </a>

            <a href="users.php" class="menu-item">
                <i class="fas fa-users"></i>
                <h3>Pengguna</h3>
                <p>Kelola akun pengguna</p>
            </a>

            <a href="reports.php" class="menu-item">
                <i class="fas fa-clipboard-list"></i>
                <h3>Laporan</h3>
                <p>Lihat laporan penjualan</p>
            </a>
        </div>
    </div>
</body>
</html> 