
<?php

require '../../koneksi_db.php';

// Pastikan data dikirim menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit;
}

// Ambil data dari form
$name = trim($_POST['name'] ?? '');
$price = $_POST['price'] ?? '';
$stock = $_POST['stock'] ?? '';
$description = trim($_POST['description'] ?? '');

// Validasi data produk
if (
    $name === '' ||
    !is_numeric($price) ||
    $price < 0 ||
    filter_var($stock, FILTER_VALIDATE_INT) === false ||
    $stock < 0
) {
    die(
        'Data tidak valid. 
        <a href="../form/create_form.php">Kembali</a>'
    );
}

// Simpan produk ke database
$stmt = $pdo->prepare(
    'INSERT INTO products (name, price, description, stock)
     VALUES (?, ?, ?, ?)'
);

$stmt->execute([
    $name,
    $price,
    $description,
    (int) $stock
]);

// Kembali ke halaman utama
header('Location: ../../index.php');
exit;
