<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

// Coba koneksi ke database
try {
    $db = connectDB();
} catch (Exception $e) {
    die("Error koneksi database: " . $e->getMessage());
}

// Jika user sudah login, arahkan ke halaman yang sesuai
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: " . BASE_URL . "/admin/index.php");
    } else {
        header("Location: " . BASE_URL . "/user/index.php");
    }
    exit();
}

// Jika belum login, arahkan ke halaman login
header("Location: " . BASE_URL . "/login.php");
exit();
?>