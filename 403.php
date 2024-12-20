<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Akses Ditolak - UD Rahayu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4a2c2a 0%, #6d4c4a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .error-icon {
            font-size: 64px;
            color: #4a2c2a;
            margin-bottom: 20px;
        }

        h1 {
            color: #4a2c2a;
            margin-bottom: 10px;
        }

        p {
            color: #666;
            margin-bottom: 20px;
        }

        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #4a2c2a 0%, #6d4c4a 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .tracking-button {
            margin: 20px 0;
        }

        .tracking-button .btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .tracking-button .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .track-icon {
            width: 24px;
            height: 24px;
        }

        .tracking-button .btn div {
            display: flex;
            flex-direction: column;
        }

        .tracking-button .btn span {
            font-size: 12px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <i class="fas fa-exclamation-triangle error-icon"></i>
        <h1>403 - Akses Ditolak</h1>
        <p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <a href="<?php echo BASE_URL; ?>" class="btn-back">
            <i class="fas fa-home"></i> Kembali ke Beranda
        </a>
        <div class="tracking-button">
            <a href="<?php echo site_url('orders/track'); ?>" class="btn btn-primary">
                <img src="<?php echo base_url('assets/images/logo.svg'); ?>" alt="Track Icon" class="track-icon">
                <div>
                    <strong>Pesanan Saya</strong>
                    <span>Lacak pesanan Anda</span>
                </div>
            </a>
        </div>
    </div>
</body>
</html> 