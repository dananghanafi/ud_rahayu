<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login admin
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = connectDB();
        
        // Validasi input
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        
        if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
            throw new Exception("Semua field harus diisi dengan benar");
        }
        
        // Handle upload gambar
        $image_url = '';
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
                $image_url = '/assets/images/products/' . $filename;
            } else {
                throw new Exception("Gagal mengupload gambar");
            }
        }
        
        // Insert ke database
        $result = $db->products->insertOne([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image_url' => $image_url,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        
        if ($result->getInsertedId()) {
            header("Location: " . BASE_URL . "/admin/products.php?success=Produk berhasil ditambahkan");
        } else {
            throw new Exception("Gagal menambahkan produk");
        }
    } catch (Exception $e) {
        header("Location: " . BASE_URL . "/admin/products.php?error=" . urlencode($e->getMessage()));
    }
    exit();
} else {
    header("Location: " . BASE_URL . "/admin/products.php");
    exit();
} 