NUSAMART - DASHBOARD E-COMMERCE (HALAMAN PENGGUNA)

ISI
- Dashboard pengguna dengan kategori, produk pilihan, pencarian, dan filter kategori.
- database.sql berisi skema database serta data contoh kategori dan produk.
- Pencarian produk berdasarkan nama, deskripsi, atau kategori.
- Detail produk dan keranjang belanja berbasis sesi pengunjung.
- Keranjang mendukung penambahan produk, pembaruan jumlah, dan penghapusan.
- Barang di keranjang dicadangkan dari stok tersedia; hapus atau kurangi jumlah
  untuk mengembalikan stok yang tidak dipakai.
- Pilih barang tertentu di keranjang untuk membuat pesanan; barang lain tetap
  berada di keranjang.
- Pembelian langsung membuat pesanan, mengurangi stok, dan menghasilkan invoice.
- Invoice dapat dicetak dari browser.
- Pembayaran online belum tersedia; pesanan tercatat dengan status menunggu pembayaran.

CARA MENJALANKAN
1. Jalankan Apache dan MySQL melalui XAMPP.
2. Buka http://localhost/phpmyadmin dan impor file database.sql.
3. Buka http://localhost/eduwork/sesi-9/
4. Jika kredensial MySQL berbeda dari konfigurasi XAMPP default, sesuaikan
   koneksi di koneksi_db.php.
