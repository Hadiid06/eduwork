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
            ['Kaos Katun', 'Kaos katun lembut untuk pemakaian sehari-hari.', 40, 'Fashion'],
            ['Jaket Denim', 'Jaket denim kasual dengan bahan kuat.', 15, 'Fashion'],
            ['Sneakers Kasual', 'Sepatu ringan untuk aktivitas harian.', 20, 'Fashion'],
            ['Jam Tangan Minimalis', 'Jam tangan sederhana dengan desain elegan.', 10, 'Aksesoris'],
            ['Tas Selempang', 'Tas selempang praktis untuk membawa barang.', 16, 'Aksesoris'],
            ['Kacamata Hitam', 'Kacamata dengan desain modern untuk kegiatan luar.', 22, 'Aksesoris'],
            ['Blender Mini', 'Blender ringkas untuk membuat minuman di rumah.', 8, 'Peralatan Rumah'],
            ['Botol Minum Stainless', 'Botol minum tahan lama untuk penggunaan sehari-hari.', 25, 'Peralatan Rumah'],
            ['Lampu Meja', 'Lampu meja untuk membantu aktivitas membaca.', 14, 'Peralatan Rumah'],
            ['Matras Yoga', 'Matras empuk untuk latihan yoga dan peregangan.', 11, 'Olahraga'],
            ['Bola Basket', 'Bola basket untuk latihan dan permainan.', 9, 'Olahraga'],
        ];

        foreach ($produk as [$name, $description, $stock, $kategori]) {
            Produk::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'stock' => $stock,
                    'image' => 'placeholder.jpg',
                    'kategori_produk_id' => $kategoriIds[$kategori],
                ],
            );
        }
    }
}
