<?php

namespace Tests\Feature;

use App\Models\Produk;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_update_and_remove_a_product(): void
    {
        $this->seed(DatabaseSeeder::class);
        $product = Produk::query()->where('name', 'Sneakers Kasual')->firstOrFail();
        $productId = (string) $product->getKey();

        $this->from(route('home'))
            ->post(route('cart.add', $productId))
            ->assertRedirect(route('home'))
            ->assertSessionHas('cart', [$productId => 1]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Sneakers Kasual')
            ->assertSee('Rp220.000');

        $this->patch(route('cart.update', $productId), ['quantity' => 2])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart', [$productId => 2]);

        $this->delete(route('cart.remove', $productId))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart', []);
    }

    public function test_cart_quantity_cannot_exceed_available_stock(): void
    {
        $this->seed(DatabaseSeeder::class);
        $product = Produk::query()->where('name', 'Sneakers Kasual')->firstOrFail();

        $this->from(route('home'))
            ->post(route('cart.add', $product->getKey()), ['quantity' => 21])
            ->assertSessionHasErrors('quantity')
            ->assertSessionMissing('cart');
    }
}
