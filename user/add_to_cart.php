<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    try {
        $db = connectDB();
        $product_id = $_POST['product_id'];
        
        // Cek apakah produk ada
        $product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
        
        if ($product) {
            // Inisialisasi keranjang jika belum ada
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            // Tambahkan atau update jumlah produk di keranjang
            if (isset($_SESSION['cart'][$product_id])) {
                // Cek stok sebelum menambah
                if ($_SESSION['cart'][$product_id] < $product->stock) {
                    $_SESSION['cart'][$product_id]++;
                }
            } else {
                $_SESSION['cart'][$product_id] = 1;
            }
            
            // Redirect kembali ke halaman produk
            header("Location: " . BASE_URL . "/user/products.php?success=1");
        } else {
            header("Location: " . BASE_URL . "/user/products.php?error=product_not_found");
        }
    } catch (Exception $e) {
        header("Location: " . BASE_URL . "/user/products.php?error=system_error");
    }
    exit();
} else {
    header("Location: " . BASE_URL . "/user/products.php");
    exit();
} 