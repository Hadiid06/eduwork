<?php

$host = 'localhost';
$db = 'sesi_9';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $exception) {
    http_response_code(503);
    exit(
        'Database NusaMart belum siap. Impor file '
        . '<code>database.sql</code> melalui phpMyAdmin, lalu muat ulang halaman. '
        . 'Jika kredensial MySQL kamu berbeda, sesuaikan <code>koneksi_db.php</code>.'
    );
}
