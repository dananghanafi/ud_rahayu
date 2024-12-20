<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../config/config.php';
require_once '../includes/auth_check.php';

// Get order ID from URL
$order_id = $_GET['order_id'] ?? null;

// Initialize variables
$order = null;
$error = null;

if ($order_id) {
    try {
        $db = connectDB();
        
        // Get order details
        $order = $db->orders->findOne([
            '_id' => new MongoDB\BSON\ObjectId($order_id),
            'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])
        ]);
        
        if (!$order) {
            $error = "Pesanan tidak ditemukan";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - UD Rahayu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .navbar {
            background: #4a2c2a;
            padding: 1rem;
            color: white;
        }

        .navbar h1 {
            max-width: 1200px;
            margin: 0 auto;
        }

        .container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .success-card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
        }

        .success-title {
            color: #333;
            margin-bottom: 1rem;
        }

        .success-message {
            color: #666;
            margin-bottom: 2rem;
        }

        .order-details {
            text-align: left;
            margin: 2rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .detail-label {
            color: #666;
        }

        .detail-value {
            color: #333;
            font-weight: 500;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .btn-primary {
            background: #4a2c2a;
            color: white;
        }

        .btn-primary:hover {
            background: #6d4c4a;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #4a2c2a;
            margin-left: 1rem;
        }

        .btn-secondary:hover {
            background: #e2e6ea;
        }

        .buttons {
            margin-top: 2rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>UD RAHAYU</h1>
    </nav>

    <div class="container">
        <?php if ($error): ?>
            <div class="success-card">
                <div class="success-icon" style="background: #dc3545;">
                    <i class="fas fa-times"></i>
                </div>
                <h2 class="success-title">Terjadi Kesalahan</h2>
                <p class="success-message"><?php echo htmlspecialchars($error); ?></p>
                <div class="buttons">
                    <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
                </div>
            </div>
        <?php else: ?>
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2 class="success-title">Pesanan Berhasil Dibuat!</h2>
                <p class="success-message">
                    Terima kasih telah berbelanja di UD Rahayu. Pesanan Anda sedang diproses.
                </p>

                <div class="order-details">
                    <div class="detail-row">
                        <span class="detail-label">Nomor Pesanan:</span>
                        <span class="detail-value"><?php echo substr($order_id, -6); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Pembayaran:</span>
                        <span class="detail-value">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="status-badge">Pending</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tanggal Pesanan:</span>
                        <span class="detail-value"><?php 
                            if ($order['order_date'] instanceof MongoDB\BSON\UTCDateTime) {
                                echo $order['order_date']->toDateTime()->format('d/m/Y H:i');
                            } else {
                                echo 'Invalid date';
                            }
                        ?></span>
                    </div>
                </div>

                <div class="buttons">
                    <a href="check_orders.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lihat Pesanan Saya
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-shopping-cart"></i> Lanjut Belanja
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 