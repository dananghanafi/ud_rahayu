<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID pengguna tidak ditemukan";
    header('Location: users.php');
    exit();
}

try {
    $db = connectDB();
    $user_id = new MongoDB\BSON\ObjectId($_GET['id']);
    
    // If form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $update_data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'status' => $_POST['status']
        ];

        $result = $db->users->updateOne(
            ['_id' => $user_id],
            ['$set' => $update_data]
        );

        if ($result->getModifiedCount() > 0) {
            $_SESSION['success'] = "Data pengguna berhasil diperbarui";
            header('Location: users.php');
            exit();
        }
    }

    // Get user data
    $user = $db->users->findOne(['_id' => $user_id]);
    if (!$user) {
        $_SESSION['error'] = "Pengguna tidak ditemukan";
        header('Location: users.php');
        exit();
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: users.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - UD Rahayu</title>
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

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .header {
            margin-bottom: 1.5rem;
        }

        .header h2 {
            color: #4a2c2a;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .edit-form {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #4a2c2a;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
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

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
        }

        .btn-primary {
            background: #4a2c2a;
            color: white;
        }

        .btn-primary:hover {
            background: #6d4c4a;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        @media screen and (max-width: 768px) {
            .navbar {
                padding: 0 1.5rem;
                height: 60px;
            }
            
            .navbar h1 {
                font-size: 1.25rem;
            }

            .edit-form {
                padding: 1.5rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>UD RAHAYU</h1>
        <div class="user-info">
            <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="users.php" class="logout-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>Edit Pengguna</h2>
        </div>

        <div class="edit-form">
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->username); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user->name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user->email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user->phone ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status">
                        <option value="active" <?php echo (isset($user->status) && $user->status === 'active') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo (!isset($user->status) || $user->status === 'inactive') ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="users.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 