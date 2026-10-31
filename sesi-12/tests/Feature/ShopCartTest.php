<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShopCartTest extends TestCase
{
    public function test_customer_can_add_update_and_remove_a_product(): void
    {
        $this->from(route('home'))
            ->post(route('cart.add', 'sneakers'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('cart', ['sneakers' => 1]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Urban Everyday Sneakers')
            ->assertSee('Rp289.000');

        $this->patch(route('cart.update', 'sneakers'), ['quantity' => 2])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart', ['sneakers' => 2]);

        $this->delete(route('cart.remove', 'sneakers'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart', []);
    }

    public function test_cart_quantity_cannot_exceed_available_stock(): void
    {
        $this->from(route('home'))
            ->post(route('cart.add', 'sneakers'), ['quantity' => 20])
            ->assertSessionHasErrors('quantity')
            ->assertSessionMissing('cart');
    }
}
