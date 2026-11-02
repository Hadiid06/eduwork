<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class PesananSeeder extends Seeder
{
    public function run(): void
    {
        $pesanan = [
            ['alya.putri@example.com', 'Headphone Bluetooth', 1, 459000],
            ['alya.putri@example.com', 'Matras Yoga', 2, 318000],
            ['bima.pratama@example.com', 'Sneakers Kasual', 1, 289000],
            ['bima.pratama@example.com', 'Blender Mini', 1, 225000],
            ['citra.lestari@example.com', 'Jam Tangan Minimalis', 1, 375000],
        ];

        foreach ($pesanan as [$email, $namaProduk, $quantity, $total]) {
            $userId = Pengguna::query()->where('email', $email)->value('id');
            $productId = Produk::query()->where('name', $namaProduk)->value('id');

            Pesanan::firstOrCreate(
                [
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ],
                ['total' => $total],
            );
        }
    }
}
