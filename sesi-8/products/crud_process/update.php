
<?php

require '../../koneksi_db.php';

// Pastikan data dikirim menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit;
}

// Ambil data dari form
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$name = trim($_POST['name'] ?? '');
$price = $_POST['price'] ?? '';
$stock = $_POST['stock'] ?? '';
$description = trim($_POST['description'] ?? '');

// Validasi data produk
if (
    !$id ||
    $name === '' ||
    !is_numeric($price) ||
    $price < 0 ||
    filter_var($stock, FILTER_VALIDATE_INT) === false ||
    $stock < 0
) {
    die(
        'Data tidak valid. 
        <a href="../../index.php">Kembali</a>'
    );
}

// Perbarui data produk di database
$stmt = $pdo->prepare(
    'UPDATE products
     SET name = ?, price = ?, description = ?, stock = ?
     WHERE id = ?'
);

// Jalankan query dengan data dari form
$stmt->execute([
    $name,
    $price,
    $description,
    (int) $stock,
    $id
]);

// Kembali ke halaman utama
header('Location: ../../index.php');
exit;
