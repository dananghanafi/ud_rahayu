<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login admin
checkAdmin();

try {
    $db = connectDB();
    $products = $db->products->find([], ['sort' => ['name' => 1]]);
    
    // Ambil pesan sukses atau error jika ada
    $success = $_GET['success'] ?? '';
    $error = $_GET['error'] ?? '';
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin UD Rahayu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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

        .add-btn {
            background: #4a2c2a;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-btn:hover {
            background: #6d4c4a;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .products-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .products-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th,
        .products-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .products-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #4a2c2a;
        }

        .products-table tr:last-child td {
            border-bottom: none;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .edit-btn {
            background: #4a2c2a;
        }

        .edit-btn:hover {
            background: #6d4c4a;
        }

        .delete-btn {
            background: #dc3545;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a2c2a;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: #4a2c2a;
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: #4a2c2a;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-submit:hover {
            background: #6d4c4a;
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
            <h2>Kelola Produk</h2>
            <button class="add-btn" onclick="showAddModal()">
                <i class="fas fa-plus"></i>
                Tambah Produk
            </button>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="products-table">
            <table>
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <img src="<?php echo BASE_URL . (!empty($product->image_url) ? $product->image_url : '/assets/images/product-placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($product->name); ?>" 
                                 class="product-image">
                        </td>
                        <td><?php echo htmlspecialchars($product->name); ?></td>
                        <td><?php echo htmlspecialchars($product->description); ?></td>
                        <td>Rp <?php echo number_format($product->price, 0, ',', '.'); ?></td>
                        <td><?php echo $product->stock; ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick="showEditModal('<?php echo $product->_id; ?>')">
                                <i class="fas fa-edit"></i>
                                Edit
                            </button>
                            <form action="delete_product.php" method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                <input type="hidden" name="product_id" value="<?php echo $product->_id; ?>">
                                <button type="submit" class="action-btn delete-btn">
                                    <i class="fas fa-trash"></i>
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Produk -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tambah Produk Baru</h3>
                <button class="modal-close" onclick="hideAddModal()">&times;</button>
            </div>
            <form action="add_product.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nama Produk</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Harga (Rp)</label>
                    <input type="number" id="price" name="price" class="form-control" min="0" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input type="number" id="stock" name="stock" class="form-control" min="0" required>
                </div>
                <div class="form-group">
                    <label for="image">Gambar Produk</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Simpan Produk
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Produk</h3>
                <button class="modal-close" onclick="hideEditModal()">&times;</button>
            </div>
            <form action="edit_product.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_product_id" name="product_id">
                <div class="form-group">
                    <label for="edit_name">Nama Produk</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Deskripsi</label>
                    <textarea id="edit_description" name="description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_price">Harga (Rp)</label>
                    <input type="number" id="edit_price" name="price" class="form-control" min="0" required>
                </div>
                <div class="form-group">
                    <label for="edit_stock">Stok</label>
                    <input type="number" id="edit_stock" name="stock" class="form-control" min="0" required>
                </div>
                <div class="form-group">
                    <label for="edit_image">Gambar Produk (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="file" id="edit_image" name="image" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk modal tambah produk
        function showAddModal() {
            document.getElementById('addModal').classList.add('show');
        }

        function hideAddModal() {
            document.getElementById('addModal').classList.remove('show');
        }

        // Fungsi untuk modal edit produk
        function showEditModal(productId) {
            // Di sini Anda bisa menambahkan AJAX untuk mengambil data produk
            // dan mengisi form edit dengan data tersebut
            document.getElementById('edit_product_id').value = productId;
            document.getElementById('editModal').classList.add('show');
        }

        function hideEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }

        // Tutup modal jika user klik di luar modal
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
    </script>
</body>
</html> 