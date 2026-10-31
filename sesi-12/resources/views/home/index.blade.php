@extends('template.layout')

@section('title', 'Belanja')

@section('content')
    <div class="container">
        <section class="hero-panel mb-5">
            <div class="hero-content">
                <span class="badge rounded-pill bg-white text-success mb-3 px-3 py-2">PILIHAN TERBAIK UNTUKMU</span>
                <h1 class="display-5 fw-bold mb-3">Temukan hal hebat untuk setiap harimu.</h1>
                <p class="mb-4 text-white-50 fs-5">Produk pilihan, harga bersahabat, dan pengalaman belanja yang lebih mudah.</p>
                <a class="shop-button d-inline-flex align-items-center gap-2" href="#produk">
                    Belanja sekarang
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </section>

        <section class="mb-5" id="kategori">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-3">
                <div>
                    <span class="product-category">JELAJAHI TOKO</span>
                    <h2 class="h4 fw-bold mt-1 mb-0">Belanja sesuai kategori</h2>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="category-chip {{ $selectedCategory === '' ? 'active' : '' }}" href="{{ route('home', array_filter(['q' => $search])) }}">Semua</a>
                    @foreach ($categories as $category)
                        <a class="category-chip {{ $selectedCategory === $category ? 'active' : '' }}" href="{{ route('home', array_filter(['category' => $category, 'q' => $search])) }}">{{ $category }}</a>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="produk">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-4">
                <div>
                    <span class="product-category">PILIHAN UNTUKMU</span>
                    <h2 class="h3 fw-bold mt-1 mb-0">
                        @if ($search !== '')
                            Hasil pencarian “{{ $search }}”
                        @elseif ($selectedCategory !== '')
                            Produk {{ $selectedCategory }}
                        @else
                            Produk pilihan
                        @endif
                    </h2>
                </div>
                <span class="text-secondary">{{ $products->count() }} produk</span>
            </div>

            @if ($products->isNotEmpty())
                <div class="row g-4">
                    @foreach ($products as $product)
                        <div class="col-6 col-md-3">
                            <x-product-card
                                :id="$product['id']"
                                :image="$product['image']"
                                :name="$product['name']"
                                :description="$product['description']"
                                :category="$product['category']"
                                :price="$product['price']"
                                :badge="$product['badge']"
                                :link="route('home').'#produk'"
                            />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="cart-panel p-5 text-center">
                    <h3 class="h5 fw-bold">Produk tidak ditemukan</h3>
                    <p class="text-secondary mb-3">Coba kata kunci atau kategori yang berbeda.</p>
                    <a href="{{ route('home') }}" class="shop-button d-inline-flex">Lihat semua produk</a>
                </div>
            @endif
        </section>

        <section class="row g-3 mt-5" aria-label="Keunggulan belanja">
            <div class="col-md-4">
                <div class="h-100 rounded-4 p-4" style="background: #f6f8f6;">
                    <strong>Pengiriman hemat</strong>
                    <p class="text-secondary mb-0 mt-1">Gratis ongkir untuk belanja minimal Rp300.000.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="h-100 rounded-4 p-4" style="background: #f6f8f6;">
                    <strong>Produk pilihan</strong>
                    <p class="text-secondary mb-0 mt-1">Jelajahi produk pilihan untuk kebutuhan harianmu.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="h-100 rounded-4 p-4" style="background: #f6f8f6;">
                    <strong>Belanja praktis</strong>
                    <p class="text-secondary mb-0 mt-1">Simpan pilihanmu di keranjang selama sesi belanja.</p>
                </div>
            </div>
        </section>
    </div>
@endsection
