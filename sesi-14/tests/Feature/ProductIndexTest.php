<?php

namespace Tests\Feature;

use App\Models\Produk;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_the_seeded_fifty_products(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('50 produk')
            ->assertSee('Headphone Bluetooth')
            ->assertSee('Sarung Tangan Fitness');
    }

    public function test_products_page_displays_related_categories_and_paginates_ten_products(): void
    {
        $this->seed(DatabaseSeeder::class);

        $product = Produk::query()
            ->with(['kategoriProduk', 'pesanans'])
            ->where('name', 'Headphone Bluetooth')
            ->firstOrFail();

        $this->assertSame('Elektronik', $product->kategoriProduk->name);
        $this->assertNotEmpty($product->pesanans);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Headphone Bluetooth')
            ->assertSee('Elektronik')
            ->assertSee('Tidak ada gambar')
            ->assertSee('Charger USB-C')
            ->assertDontSee('Stand Laptop Aluminium')
            ->assertSee('page=2', false);

        $this->get(route('products.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Stand Laptop Aluminium')
            ->assertDontSee('Headphone Bluetooth');
    }
}
