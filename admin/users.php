<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

try {
    // Initialize MongoDB connection
    $db = connectDB();
    
    // Get all users except current admin
    $users = $db->users->find(
        ['_id' => ['$ne' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])]], 
        ['sort' => ['username' => 1]]
    )->toArray();
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - UD Rahayu</title>
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
            margin-bottom: 2rem;
        }

        .navbar-brand {
            font-size: 1.25rem;
            margin: 0;
            padding: 0;
            font-weight: 600;
            color: white;
            text-decoration: none;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 0.95rem;
        }

        .admin-text {
            color: white;
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

        .header p {
            color: #666;
            font-size: 0.95rem;
        }

        .users-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }

        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #4a2c2a;
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .users-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            display: inline-block;
        }

        .status-active { 
            background: #d4edda; 
            color: #155724; 
        }

        .status-inactive { 
            background: #f8d7da; 
            color: #721c24; 
        }

        .action-btn {
            padding: 0.4rem 0.75rem;
            border-radius: 4px;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
            margin-right: 0.5rem;
        }

        .btn-edit {
            background: #4a2c2a;
            color: white;
        }

        .btn-edit:hover {
            background: #6d4c4a;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            color: white;
        }

        .empty-state {
            background: white;
            border-radius: 8px;
            padding: 3rem 1rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #4a2c2a;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        @media screen and (max-width: 768px) {
            .navbar {
                padding: 0 1.5rem;
                height: 60px;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
            }

            .navbar-right {
                gap: 1rem;
                font-size: 0.9rem;
            }

            .logout-btn {
                padding: 0.5rem 1rem;
                height: 34px;
                font-size: 0.9rem;
            }

            .header h2 {
                font-size: 1.35rem;
            }

            .header p {
                font-size: 0.9rem;
            }

            .users-table {
                display: block;
                overflow-x: auto;
            }

            .users-table th,
            .users-table td {
                font-size: 0.9rem;
                padding: 0.75rem;
            }

            .action-btn {
                padding: 0.35rem 0.65rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">UD RAHAYU - Admin Dashboard</a>
        <div class="navbar-right">
            <span class="admin-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="index.php" class="logout-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>Kelola Pengguna</h2>
            <p>Daftar semua pengguna yang terdaftar di sistem</p>
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

        <?php if (empty($users)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Belum ada pengguna</h3>
                <p>Belum ada pengguna yang terdaftar dalam sistem</p>
            </div>
        <?php else: ?>
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user->name ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user->username); ?></td>
                            <td><?php echo htmlspecialchars($user->email ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user->phone ?? '-'); ?></td>
                            <td>
                                <span class="status-badge <?php echo isset($user->status) && $user->status === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo isset($user->status) && $user->status === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user->_id; ?>" class="action-btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_user.php?id=<?php echo $user->_id; ?>" 
                                   class="action-btn btn-delete" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 