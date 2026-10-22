<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tugas 2 - Form Input Produk</title>
<style>
body{font-family:Arial,sans-serif;max-width:700px;margin:40px auto;padding:0 16px;color:#222}
form{border:1px solid #ddd;border-radius:10px;padding:20px}
label{display:block;margin:14px 0 6px;font-weight:bold}
input,textarea{box-sizing:border-box;width:100%;padding:10px;border:1px solid #bbb;border-radius:6px;font:inherit}
textarea{min-height:110px;resize:vertical}
button{margin-top:18px;background:#155eef;color:white;border:0;padding:11px 16px;border-radius:6px;cursor:pointer}
</style>
</head>
<body>
<h1>Tugas 2: Form Input Produk</h1>
<p>Isi nama, harga, dan deskripsi produk. Data akan diperiksa di halaman berikutnya.</p>
<form action="tugas3_validasi.php" method="POST">
<label for="nama">Nama produk</label>
<input type="text" id="nama" name="nama" placeholder="Contoh: Mouse Wireless" required>
<label for="harga">Harga (Rp)</label>
<input type="number" id="harga" name="harga" placeholder="Contoh: 75000" min="1" step="1" required>
<label for="deskripsi">Deskripsi produk</label>
<textarea id="deskripsi" name="deskripsi" placeholder="Jelaskan produk..." required></textarea>
<button type="submit">Kirim Data Produk</button>
</form>
<p><a href="tugas1_dasar_php.php">Kembali ke Tugas 1</a></p>
</body></html>
