<?php
session_start();
require_once '../../config/mongodb.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

try {
    // Ambil data dari request
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['product_id']) || !isset($data['quantity'])) {
        echo json_encode(['success' => false, 'message' => 'Data produk tidak lengkap']);
        exit();
    }

    // Konversi ID ke ObjectId
    $user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    $product_id = new MongoDB\BSON\ObjectId($data['product_id']);
    $quantity = (int)$data['quantity'];

    // Validasi jumlah minimal
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Jumlah pesanan minimal 1']);
        exit();
    }

    $db = connectToMongoDB();
    
    // Cek ketersediaan produk
    $product = $db->products->findOne(['_id' => $product_id]);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
        exit();
    }

    // Cek stok produk
    if ($product->stock < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $product->stock]);
        exit();
    }

    // Cek apakah produk sudah ada di keranjang
    $existing_item = $db->cart->findOne([
        'user_id' => $user_id,
        'product_id' => $product_id
    ]);

    if ($existing_item) {
        // Jika sudah ada, update jumlahnya
        $new_quantity = $existing_item->quantity + $quantity;
        
        // Cek lagi dengan total quantity baru
        if ($product->stock < $new_quantity) {
            echo json_encode(['success' => false, 'message' => 'Total pesanan melebihi stok yang tersedia']);
            exit();
        }

        $result = $db->cart->updateOne(
            [
                'user_id' => $user_id,
                'product_id' => $product_id
            ],
            [
                '$set' => [
                    'quantity' => $new_quantity,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );

        if ($result->getModifiedCount() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Jumlah produk dalam keranjang berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Gagal memperbarui jumlah produk'
            ]);
        }
    } else {
        // Jika belum ada, tambahkan ke keranjang
        $result = $db->cart->insertOne([
            'user_id' => $user_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]);

        if ($result->getInsertedCount() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Produk berhasil ditambahkan ke keranjang'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Gagal menambahkan produk ke keranjang'
            ]);
        }
    }

} catch (Exception $e) {
    error_log("Error in add to cart: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan saat menambahkan produk ke keranjang'
    ]);
}
?> 