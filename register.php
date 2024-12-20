<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

initSession();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid request');
        }

        $db = connectDB();
        $users = $db->users;

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validasi input
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            throw new Exception("Semua field harus diisi");
        }
        
        if (!validateUsername($username)) {
            throw new Exception("Username hanya boleh mengandung huruf, angka, titik, dan underscore (maksimal " . MAX_USERNAME_LENGTH . " karakter)");
        }
        
        if (!validatePassword($password)) {
            throw new Exception("Password minimal " . MIN_PASSWORD_LENGTH . " karakter dan harus mengandung huruf besar, huruf kecil, dan angka");
        }
        
        if ($password !== $confirm_password) {
            throw new Exception("Password tidak cocok");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format email tidak valid");
        }
        
        // Cek username dan email sudah ada atau belum
        $existingUser = $users->findOne(['$or' => [
            ['username' => $username],
            ['email' => $email]
        ]]);

        if ($existingUser) {
            if ($existingUser->username === $username) {
                throw new Exception("Username sudah digunakan");
            } else {
                throw new Exception("Email sudah digunakan");
            }
        }

        // Buat user baru
        $result = $users->insertOne([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
            'status' => 'active',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]);

        if ($result->getInsertedId()) {
            $success = "Registrasi berhasil! Anda akan dialihkan ke halaman login...";
            // Redirect ke login dengan proper header
            header("Location: " . BASE_URL . "/login.php?registered=1");
            exit();
        } else {
            throw new Exception("Gagal membuat akun");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Generate new CSRF token
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UD Rahayu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/auth.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.svg" alt="UD Rahayu Logo">
            <h1>UD RAHAYU</h1>
            <p>Buat akun baru</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" 
                           placeholder="Masukkan username"
                           maxlength="<?php echo MAX_USERNAME_LENGTH; ?>"
                           pattern="[a-zA-Z0-9._]+"
                           title="Username hanya boleh mengandung huruf, angka, titik, dan underscore"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" 
                           placeholder="Masukkan email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" 
                           placeholder="Masukkan password"
                           minlength="<?php echo MIN_PASSWORD_LENGTH; ?>"
                           title="Password minimal <?php echo MIN_PASSWORD_LENGTH; ?> karakter, harus mengandung huruf besar, huruf kecil, dan angka"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Masukkan ulang password"
                           minlength="<?php echo MIN_PASSWORD_LENGTH; ?>"
                           required>
                </div>
            </div>

            <button type="submit" class="btn-auth">
                <i class="fas fa-user-plus"></i>
                Daftar
            </button>
        </form>

        <div class="auth-link">
            Sudah punya akun? <a href="<?php echo BASE_URL; ?>/login.php">Login sekarang</a>
        </div>
    </div>
</body>
</html> 