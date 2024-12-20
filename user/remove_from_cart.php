<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    // Hapus produk dari keranjang
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    
    // Redirect kembali ke keranjang
    header("Location: " . BASE_URL . "/user/cart.php");
    exit();
} else {
    header("Location: " . BASE_URL . "/user/cart.php");
    exit();
} 