<?php
$errors = [];
$nama = trim($_POST['nama'] ?? '');
$hargaInput = trim($_POST['harga'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$harga = null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Halaman ini hanya menerima data dari form. Silakan buka tugas2_form_input.php.');
}
if ($nama === '') {
    $errors[] = 'Nama produk wajib diisi.';
}
if ($hargaInput === '') {
    $errors[] = 'Harga wajib diisi.';
} elseif (!ctype_digit($hargaInput) || (int)$hargaInput <= 0) {
    $errors[] = 'Harga harus berupa angka bulat lebih dari 0.';
} else {
    $harga = (int)$hargaInput;
}
if ($deskripsi === '') {
    $errors[] = 'Deskripsi produk wajib diisi.';
}
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tugas 3 - Validasi Produk</title>
<style>
body{font-family:Arial,sans-serif;max-width:700px;margin:40px auto;padding:0 16px;color:#222}
.card{border:1px solid #ddd;border-radius:10px;padding:20px}
.error{background:#fff0f0;border:1px solid #e7a2a2;padding:14px;border-radius:6px}
.success{background:#eef7ee;border:1px solid #a9d4a9;padding:14px;border-radius:6px}
li{margin:6px 0}
</style>
</head>
<body>
<h1>Tugas 3: Validasi Data Produk</h1>
<div class="card">
<?php if (!empty($errors)): ?>
<div class="error"><h2>Data belum valid</h2><p>Perbaiki hal berikut:</p><ul>
<?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
</ul></div>
<p><a href="tugas2_form_input.php">Kembali ke form dan perbaiki data</a></p>
<?php else: ?>
<div class="success"><h2>Validasi berhasil!</h2><p>Data lolos pemeriksaan dan siap disimpan ke database.</p></div>
<p><strong>Nama produk:</strong> <?= e($nama) ?></p>
<p><strong>Harga:</strong> Rp<?= number_format($harga,0,',','.') ?></p>
<p><strong>Deskripsi:</strong><br><?= nl2br(e($deskripsi)) ?></p>
<p><em>Catatan: contoh ini belum menyimpan data ke database.</em></p>
<p><a href="tugas2_form_input.php">Input produk lain</a></p>
<?php endif; ?>
</div></body></html>
