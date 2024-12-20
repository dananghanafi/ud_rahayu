<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login admin
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    try {
        $db = connectDB();
        
        // Validasi input
        $product_id = $_POST['product_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        
        if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
            throw new Exception("Semua field harus diisi dengan benar");
        }
        
        // Update data
        $update_data = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        // Handle upload gambar jika ada
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception("Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF");
            }
            
            // Buat direktori jika belum ada
            $upload_dir = __DIR__ . '/../assets/images/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate nama file unik
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Pindahkan file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Hapus gambar lama jika ada
                $old_product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
                if ($old_product && !empty($old_product->image_url)) {
                    $old_image_path = __DIR__ . '/..' . $old_product->image_url;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                $update_data['image_url'] = '/assets/images/products/' . $filename;
            } else {
                throw new Exception("Gagal mengupload gambar");
            }
        }
        
        // Update ke database
        $result = $db->products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($product_id)],
            ['$set' => $update_data]
        );
        
        if ($result->getModifiedCount() > 0) {
            header("Location: " . BASE_URL . "/admin/products.php?success=Produk berhasil diperbarui");
        } else {
            throw new Exception("Gagal memperbarui produk");
        }
    } catch (Exception $e) {
        header("Location: " . BASE_URL . "/admin/products.php?error=" . urlencode($e->getMessage()));
    }
    exit();
} else {
    header("Location: " . BASE_URL . "/admin/products.php");
    exit();
} 