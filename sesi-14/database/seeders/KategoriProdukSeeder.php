<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use Illuminate\Database\Seeder;

class KategoriProdukSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Elektronik',
            'Fashion',
            'Aksesoris',
            'Peralatan Rumah',
            'Olahraga',
        ] as $name) {
            KategoriProduk::updateOrCreate(['name' => $name]);
        }
    }
}
