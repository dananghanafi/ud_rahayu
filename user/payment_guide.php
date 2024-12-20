<?php
session_start();
require_once '../config/mongodb.php';
require_once '../includes/auth_check.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Pembayaran - UD Rahayu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="text-center mb-4">Panduan Pembayaran</h2>
                
                <!-- Bayar di Kasir -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Bayar di Kasir
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="step mb-3">
                                <h6><i class="fas fa-shopping-cart me-2"></i>Langkah 1: Pilih Produk</h6>
                                <p class="text-muted ms-4 mb-0">Pilih produk yang ingin dibeli dan masukkan ke keranjang</p>
                            </div>
                            <div class="step mb-3">
                                <h6><i class="fas fa-cash-register me-2"></i>Langkah 2: Checkout</h6>
                                <p class="text-muted ms-4 mb-0">Klik checkout dan pilih metode "Bayar di Kasir"</p>
                            </div>
                            <div class="step mb-3">
                                <h6><i class="fas fa-receipt me-2"></i>Langkah 3: Dapatkan Nomor Pesanan</h6>
                                <p class="text-muted ms-4 mb-0">Sistem akan memberikan nomor pesanan unik</p>
                            </div>
                            <div class="step mb-3">
                                <h6><i class="fas fa-clipboard-check me-2"></i>Langkah 4: Pembayaran</h6>
                                <p class="text-muted ms-4 mb-0">Tunjukkan nomor pesanan ke kasir dan lakukan pembayaran</p>
                            </div>
                            <div class="step">
                                <h6><i class="fas fa-mug-hot me-2"></i>Langkah 5: Pesanan Diproses</h6>
                                <p class="text-muted ms-4 mb-0">Setelah pembayaran dikonfirmasi, pesanan akan segera diproses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transfer Bank -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-university me-2"></i>
                            Transfer Bank
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="step mb-3">
                                <h6><i class="fas fa-shopping-cart me-2"></i>Langkah 1: Pilih Produk</h6>
                                <p class="text-muted ms-4 mb-0">Pilih produk yang ingin dibeli dan masukkan ke keranjang</p>
                            </div>
                            <div class="step mb-3">
                                <h6><i class="fas fa-credit-card me-2"></i>Langkah 2: Checkout</h6>
                                <p class="text-muted ms-4 mb-0">Klik checkout dan pilih metode "Transfer Bank"</p>
                            </div>
                            <div class="step mb-3">
                                <h6><i class="fas fa-university me-2"></i>Langkah 3: Transfer Pembayaran</h6>
                                <p class="text-muted ms-4">Transfer ke rekening yang tertera:</p>
                                <div class="alert alert-info ms-4">
                                    <strong>Bank BCA</strong><br>
                                    No. Rekening: 1234567890<br>
                                    Atas Nama: UD Rahayu
                                </div>
                            </div>
                            <div class="step mb-3">
                                <h6><i class="fab fa-whatsapp me-2"></i>Langkah 4: Kirim Bukti Transfer</h6>
                                <p class="text-muted ms-4 mb-0">
                                    Kirim bukti transfer ke WhatsApp admin:<br>
                                    <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-success btn-sm mt-2">
                                        <i class="fab fa-whatsapp me-2"></i>Chat Admin (081234567890)
                                    </a>
                                </p>
                            </div>
                            <div class="step mb-3">
                                <h6><i class="fas fa-clock me-2"></i>Langkah 5: Verifikasi</h6>
                                <p class="text-muted ms-4 mb-0">Admin akan memverifikasi pembayaran dalam waktu maksimal 1x24 jam</p>
                            </div>
                            <div class="step">
                                <h6><i class="fas fa-mug-hot me-2"></i>Langkah 6: Pesanan Diproses</h6>
                                <p class="text-muted ms-4 mb-0">Setelah pembayaran diverifikasi, pesanan akan segera diproses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Kembali -->
                <div class="text-center mt-4">
                    <a href="menu.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Menu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 