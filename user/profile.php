<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';

// Inisialisasi koneksi MongoDB
$db = connectToMongoDB();
$user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

// Ambil data user
$user = $db->users->findOne(['_id' => $user_id]);

// Proses update profil jika ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update_data = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];

    try {
        $result = $db->users->updateOne(
            ['_id' => $user_id],
            ['$set' => $update_data]
        );

        if ($result->getModifiedCount() > 0) {
            $_SESSION['success'] = 'Profil berhasil diperbarui';
            header('Location: profile.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Gagal memperbarui profil: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - UD Rahayu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header h2 {
            color: #4a2c2a;
            font-size: 1.5rem;
        }

        .profile-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .profile-header {
            background: #4a2c2a;
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .profile-header i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .profile-header h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .profile-header p {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .profile-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-size: 0.95rem;
            color: #4a2c2a;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-group label i {
            margin-right: 0.5rem;
            width: 20px;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #4a2c2a;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .btn-submit {
            background: #4a2c2a;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 4px;
            font-size: 0.95rem;
            width: 100%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: #6d4c4a;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media screen and (max-width: 768px) {
            .navbar {
                padding: 0 1.5rem;
                height: 60px;
            }
            
            .navbar h1 {
                font-size: 1.25rem;
            }
            
            .header h2 {
                font-size: 1.35rem;
            }
            
            .profile-header {
                padding: 1.5rem;
            }
            
            .profile-form {
                padding: 1.5rem;
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

            .profile-header h3 {
                font-size: 1.15rem;
            }

            .profile-header p,
            .form-group label,
            .form-control,
            .btn-submit,
            .alert {
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
            <a href="index.php" class="logout-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>Profil Saya</h2>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-header">
                <i class="fas fa-user-circle"></i>
                <h3><?php echo htmlspecialchars($user->name ?? ''); ?></h3>
                <p><?php echo htmlspecialchars($user->username ?? ''); ?></p>
            </div>
            <div class="profile-form">
                <form method="POST" action="">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?php echo htmlspecialchars($user->name ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i>Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?php echo htmlspecialchars($user->email ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i>Nomor Telepon</label>
                        <input type="tel" class="form-control" name="phone" 
                               value="<?php echo htmlspecialchars($user->phone ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i>Alamat</label>
                        <textarea class="form-control" name="address" required><?php echo htmlspecialchars($user->address ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 