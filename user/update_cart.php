<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['action'])) {
    try {
        $db = connectDB();
        $product_id = $_POST['product_id'];
        $action = $_POST['action'];
        
        // Cek apakah produk ada di keranjang
        if (isset($_SESSION['cart'][$product_id])) {
            $product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
            
            if ($product) {
                if ($action === 'increase') {
                    // Tambah jumlah jika stok masih ada
                    if ($_SESSION['cart'][$product_id] < $product->stock) {
                        $_SESSION['cart'][$product_id]++;
                    }
                } elseif ($action === 'decrease') {
                    // Kurangi jumlah
                    if ($_SESSION['cart'][$product_id] > 1) {
                        $_SESSION['cart'][$product_id]--;
                    } else {
                        // Hapus produk jika jumlah 1 dan dikurangi
                        unset($_SESSION['cart'][$product_id]);
                    }
                }
            }
        }
        
        // Redirect kembali ke keranjang
        header("Location: " . BASE_URL . "/user/cart.php");
    } catch (Exception $e) {
        header("Location: " . BASE_URL . "/user/cart.php?error=system_error");
    }
    exit();
} else {
    header("Location: " . BASE_URL . "/user/cart.php");
    exit();
} 