<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>System Check</h1>";

// Cek versi PHP
echo "<h2>PHP Version</h2>";
echo "Current version: " . phpversion() . "<br>";
echo "Required version: >= 7.4<br>";

// Cek ekstensi yang dibutuhkan
echo "<h2>Required Extensions</h2>";
$required_extensions = ['mongodb', 'json', 'session'];
foreach ($required_extensions as $ext) {
    echo $ext . ": " . (extension_loaded($ext) ? "✓ Installed" : "✗ Not Installed") . "<br>";
}

// Cek direktori yang diperlukan
echo "<h2>Directory Permissions</h2>";
$directories = [
    'assets/images',
    'config',
    'includes',
    'public'
];

foreach ($directories as $dir) {
    echo $dir . ": " . (is_writable($dir) ? "✓ Writable" : "✗ Not Writable") . "<br>";
}

// Cek koneksi MongoDB
echo "<h2>MongoDB Connection</h2>";
try {
    require_once "../config/database.php";
    $db = getMongoDBConnection();
    echo "MongoDB Connection: ✓ Success<br>";
    
    // Cek collections
    $collections = ['products', 'orders', 'users', 'notifications'];
    foreach ($collections as $collection) {
        echo "Collection '$collection': " . 
             ($db->listCollections(['filter' => ['name' => $collection]])->toArray() ? "✓ Exists" : "✗ Missing") . 
             "<br>";
    }
} catch (Exception $e) {
    echo "MongoDB Connection: ✗ Failed<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// Cek file konfigurasi
echo "<h2>Configuration Files</h2>";
$config_files = [
    '.htaccess',
    'config/database.php',
    'includes/header.php',
    'includes/footer.php'
];

foreach ($config_files as $file) {
    echo $file . ": " . (file_exists("../" . $file) ? "✓ Exists" : "✗ Missing") . "<br>";
} 