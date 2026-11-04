@extends('template.layout')

@section('title', 'Daftar Produk')

@section('content')
    <div class="container">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-4">
            <div>
                <span class="product-category">KATALOG TOKO</span>
                <h1 class="h2 fw-bold mt-1 mb-0">Daftar Produk</h1>
            </div>
            <span class="text-secondary">{{ $products->total() }} produk</span>
        </div>

        <div class="cart-panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nomor</th>
                            <th scope="col">Gambar Produk</th>
                            <th scope="col">Nama Produk</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Deskripsi</th>
                            <th scope="col">Stok</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>{{ $products->firstItem() + $loop->index }}</td>
                                <td>
                                    @if ($product->image && (\Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://']) || \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)))
                                        <img
                                            src="{{ \Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://']) ? $product->image : \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
                                            alt="{{ $product->name }}"
                                            width="72"
                                            height="72"
                                            class="rounded object-fit-cover"
                                        >
                                    @else
                                        <span class="text-secondary">Tidak ada gambar</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $product->name }}</td>
                                <td>{{ $product->kategoriProduk?->name ?? '-' }}</td>
                                <td>{{ $product->description ?: '-' }}</td>
                                <td>{{ $product->stock }}</td>
                                <td><span class="text-secondary">-</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5 text-center text-secondary">
                                    Belum ada produk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($products->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
