<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

initSession();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid request');
        }

        // Check login attempts
        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            if (time() - $_SESSION['last_attempt'] < LOGIN_TIMEOUT) {
                throw new Exception('Terlalu banyak percobaan login. Silakan coba lagi dalam ' . 
                    ceil((LOGIN_TIMEOUT - (time() - $_SESSION['last_attempt'])) / 60) . ' menit.');
            }
            // Reset attempts after timeout
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt']);
        }

        $db = connectDB();
        $users = $db->users;
        
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Validate input
        if (!validateUsername($username)) {
            throw new Exception('Username tidak valid');
        }
        
        // Cari user
        $user = $users->findOne(['username' => $username]);
        
        if ($user && password_verify($password, $user->password)) {
            // Reset login attempts on success
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt']);

            // Set session variables
            $_SESSION['user_id'] = (string)$user->_id;
            $_SESSION['username'] = $user->username;
            $_SESSION['role'] = $user->role;
            
            // Redirect berdasarkan role
            if ($user->role === 'admin') {
                header("Location: " . BASE_URL . "/admin/index.php");
            } else {
                header("Location: " . BASE_URL . "/user/index.php");
            }
            exit();
        } else {
            // Increment login attempts
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $_SESSION['last_attempt'] = time();
            
            if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
                throw new Exception('Terlalu banyak percobaan login. Silakan coba lagi dalam 5 menit.');
            }
            
            $error = 'Username atau password salah';
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
    <title>Login - UD Rahayu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/auth.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.svg" alt="UD Rahayu Logo">
            <h1>UD RAHAYU</h1>
            <p>Silakan login untuk melanjutkan</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
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
                           required>
                </div>
            </div>

            <button type="submit" class="btn-auth">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
        </form>

        <div class="auth-link">
            Belum punya akun? <a href="<?php echo BASE_URL; ?>/register.php">Daftar sekarang</a>
        </div>
    </div>
</body>
</html> 