<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Alya Putri', 'alya.putri@example.com'],
            ['Bima Pratama', 'bima.pratama@example.com'],
            ['Citra Lestari', 'citra.lestari@example.com'],
        ] as [$name, $email]) {
            Pengguna::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password')],
            );
        }
    }
}
