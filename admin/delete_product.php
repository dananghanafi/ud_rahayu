<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login admin
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    try {
        $db = connectDB();
        $product_id = $_POST['product_id'];
        
        // Ambil info produk untuk hapus gambar
        $product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
        
        if ($product) {
            // Hapus gambar jika ada
            if (!empty($product->image_url)) {
                $image_path = __DIR__ . '/..' . $product->image_url;
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // Hapus produk dari database
            $result = $db->products->deleteOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
            
            if ($result->getDeletedCount() > 0) {
                header("Location: " . BASE_URL . "/admin/products.php?success=Produk berhasil dihapus");
            } else {
                throw new Exception("Gagal menghapus produk");
            }
        } else {
            throw new Exception("Produk tidak ditemukan");
        }
    } catch (Exception $e) {
        header("Location: " . BASE_URL . "/admin/products.php?error=" . urlencode($e->getMessage()));
    }
    exit();
} else {
    header("Location: " . BASE_URL . "/admin/products.php");
    exit();
} 