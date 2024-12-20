<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';

// Inisialisasi koneksi MongoDB
$db = connectToMongoDB();

// Ambil data user
$user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
$user = $db->users->findOne(['_id' => $user_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Data User - UD Rahayu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Data User di MongoDB</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($user): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>ID</th>
                                        <td><?php echo $user->_id; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td><?php echo $user->username ?? 'Tidak ada'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nama</th>
                                        <td><?php echo $user->name ?? 'Tidak ada'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?php echo $user->email ?? 'Tidak ada'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Telepon</th>
                                        <td><?php echo $user->phone ?? 'Tidak ada'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Alamat</th>
                                        <td><?php echo $user->address ?? 'Tidak ada'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
                                        <td><?php echo $user->role ?? 'Tidak ada'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Data user tidak ditemukan
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="checkout.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 