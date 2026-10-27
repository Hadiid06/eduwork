PROJECT STOKKU - CRUD PRODUK SEDERHANA

FITUR
- Menampilkan daftar produk
- Mencari produk berdasarkan nama
- Menambah produk
- Mengedit produk
- Menghapus produk
- Melihat etalase produk
- Menambahkan produk ke keranjang
- Checkout pesanan dan melihat invoice
- Menghitung jumlah jenis produk dan total stok

CARA MENJALANKAN
1. Ekstrak folder produk-sederhana ke htdocs XAMPP.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka http://localhost/phpmyadmin
4. Pilih tab Import dan impor file database.sql, atau jalankan isi file itu dari tab SQL.
5. Buka http://localhost/produk-sederhana/

Jika database sesi-8 sudah dibuat sebelumnya, jalankan ulang perintah SQL
CREATE TABLE IF NOT EXISTS orders dan order_items di sesi-8.sql agar tabel
checkout tersedia. Pesanan yang berhasil dibuat akan mengurangi stok produk.

KONEKSI DATABASE
File koneksi_db.php menggunakan database sesi-8, user root, dan password kosong (default XAMPP). Jika konfigurasi MySQL kamu berbeda, sesuaikan nilai host, db, user, dan pass.

TEKNOLOGI
PHP, PDO, MySQL, HTML, CSS.
