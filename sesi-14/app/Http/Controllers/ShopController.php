<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ShopController
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));

        $query = Produk::query()->with('kategoriProduk');

        if ($category !== '') {
            $query->whereHas('kategoriProduk', fn ($query) => $query->where('name', $category));
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $products = $query->get()->map(fn (Produk $product): array => $this->toCartProduct($product));

        return view('home.index', [
            'products' => $products,
            'categories' => KategoriProduk::query()->orderBy('name')->pluck('name'),
            'search' => $search,
            'selectedCategory' => $category,
        ]);
    }

    public function cart(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $products = Produk::query()
            ->with('kategoriProduk')
            ->whereKey(array_keys($cart))
            ->get()
            ->keyBy(fn (Produk $product): string => (string) $product->getKey());

        $items = collect($cart)->map(function (int $quantity, string $id) use ($products): ?array {
            $product = $products->get($id);

            if (! $product) {
                return null;
            }

            $product = $this->toCartProduct($product);

            return [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $product['price'] * $quantity,
            ];
        })->filter()->values();

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
        $product = Produk::query()->findOrFail($id);
        $validated = $request->validate(['quantity' => ['sometimes', 'integer', 'min:1']]);

        $cart = $request->session()->get('cart', []);
        $quantity = ($cart[$id] ?? 0) + ($validated['quantity'] ?? 1);
        if ($quantity > $product->stock) {
            return back()
                ->withErrors(['quantity' => 'Jumlah produk melebihi stok yang tersedia.'])
                ->withInput();
        }

        $cart[$id] = $quantity;
        $request->session()->put('cart', $cart);

        return back()->with('success', $product->name.' ditambahkan ke keranjang.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $product = Produk::query()->findOrFail($id);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$product->stock],
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

    /**
     * Convert a product model into the data shape used by the shop and cart views.
     *
     * @return array{id: string, name: string, description: string, category: string, price: int, stock: int, image: ?string, badge: null}
     */
    private function toCartProduct(Produk $product): array
    {
        return [
            'id' => (string) $product->getKey(),
            'name' => $product->name,
            'description' => $product->description ?? '',
            'category' => $product->kategoriProduk?->name ?? '',
            'price' => (int) $product->price,
            'stock' => $product->stock,
            'image' => $product->image && Storage::disk('public')->exists($product->image)
                ? Storage::disk('public')->url($product->image)
                : null,
            'badge' => null,
        ];
    }
}
