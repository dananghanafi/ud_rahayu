<?php
// Fungsi untuk membuat direktori dan set permission
function createDirectory($path) {
    if (!file_exists($path)) {
        // Buat direktori dengan permission penuh
        if (!mkdir($path, 0777, true)) {
            throw new Exception("Gagal membuat direktori: " . $path);
        }
    }
    // Set permission 777 untuk memastikan akses penuh
    chmod($path, 0777);
    error_log("Created directory with full permissions: " . $path);
}

try {
    // Definisikan path yang diperlukan
    $base_path = dirname(__DIR__); // Path ke root project
    
    // Pastikan base_path valid
    if (!file_exists($base_path)) {
        throw new Exception("Root directory tidak ditemukan: " . $base_path);
    }

    // Daftar direktori yang perlu dibuat
    $dirs = [
        $base_path . DIRECTORY_SEPARATOR . 'assets',
        $base_path . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images',
        $base_path . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products'
    ];

    // Buat dan set permission untuk setiap direktori
    foreach ($dirs as $dir) {
        createDirectory($dir);
        // Double check permission
        if (!is_writable($dir)) {
            chmod($dir, 0777);
            error_log("Re-applied permissions to: " . $dir);
        }
    }

    // Verifikasi hasil
    $success = true;
    foreach ($dirs as $dir) {
        if (!is_dir($dir) || !is_writable($dir)) {
            $success = false;
            error_log("Directory check failed for: " . $dir);
            throw new Exception("Direktori tidak dapat diakses: " . $dir);
        }
    }

    if ($success) {
        error_log("All directories created and verified successfully");
    }

} catch (Exception $e) {
    error_log("Setup Error: " . $e->getMessage());
    throw $e; // Re-throw exception untuk ditangani oleh caller
}
?> 