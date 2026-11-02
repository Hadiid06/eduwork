<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriIds = KategoriProduk::query()
            ->whereIn('name', ['Elektronik', 'Fashion', 'Aksesoris', 'Peralatan Rumah', 'Olahraga'])
            ->pluck('id', 'name');

        $produk = [
            ['Headphone Bluetooth', 'Headphone nirkabel dengan suara jernih.', 18, 'Elektronik'],
            ['Mouse Wireless', 'Mouse praktis untuk bekerja dan belajar.', 30, 'Elektronik'],
            ['Keyboard Mechanical', 'Keyboard mekanis nyaman untuk mengetik.', 12, 'Elektronik'],
            ['Lampu Smart LED', 'Lampu hemat energi untuk ruangan rumah.', 24, 'Elektronik'],
            ['Speaker Bluetooth Portable', 'Speaker portabel dengan suara kuat untuk berbagai aktivitas.', 17, 'Elektronik'],
            ['Webcam Full HD', 'Webcam beresolusi tinggi untuk rapat dan kelas daring.', 13, 'Elektronik'],
            ['Monitor LED 24 Inci', 'Monitor LED dengan tampilan jernih untuk bekerja.', 7, 'Elektronik'],
            ['Power Bank 10000 mAh', 'Power bank praktis untuk mengisi daya saat bepergian.', 21, 'Elektronik'],
            ['Flashdisk 64 GB', 'Penyimpanan portabel untuk dokumen dan file sehari-hari.', 32, 'Elektronik'],
            ['Charger USB-C', 'Pengisi daya USB-C ringkas dengan pengisian cepat.', 26, 'Elektronik'],
            ['Stand Laptop Aluminium', 'Penyangga laptop yang kokoh dan membantu posisi kerja.', 15, 'Elektronik'],
            ['Kabel Data USB-C', 'Kabel data untuk pengisian daya dan transfer file.', 35, 'Elektronik'],
            ['Kaos Katun', 'Kaos katun lembut untuk pemakaian sehari-hari.', 40, 'Fashion'],
            ['Jaket Denim', 'Jaket denim kasual dengan bahan kuat.', 15, 'Fashion'],
            ['Sneakers Kasual', 'Sepatu ringan untuk aktivitas harian.', 20, 'Fashion'],
            ['Kemeja Flanel', 'Kemeja flanel nyaman untuk gaya kasual.', 18, 'Fashion'],
            ['Hoodie Polos', 'Hoodie hangat dengan desain sederhana.', 14, 'Fashion'],
            ['Celana Chino', 'Celana chino dengan potongan nyaman untuk sehari-hari.', 19, 'Fashion'],
            ['Polo Shirt', 'Polo shirt berbahan lembut untuk tampilan rapi dan santai.', 23, 'Fashion'],
            ['Sandal Kasual', 'Sandal ringan untuk aktivitas santai.', 27, 'Fashion'],
            ['Topi Baseball', 'Topi dengan desain klasik untuk melengkapi penampilan.', 16, 'Fashion'],
            ['Kemeja Linen', 'Kemeja linen ringan untuk digunakan saat cuaca hangat.', 11, 'Fashion'],
            ['Jam Tangan Minimalis', 'Jam tangan sederhana dengan desain elegan.', 10, 'Aksesoris'],
            ['Tas Selempang', 'Tas selempang praktis untuk membawa barang.', 16, 'Aksesoris'],
            ['Kacamata Hitam', 'Kacamata dengan desain modern untuk kegiatan luar.', 22, 'Aksesoris'],
            ['Dompet Kulit Sintetis', 'Dompet ringkas dengan ruang untuk kartu dan uang.', 20, 'Aksesoris'],
            ['Ikat Pinggang Kasual', 'Ikat pinggang serbaguna untuk pemakaian harian.', 18, 'Aksesoris'],
            ['Tas Ransel Harian', 'Tas ransel dengan ruang penyimpanan untuk kebutuhan harian.', 12, 'Aksesoris'],
            ['Gelang Stainless', 'Gelang sederhana dengan bahan stainless yang tahan lama.', 25, 'Aksesoris'],
            ['Syal Polos', 'Syal lembut untuk melengkapi gaya sehari-hari.', 14, 'Aksesoris'],
            ['Tempat Kartu', 'Tempat kartu kecil dan praktis untuk dibawa bepergian.', 28, 'Aksesoris'],
            ['Payung Lipat', 'Payung ringan yang mudah disimpan di dalam tas.', 17, 'Aksesoris'],
            ['Blender Mini', 'Blender ringkas untuk membuat minuman di rumah.', 8, 'Peralatan Rumah'],
            ['Botol Minum Stainless', 'Botol minum tahan lama untuk penggunaan sehari-hari.', 25, 'Peralatan Rumah'],
            ['Lampu Meja', 'Lampu meja untuk membantu aktivitas membaca.', 14, 'Peralatan Rumah'],
            ['Ketel Listrik', 'Ketel listrik praktis untuk memanaskan air.', 10, 'Peralatan Rumah'],
            ['Panci Serbaguna', 'Panci untuk membantu menyiapkan berbagai hidangan.', 13, 'Peralatan Rumah'],
            ['Wajan Anti Lengket', 'Wajan dengan permukaan anti lengket agar mudah dibersihkan.', 9, 'Peralatan Rumah'],
            ['Rak Penyimpanan', 'Rak serbaguna untuk menjaga barang tetap tertata.', 6, 'Peralatan Rumah'],
            ['Kipas Angin Meja', 'Kipas angin ringkas untuk sirkulasi udara di meja kerja.', 12, 'Peralatan Rumah'],
            ['Set Alat Makan', 'Set alat makan untuk melengkapi kebutuhan rumah.', 20, 'Peralatan Rumah'],
            ['Bantal Tidur', 'Bantal empuk untuk mendukung istirahat yang nyaman.', 15, 'Peralatan Rumah'],
            ['Matras Yoga', 'Matras empuk untuk latihan yoga dan peregangan.', 11, 'Olahraga'],
            ['Bola Basket', 'Bola basket untuk latihan dan permainan.', 9, 'Olahraga'],
            ['Bola Sepak', 'Bola untuk latihan dan permainan sepak bola.', 14, 'Olahraga'],
            ['Raket Badminton', 'Raket ringan untuk latihan dan permainan badminton.', 8, 'Olahraga'],
            ['Dumbbell 2 kg', 'Dumbbell untuk latihan kekuatan di rumah.', 10, 'Olahraga'],
            ['Tali Skipping', 'Tali skipping yang dapat digunakan untuk latihan kardio.', 22, 'Olahraga'],
            ['Botol Minum Olahraga', 'Botol minum praktis untuk menemani kegiatan olahraga.', 19, 'Olahraga'],
            ['Sarung Tangan Fitness', 'Sarung tangan untuk membantu kenyamanan saat latihan beban.', 13, 'Olahraga'],
        ];

        $hargaKategori = [
            'Elektronik' => 250000,
            'Fashion' => 150000,
            'Aksesoris' => 100000,
            'Peralatan Rumah' => 125000,
            'Olahraga' => 90000,
        ];

        foreach ($produk as $index => [$name, $description, $stock, $kategori]) {
            Produk::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'stock' => $stock,
                    'image' => 'placeholder.jpg',
                    'price' => $hargaKategori[$kategori] + ($index * 5000),
                    'kategori_produk_id' => $kategoriIds[$kategori],
                ],
            );
        }
    }
}
