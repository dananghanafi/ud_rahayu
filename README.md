# UD Rahayu - Aplikasi Web Kopi Premium

Aplikasi web untuk manajemen toko kopi UD Rahayu menggunakan PHP dan MongoDB.

## Persyaratan Sistem

- PHP >= 7.4
- MongoDB Server
- Apache Web Server
- Composer

## Instalasi

1. Clone repository ini ke direktori web server Anda:
```bash
git clone https://github.com/username/ud_rahayu.git
cd ud_rahayu
```

2. Install dependensi menggunakan Composer:
```bash
composer install
```

3. Konfigurasi MongoDB:
- Buat database bernama 'ud_rahayu'
- Import collections yang diperlukan (products, orders, users, notifications)

4. Buat user admin:
```php
use MongoDB\Client;
$client = new MongoDB\Client("mongodb://localhost:27017");
$db = $client->ud_rahayu;

$db->users->insertOne([
    'username' => 'admin',
    'password' => password_hash('your_password', PASSWORD_DEFAULT),
    'role' => 'admin',
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);
```

5. Konfigurasi Apache:
- Pastikan mod_rewrite diaktifkan
- Sesuaikan file .htaccess jika diperlukan

## Struktur Direktori

```
ud_rahayu/
├── app/            # Logic aplikasi
├── assets/         # Asset statis (CSS, JS, images)
├── config/         # File konfigurasi
├── includes/       # File yang di-include
├── public/         # File yang dapat diakses publik
└── vendor/         # Dependensi (dikelola Composer)
```

## Fitur

- Manajemen produk
- Sistem pemesanan
- Dashboard admin
- Manajemen pesanan
- Notifikasi pesanan baru
- Laporan penjualan

## Penggunaan

1. Akses aplikasi melalui browser:
```
http://localhost/ud_rahayu
```

2. Login admin:
```
URL: http://localhost/ud_rahayu/admin/login.php
Username: admin
Password: [sesuai yang Anda set]
```

## Keamanan

- Semua password di-hash menggunakan password_hash()
- Proteksi direktori sensitif melalui .htaccess
- Validasi input dan sanitasi output
- Session management untuk admin

## Lisensi

MIT License 