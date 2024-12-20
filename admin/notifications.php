<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config/mongodb.php';

// Ensure user is admin
requireAdmin();
$user = getCurrentUser();

// Get MongoDB instance
$mongodb = MongoDB::getInstance();
$notifications = $mongodb->getCollection('notifications');

// Handle notification actions (mark as read, delete, etc)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $notifId = new MongoDB\BSON\ObjectId($_POST['notification_id']);
        
        switch ($_POST['action']) {
            case 'mark_read':
                $notifications->updateOne(
                    ['_id' => $notifId],
                    ['$set' => ['status' => 'read']]
                );
                break;
                
            case 'delete':
                $notifications->deleteOne(['_id' => $notifId]);
                break;
        }
        
        // Redirect to prevent form resubmission
        header('Location: notifications.php');
        exit;
    }
}

// Get all notifications, sorted by date (newest first)
$allNotifications = $notifications->find(
    [],
    [
        'sort' => ['created_at' => -1],
        'limit' => 50 // Limit to last 50 notifications
    ]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">UD Rahayu - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="notifications.php">
                            Notifications
                            <span class="badge bg-danger">New</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Users</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">
                        Welcome, <?php echo htmlspecialchars($user['username']); ?>
                    </span>
                    <a href="../logout.php" class="btn btn-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Notifications</h2>
            <div>
                <button class="btn btn-success" onclick="markAllAsRead()">
                    <i class="bi bi-check-all"></i> Mark All as Read
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <?php foreach ($allNotifications as $notif): ?>
                            <div class="notification-item p-3 border-bottom <?php echo isset($notif->status) && $notif->status === 'read' ? 'bg-light' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">
                                            <?php if (!isset($notif->status) || $notif->status !== 'read'): ?>
                                                <span class="badge bg-primary">New</span>
                                            <?php endif; ?>
                                            Order Update
                                        </h5>
                                        <p class="mb-1"><?php echo htmlspecialchars($notif->message); ?></p>
                                        <small class="text-muted">
                                            <?php echo $notif->created_at->toDateTime()->format('d M Y H:i'); ?>
                                        </small>
                                    </div>
                                    <div class="btn-group">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="notification_id" value="<?php echo $notif->_id; ?>">
                                            <?php if (!isset($notif->status) || $notif->status !== 'read'): ?>
                                                <button type="submit" name="action" value="mark_read" class="btn btn-sm btn-success me-2">
                                                    <i class="bi bi-check"></i> Mark as Read
                                                </button>
                                            <?php endif; ?>
                                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this notification?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (!iterator_count($allNotifications)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-bell-slash fs-1 text-muted"></i>
                                <p class="mt-3">No notifications found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAllAsRead() {
            if (confirm('Mark all notifications as read?')) {
                // You can implement this functionality later
                alert('This feature will be implemented soon!');
            }
        }
    </script>
</body>
</html>