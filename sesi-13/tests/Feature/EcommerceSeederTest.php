<?php

namespace Tests\Feature;

use App\Models\KategoriProduk;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Produk;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EcommerceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_catalog_users_and_related_orders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('kategori_produk', 5);
        $this->assertDatabaseCount('produk', 15);
        $this->assertDatabaseCount('pengguna', 3);
        $this->assertDatabaseCount('pesanan', 5);

        $this->assertTrue(Hash::check('password', Pengguna::query()->firstOrFail()->password));
        $this->assertSame(0, Produk::query()->whereDoesntHave('kategoriProduk')->count());

        $pesanan = Pesanan::query()->with(['pengguna', 'produk'])->firstOrFail();
        $this->assertNotNull($pesanan->pengguna);
        $this->assertNotNull($pesanan->produk);
        $this->assertGreaterThan(0, KategoriProduk::query()->firstOrFail()->produks()->count());
    }
}
