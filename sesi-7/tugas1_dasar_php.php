<?php
$namaProduk = "Mouse Wireless";
$harga = 75000;
$stok = 10;
$totalNilaiStok = $harga * $stok;

if ($stok > 0 && $harga >= 50000) {
    $status = "Produk tersedia dan harganya minimal Rp50.000.";
} elseif ($stok > 0) {
    $status = "Produk tersedia, tetapi harganya di bawah Rp50.000.";
} else {
    $status = "Produk habis.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tugas 1 - Dasar PHP</title>
<style>body{font-family:Arial,sans-serif;max-width:700px;margin:40px auto;padding:0 16px;color:#222}.card{border:1px solid #ddd;border-radius:10px;padding:20px}.status{background:#eef7ee;padding:12px;border-radius:6px}</style>
</head>
<body>
<h1>Tugas 1: Dasar PHP</h1>
<div class="card">
<p><strong>Nama produk:</strong> <?= htmlspecialchars($namaProduk) ?></p>
<p><strong>Harga:</strong> Rp<?= number_format($harga,0,',','.') ?></p>
<p><strong>Stok:</strong> <?= $stok ?></p>
<p><strong>Total nilai stok:</strong> Rp<?= number_format($totalNilaiStok,0,',','.') ?></p>
<p class="status"><?= htmlspecialchars($status) ?></p>
</div>
<p>Pelajari variabel, operator perkalian <code>*</code>, kondisi <code>if-elseif-else</code>, dan operator <code>&&</code>.</p>
<p><a href="tugas2_form_input.php">Lanjut ke Tugas 2: Form Input</a></p>
</body></html>
