<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController
{
    private const PRODUCTS = [
        'sneakers' => [
            'id' => 'sneakers',
            'name' => 'Urban Everyday Sneakers',
            'description' => 'Sepatu ringan dan nyaman untuk aktivitas harian.',
            'category' => 'Fashion',
            'price' => 289000,
            'stock' => 12,
            'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=85',
            'badge' => 'Terlaris',
        ],
        'headphones' => [
            'id' => 'headphones',
            'name' => 'Studio Wireless Headphones',
            'description' => 'Suara jernih dan nyaman dipakai sepanjang hari.',
            'category' => 'Elektronik',
            'price' => 459000,
            'stock' => 8,
            'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=85',
            'badge' => 'Favorit',
        ],
        'watch' => [
            'id' => 'watch',
            'name' => 'Classic Minimal Watch',
            'description' => 'Jam tangan minimalis untuk gaya yang selalu tepat.',
            'category' => 'Aksesori',
            'price' => 375000,
            'stock' => 10,
            'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=85',
            'badge' => null,
        ],
        'camera' => [
            'id' => 'camera',
            'name' => 'Pocket Film Camera',
            'description' => 'Abadikan momen favorit dengan karakter yang unik.',
            'category' => 'Elektronik',
            'price' => 629000,
            'stock' => 5,
            'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=85',
            'badge' => 'Baru',
        ],
    ];

    public function index(Request $request): View
    {
        $products = collect(self::PRODUCTS);
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));

        if ($search !== '') {
            $products = $products->filter(fn (array $product): bool =>
                str_contains(mb_strtolower($product['name'].' '.$product['description'].' '.$product['category']), mb_strtolower($search))
            );
        }

        if ($category !== '') {
            $products = $products->where('category', $category);
        }

        return view('home.index', [
            'products' => $products->values(),
            'categories' => collect(self::PRODUCTS)->pluck('category')->unique()->values(),
            'search' => $search,
            'selectedCategory' => $category,
        ]);
    }

    public function cart(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $items = collect($cart)
            ->filter(fn (int $quantity, string $id): bool => isset(self::PRODUCTS[$id]))
            ->map(function (int $quantity, string $id): array {
                $product = self::PRODUCTS[$id];

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $product['price'] * $quantity,
                ];
            })->values();

        $subtotal = $items->sum('line_total');
        $shipping = $subtotal === 0 || $subtotal >= 300000 ? 0 : 15000;

        return view('cart.index', [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'itemCount' => $items->sum('quantity'),
        ]);
    }

    public function add(Request $request, string $id): RedirectResponse
    {
        abort_unless(isset(self::PRODUCTS[$id]), 404);
        $validated = $request->validate(['quantity' => ['sometimes', 'integer', 'min:1']]);

        $cart = $request->session()->get('cart', []);
        $quantity = ($cart[$id] ?? 0) + ($validated['quantity'] ?? 1);
        if ($quantity > self::PRODUCTS[$id]['stock']) {
            return back()
                ->withErrors(['quantity' => 'Jumlah produk melebihi stok yang tersedia.'])
                ->withInput();
        }

        $cart[$id] = $quantity;
        $request->session()->put('cart', $cart);

        return back()->with('success', self::PRODUCTS[$id]['name'].' ditambahkan ke keranjang.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        abort_unless(isset(self::PRODUCTS[$id]), 404);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.self::PRODUCTS[$id]['stock']],
        ]);

        $cart = $request->session()->get('cart', []);
        abort_unless(isset($cart[$id]), 404);

        $cart[$id] = $validated['quantity'];
        $request->session()->put('cart', $cart);

        return to_route('cart.index')->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    public function remove(Request $request, string $id): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        abort_unless(isset($cart[$id]), 404);

        unset($cart[$id]);
        $request->session()->put('cart', $cart);

        return to_route('cart.index')->with('success', 'Produk dihapus dari keranjang.');
    }
}
