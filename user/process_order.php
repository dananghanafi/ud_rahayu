<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';

// Inisialisasi koneksi MongoDB
$db = connectToMongoDB();

// Cek jika bukan POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit();
}

try {
    // Ambil data user
    $user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    $user = $db->users->findOne(['_id' => $user_id]);

    if (!$user) {
        throw new Exception('Data user tidak ditemukan');
    }

    // Ambil data keranjang
    $cart_items = iterator_to_array($db->cart->aggregate([
        [
            '$match' => ['user_id' => $user_id]
        ],
        [
            '$lookup' => [
                'from' => 'products',
                'localField' => 'product_id',
                'foreignField' => '_id',
                'as' => 'product'
            ]
        ],
        [
            '$unwind' => '$product'
        ]
    ]));

    if (empty($cart_items)) {
        throw new Exception('Keranjang belanja Anda kosong');
    }

    // Hitung total
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item->product->price * $item->quantity;
    }

    $pajak = $subtotal * 0.11;
    $total = $subtotal + $pajak;

    // Validasi metode pembayaran
    if (!isset($_POST['payment_method']) || !in_array($_POST['payment_method'], ['cash', 'transfer'])) {
        throw new Exception('Metode pembayaran tidak valid');
    }

    // Generate nomor pesanan
    $order_number = 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -5));

    // Ambil data user untuk customer_info
    $customer_info = [
        'name' => $user->name ?? '',
        'email' => $user->email ?? '',
        'phone' => $user->phone ?? '',
        'address' => $user->address ?? '',
        'username' => $user->username ?? ''
    ];

    // Buat pesanan baru
    $order_data = [
        'order_number' => $order_number,
        'user_id' => $user_id,
        'customer_info' => $customer_info,
        'items' => array_map(function($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'subtotal' => $item->product->price * $item->quantity
            ];
        }, $cart_items),
        'payment_info' => [
            'subtotal' => $subtotal,
            'tax' => $pajak,
            'total' => $total,
            'method' => $_POST['payment_method'],
            'status' => 'pending',
            'verification_status' => 'pending',
            'verified_by' => null,
            'verified_at' => null,
            'verification_note' => null,
            'proof_of_payment' => null
        ],
        'order_status' => 'pending',
        'status_history' => [
            [
                'status' => 'pending',
                'timestamp' => new MongoDB\BSON\UTCDateTime(),
                'note' => 'Pesanan dibuat'
            ]
        ],
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];

    // Debug: Tampilkan data user
    error_log("User Data: " . json_encode($user));
    error_log("Customer Info: " . json_encode($customer_info));

    // Simpan pesanan
    $order_result = $db->orders->insertOne($order_data);

    if (!$order_result->getInsertedId()) {
        throw new Exception('Gagal menyimpan pesanan');
    }

    // Kosongkan keranjang
    $db->cart->deleteMany(['user_id' => $user_id]);

    // Set session untuk halaman konfirmasi
    $_SESSION['order_success'] = [
        'order_id' => (string)$order_result->getInsertedId(),
        'order_number' => $order_number,
        'payment_method' => $_POST['payment_method'],
        'total' => $total
    ];

    // Redirect ke halaman konfirmasi
    header('Location: order_confirmation.php?order_id=' . $order_result->getInsertedId());
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: checkout.php');
    exit();
}
?> 