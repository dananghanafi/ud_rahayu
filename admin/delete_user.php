<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID pengguna tidak ditemukan";
    header('Location: users.php');
    exit();
}

try {
    $db = connectDB();
    $user_id = new MongoDB\BSON\ObjectId($_GET['id']);
    
    // Check if user exists
    $user = $db->users->findOne(['_id' => $user_id]);
    if (!$user) {
        $_SESSION['error'] = "Pengguna tidak ditemukan";
        header('Location: users.php');
        exit();
    }

    // Delete user
    $result = $db->users->deleteOne(['_id' => $user_id]);
    
    if ($result->getDeletedCount() > 0) {
        $_SESSION['success'] = "Pengguna berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus pengguna";
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header('Location: users.php');
exit();
?>