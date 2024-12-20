<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$db = getMongoDBConnection();

// Proses penambahan produk baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $product = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price' => (float)$_POST['price'],
            'category' => $_POST['category'],
            'image_url' => $_POST['image_url'],
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        $db->products->insertOne($product);
    }
    // Proses update produk
    elseif ($_POST['action'] === 'edit') {
        $id = $_POST['id'];
        $update = [
            '$set' => [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => (float)$_POST['price'],
                'category' => $_POST['category'],
                'image_url' => $_POST['image_url'],
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ];
        $db->products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            $update
        );
    }
    // Proses hapus produk
    elseif ($_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $db->products->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
    }
}

// Mengambil semua produk
$products = $db->products->find();
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kelola Produk</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </div>

    <!-- Tabel Produk -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <img src="<?php echo $product->image_url ?? '/assets/images/default-product.jpg'; ?>" 
                                     alt="<?php echo $product->name; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td><?php echo $product->name; ?></td>
                            <td><?php echo $product->category; ?></td>
                            <td>Rp <?php echo number_format($product->price, 0, ',', '.'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info me-1" onclick="editProduct('<?php echo $product->_id; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct('<?php echo $product->_id; ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" class="form-control" name="category" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Gambar</label>
                        <input type="url" class="form-control" name="image_url">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Produk -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" name="price" id="edit_price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" class="form-control" name="category" id="edit_category" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Gambar</label>
                        <input type="url" class="form-control" name="image_url" id="edit_image_url">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Hapus Produk -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function editProduct(id) {
    // Implementasi JavaScript untuk mengisi form edit
    const product = <?php echo json_encode(iterator_to_array($products)); ?>;
    const selectedProduct = product.find(p => p._id.$oid === id);
    
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = selectedProduct.name;
    document.getElementById('edit_description').value = selectedProduct.description;
    document.getElementById('edit_price').value = selectedProduct.price;
    document.getElementById('edit_category').value = selectedProduct.category;
    document.getElementById('edit_image_url').value = selectedProduct.image_url;
    
    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}

function deleteProduct(id) {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?> 