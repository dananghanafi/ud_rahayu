<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Cek login admin
checkAdmin();

try {
    $db = connectDB();
    
    // Proses update status jika ada
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
        $orderId = $_POST['order_id'];
        $status = $_POST['status'];
        
        $result = $db->orders->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($orderId)],
            ['$set' => [
                'status' => $status,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]]
        );
        
        if ($result->getModifiedCount() > 0) {
            header("Location: orders.php?success=Status pesanan berhasil diperbarui");
            exit();
        }
    }
    
    // Ambil semua pesanan
    $orders = $db->orders->find([], ['sort' => ['order_date' => -1]]);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin UD Rahayu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar {
            background: #4a2c2a;
            padding: 0 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 65px;
            margin-bottom: 2rem;
        }

        .navbar-brand {
            font-size: 1.25rem;
            margin: 0;
            padding: 0;
            font-weight: 600;
            color: white;
            text-decoration: none;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 0.95rem;
        }

        .admin-text {
            color: white;
            font-size: 0.95rem;
        }

        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1.25rem;
            border-radius: 4px;
            font-size: 0.95rem;
            transition: background 0.2s;
            height: 36px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
        }

        @media screen and (max-width: 768px) {
            .navbar {
                padding: 0 1.5rem;
                height: 60px;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
            }

            .navbar-right {
                gap: 1rem;
                font-size: 0.9rem;
            }

            .logout-btn {
                padding: 0.5rem 1rem;
                height: 34px;
                font-size: 0.9rem;
            }
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-processing { background: #17a2b8; color: #fff; }
        .status-completed { background: #28a745; color: #fff; }
        .status-cancelled { background: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">UD RAHAYU - Admin Dashboard</a>
        <div class="navbar-right">
            <span class="admin-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="index.php" class="logout-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h2>Kelola Pesanan</h2>
                <p class="text-muted mb-0">Kelola dan pantau status pesanan pelanggan</p>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo substr((string)$order['_id'], -6); ?></td>
                                <td><?php echo isset($order['customer_name']) ? htmlspecialchars($order['customer_name']) : 'Pelanggan'; ?></td>
                                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';
                                    switch ($order['status']) {
                                        case 'pending':
                                            $statusClass = 'status-pending';
                                            $statusText = 'Menunggu';
                                            break;
                                        case 'processing':
                                            $statusClass = 'status-processing';
                                            $statusText = 'Diproses';
                                            break;
                                        case 'completed':
                                            $statusClass = 'status-completed';
                                            $statusText = 'Selesai';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'status-cancelled';
                                            $statusText = 'Dibatalkan';
                                            break;
                                        default:
                                            $statusClass = 'status-pending';
                                            $statusText = 'Menunggu';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', $order['order_date']->toDateTime()->getTimestamp()); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="showOrderDetails('<?php echo $order['_id']; ?>')">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Pesanan -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetails">
                    <!-- Detail pesanan akan diisi melalui JavaScript -->
                </div>
                <div class="modal-footer">
                    <form id="updateStatusForm" method="POST" action="orders.php" class="w-100">
                        <input type="hidden" name="order_id" id="modalOrderId">
                        <div class="row g-2">
                            <div class="col">
                                <select name="status" id="status" class="form-select">
                                    <option value="pending">Menunggu</option>
                                    <option value="processing">Diproses</option>
                                    <option value="completed">Selesai</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Status
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let orderModal;
    
    document.addEventListener('DOMContentLoaded', function() {
        orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
        
        // Handle form submission
        document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const orderId = formData.get('order_id');
            const status = formData.get('status');
            
            if (confirm('Apakah Anda yakin ingin mengubah status pesanan ini?')) {
                this.submit();
            }
        });
    });

    async function showOrderDetails(orderId) {
        try {
            const response = await fetch(`get_order_details.php?id=${orderId}`);
            const data = await response.json();
            
            if (data.error) {
                alert(data.error);
                return;
            }

            document.getElementById('modalOrderId').value = orderId;
            document.getElementById('status').value = data.status;

            const orderDetails = document.getElementById('orderDetails');
            orderDetails.innerHTML = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-2">Informasi Pesanan</h6>
                        <p class="mb-1"><strong>ID Pesanan:</strong> #${orderId.substr(-6)}</p>
                        <p class="mb-1"><strong>Tanggal:</strong> ${data.order_date}</p>
                        <p class="mb-1"><strong>Status:</strong> <span class="status-badge status-${data.status}">${getStatusText(data.status)}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-2">Informasi Pelanggan</h6>
                        <p class="mb-1"><strong>Nama:</strong> ${data.customer_name}</p>
                        ${data.customer_email ? `<p class="mb-1"><strong>Email:</strong> ${data.customer_email}</p>` : ''}
                    </div>
                </div>

                <h6 class="mb-3">Detail Produk</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.items.map(item => `
                                <tr>
                                    <td>${item.product_name || 'Produk tidak ditemukan'}</td>
                                    <td>Rp ${formatNumber(item.price)}</td>
                                    <td>${item.quantity}</td>
                                    <td>Rp ${formatNumber(item.price * item.quantity)}</td>
                                </tr>
                            `).join('')}
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total</strong></td>
                                <td><strong>Rp ${formatNumber(data.total_amount)}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;

            orderModal.show();
        } catch (error) {
            console.error('Error:', error);
            alert('Gagal memuat detail pesanan');
        }
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function getStatusText(status) {
        switch (status) {
            case 'pending': return 'Menunggu';
            case 'processing': return 'Diproses';
            case 'completed': return 'Selesai';
            case 'cancelled': return 'Dibatalkan';
            default: return 'Menunggu';
        }
    }
    </script>
</body>
</html> 